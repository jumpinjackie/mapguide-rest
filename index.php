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
    $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];

require 'vendor/autoload.php';
include dirname(__FILE__)."/../mapadmin/constants.php";

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

$config = require_once dirname(__FILE__)."/app/config.php";
$app = new \Slim\Slim($config);
$app->config("SelfUrl", $app->request->getUrl() . $app->request->getRootUri());

//Register known REST adapters and geom formatters to our DI container
include dirname(__FILE__)."/app/adapters/registration.php";
include dirname(__FILE__)."/app/formatters/registration.php";

//Set the root dir of this file for code that needs to know about it
$app->config("AppRootDir", dirname(__FILE__));
include "app/config.php";
include "app/routes/routes.data.php";
include "app/routes/routes.library.php";
include "app/routes/routes.session.php";
include "app/routes/routes.coordsys.php";
include "app/routes/routes.providers.php";
include "app/routes/routes.site.php";
include "app/routes/routes.admin.php";
include "app/routes/routes.services.php";
include "app/routes/routes.doc.php";
/*
$app->get("/", function() {
    echo "Hello World";
});
*/
$app->run();

?>