<?php

//
//  Copyright (C) 2014 by Jackie Ng
//
//  This library is free software; you can redistribute it and/or
//  modify it under the terms of version 2.1 of the GNU Lesser
//  General Public License as published by the Free Software Foundation.
//
//  This library is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  Lesser General Public License for more details.
//
//  You should have received a copy of the GNU Lesser General Public
//  License along with this library; if not, write to the Free Software
//  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
//

// Avoid any nasty XXE surprises
libxml_disable_entity_loader(true);

// See this link for why we are doing this:
// http://q.nett.gr/wordpress-3-x-on-iis-7-x-with-unicode-permalinks-problem-solved/comment-page-1/
//
// Or if that link 404s or you're lazy:
//
// This is to ensure URLs with unicode characters do not get trashed by the IIS URL rewriting module
// which in turn will crash php-cgi.exe when this processed URL goes through the Slim router
//
// Only do this for IIS though, as this will screw up Apache httpd
if (strpos($_SERVER['SERVER_SOFTWARE'], "IIS") !== FALSE)
{
    if (array_key_exists('UNENCODED_URL', $_SERVER))
        $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
}

require 'vendor/autoload.php';
include dirname(__FILE__)."/../mapadmin/constants.php";
//This is a quick and dirty way to inject the MapGuide Server version. That version number is stamped on
//resizableadmin.php from the Site Administrator, since we're already pulling in its constants, we can pull
//this in as well
include dirname(__FILE__)."/../mapadmin/resizableadmin.php";

//Shim some constants we know haven't been properly exposed in previous versions of MapGuide
if (!class_exists("MgImageFormats")) {
    class MgImageFormats
    {
        const Gif = "GIF";
        const Jpeg = "JPG";
        const Png = "PNG";
        const Png8 = "PNG8";
        const Raw = "RAW";
        const Tiff = "TIF";
    }
}

$webConfigPath = dirname(__FILE__)."/../webconfig.ini";
MgInitializeWebTier($webConfigPath);

require_once dirname(__FILE__)."/app/util/localizer.php";
require_once dirname(__FILE__)."/app/util/utils.php";
$config = require_once dirname(__FILE__)."/app/config.php";
$logConfig = require_once dirname(__FILE__)."/app/log_config.php";
$config = array_merge($config, $logConfig);
//Pull in the appropriate string bundle
$strings = require_once dirname(__FILE__)."/app/res/lang/".$config["Locale"].".php";
$app = new \Slim\Slim($config);
$corsOptions = $app->config("MapGuide.Cors");
if ($corsOptions != null) {
    $app->add(new \CorsSlim\CorsSlim($corsOptions));
}
//Override error handler for unhandled exceptions
$app->error(function($err) use ($app) {
    $title = $app->localizer->getText("E_UNHANDLED_EXCEPTION");
    $mimeType = MgMimeType::Html;
    //As part of validating the desired representation, this variable will be set, this will tell us
    //what mime type to shape the response
    if (isset($app->requestedMimeType)) {
        $mimeType = $app->requestedMimeType;
    }
    $details = $app->localizer->getText("E_PHP_EXCEPTION_DETAILS", $err->getMessage(), $err->getFile(), $err->getLine());
    $app->response->header("Content-Type", $mimeType);
    $app->response->setBody(MgUtils::FormatException($app->config("Error.OutputStackTrace"), "UnhandledError", $title, $details, $err->getTraceAsString(), 500, $mimeType));
});
$app->localizer = new Localizer($strings);
//Set server version
$ver = explode(".", SITE_ADMINISTRATOR_VERSION, 4);
$app->MG_VERSION = array(intval($ver[0]), intval($ver[1]), intval($ver[2]), intval($ver[3]));
$app->config("SelfUrl", $app->request->getUrl() . $app->request->getRootUri());
$un = new URL\Normalizer($app->config("SelfUrl") . "/" . $app->config("MapGuide.MapAgentUrl"));
$app->config("MapGuide.MapAgentUrl", $un->normalize());
/*
var_dump($app->localizer->getText("E_METHOD_NOT_SUPPORTED"));
var_dump($app->localizer->getText("E_METHOD_NOT_SUPPORTED", "test"));
var_dump($app->localizer->getText("E_METHOD_NOT_SUPPORTED", 123));
var_dump($app->localizer->getText("I_DONT_EXIST"));
var_dump($app->localizer->getText("I_DONT_EXIST", "test"));
var_dump($app->localizer->getText("I_DONT_EXIST", 123));
var_dump($app->localizer->getText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS"));
var_dump($app->localizer->getText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS", "foo"));
var_dump($app->localizer->getText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS", 123));
var_dump($app->localizer->getText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS", "foo", "bar"));
var_dump($app->localizer->getText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS", 123, "bar"));
var_dump($app->localizer->getText("E_INVALID_COORDINATE_PAIR", "bar", 2));
var_dump($app->localizer->getText("E_INVALID_COORDINATE_PAIR", "bar", "sdf"));
var_dump($app->localizer->getText("E_INVALID_COORDINATE_PAIR", "bar", "234"));
die;
*/
//Register known REST adapters and geom formatters to our DI container
include dirname(__FILE__)."/app/adapters/registration.php";
include dirname(__FILE__)."/app/formatters/registration.php";

