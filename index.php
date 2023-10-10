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
require dirname(__FILE__)."/app/core/exceptions.php";
require dirname(__FILE__)."/app/polyfill.php";

$webConfigPath = dirname(__FILE__)."/../webconfig.ini";
MgInitializeWebTier($webConfigPath);

require dirname(__FILE__)."/app/util/localizer.php";
require dirname(__FILE__)."/app/util/utils.php";
$config = require dirname(__FILE__)."/app/config.php";
$container = new \Slim\Container($config);

//Register our app services wrapper
$container['AppServices'] = function($c) {
    return new AppServices($c);
};

//Override error handler for unhandled exceptions
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $err) use ($c) {
        if ($err instanceof HaltException) {
            return $response->withStatus($err->getCode())
                            ->withHeader('Content-Type', $err->getMimeType())
                            ->write($err->getMessage());
        } else {
            $wrap = $c->get('AppServices');
            $title = $wrap->GetLocalizedText("E_UNHANDLED_EXCEPTION");
            $mimeType = MgMimeType::Html;
            //As part of validating the desired representation, this variable will be set, this will tell us
            //what mime type to shape the response
            //if (isset($app->requestedMimeType)) {
            //    $mimeType = $app->requestedMimeType;
            //}
            $details = $wrap->GetLocalizedText("E_PHP_EXCEPTION_DETAILS", $err->getMessage(), $err->getFile(), $err->getLine());
            $wrap->SetResponseHeader("Content-Type", $mimeType);
            $wrap->SetResponseBody(MgUtils::FormatException($wrap, "UnhandledError", $title, $details, $err->getTraceAsString(), 500, $mimeType));
            return $wrap->Done();
        }
    };
};

$settings = $container->get('settings');

//Pull in the appropriate string bundle
$strings = require dirname(__FILE__)."/app/res/lang/".$settings["Locale"].".php";

//Register our text localizer
$container['localizer'] = function ($c) use ($strings) {
    return new Localizer($strings);
};

//Set server version
$ver = explode(".", SITE_ADMINISTRATOR_VERSION, 4);
$container['mgVersion'] = function ($c) use ($ver) {
    return array(intval($ver[0]), intval($ver[1]), intval($ver[2]), intval($ver[3]));
};

//Add extra settings
$settings->replace([
    //Set the root dir of this file for code that needs to know about it
    "AppRootDir" => dirname(__FILE__),
    "SelfUrl" => MgUtils::GetSelfUrlRoot("$_SERVER[REQUEST_SCHEME]://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]")
]);

$un = new URL\Normalizer($settings["SelfUrl"] . "/" . $settings["MapGuide.MapAgentUrl"]);
$settings->replace([
    "MapGuide.MapAgentUrl" => $un->normalize()
]);

//Now we can init the main slim app
$app = new \Slim\App($container);

//require dirname(__FILE__)."/app/log_config.php";
//$corsOptions = $container->get('settings')["MapGuide.Cors"];
//if ($corsOptions != null) {
//    $app->add(new \CorsSlim\CorsSlim($corsOptions));
//}
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
// Uncomment below to dump the known route table
/*
$routes = $app->getContainer()->get('router')->getRoutes();
echo "<ul>";
foreach ($routes as $route) {
    foreach ($route->getMethods() as $method) {
        echo "<li>" . ($method . " " . $route->getPattern()) . "</li>";
    }
}
echo "</ul>";
*/
$app->run();

?>