//Set the root dir of this file for code that needs to know about it
$app->config("AppRootDir", dirname(__FILE__));
include "app/config.php";

//$namespace is used to uniquely suffix named routes. Otherwise the slim router can throw
//an error about duplicate routes
$namespace = "default";
//Register these routes in the default namespace
include "app/routes/v1/routes.data.php";
include "app/routes/v1/routes.library.php";
include "app/routes/v1/routes.session.php";
include "app/routes/v1/routes.coordsys.php";
include "app/routes/v1/routes.providers.php";
include "app/routes/v1/routes.site.php";
include "app/routes/v1/routes.admin.php";
include "app/routes/v1/routes.services.php";
include "app/routes/v1/routes.processing.php";
include "app/routes/v1/routes.doc.php";
//Now scope the same routes under the v1 namespace. While the above includes may change, the includes
//here cannot. We have effectively set these routes "in stone". Once set, this cannot be changed
$app->group("/v1", function() use ($app) {
    $namespace = "v1";
    include "app/routes/v1/routes.data.php";
    include "app/routes/v1/routes.library.php";
    include "app/routes/v1/routes.session.php";
    include "app/routes/v1/routes.coordsys.php";
    include "app/routes/v1/routes.providers.php";
    include "app/routes/v1/routes.site.php";
    include "app/routes/v1/routes.admin.php";
    include "app/routes/v1/routes.services.php";
    include "app/routes/v1/routes.processing.php";
    include "app/routes/v1/routes.doc.php";
});
/*
$app->get("/", function() {
    echo "Hello World";
});
*/
/*
$app->get("/chunktest", function() use ($app) {
    function dump_chunk($chunk)
    {
        echo sprintf("%x\r\n", strlen($chunk));
        echo $chunk;
        echo "\r\n";
        flush();
        ob_flush();
    }

    while (ob_get_level()) {
        ob_end_flush();
    }
    if (ob_get_length() === false) {
        ob_start();
    }
    //@ini_set("output_buffering", "off");
    header("Transfer-Encoding: chunked");
    header("Content-Type: text/xml");
    //$app->response->header("Content-Type", "text/xml; charset=utf-8");
    flush();
    $userInfo = new MgUserInformation("Anonymous", "");
    $siteConn = new MgSiteConnection();
    $siteConn->Open($userInfo);
    $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
    $fsId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/Parcels.FeatureSource");
    $reader = $featSvc->SelectFeatures($fsId, "SHP_Schema:Parcels", null);
    dump_chunk("<?xml version=\"1.0\" encoding=\"utf-8\"?><queryresult>");

    //ob_flush();
    //flush();
    $count = 0;
    while($reader->ReadNext()) {
        if ($count >= 8000) {
            break;
        }
        $xml = "<feature>";
        for ($i = 0; $i < $reader->GetPropertyCount(); $i++) {
            if (!$reader->IsNull($i)) {
                $xml .= "<property>";
                $xml .= $reader->GetPropertyName($i);
                $xml .= "</property>";
            } else {
                $xml .= "<nullproperty>";
                $xml .= $reader->GetPropertyName($i);
                $xml .= "</nullproperty>";
            }
        }
        $xml .= "</feature>";
        dump_chunk($xml);
        $count++;
    }
    $reader->Close();
    dump_chunk("</queryresult>");
});
*/
$app->run();

?>