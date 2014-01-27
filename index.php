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

require 'vendor/autoload.php';
require_once dirname(__FILE__)."/app/lib/pimple.php";
include dirname(__FILE__)."/../mapadmin/constants.php";

require_once dirname(__FILE__)."/app/adapters/featurexmladapter.php";
require_once dirname(__FILE__)."/app/adapters/geojsonadapter.php";
require_once dirname(__FILE__)."/app/adapters/mapimageadapter.php";

$webConfigPath = dirname(__FILE__)."/../webconfig.ini";
MgInitializeWebTier($webConfigPath);

$app = new \Slim\Slim();

//Register known REST adapters to our pimple container
$container = new Pimple();
$container["FeatureSetXml"] = function($container) use ($app) {
    return new MgFeatureXmlRestAdapter($app, $container["MgSiteConnection"], $container["FeatureSource"], $container["FeatureClass"], $container["AdapterConfig"]);
};
$container["FeatureSetGeoJson"] = function($container) use ($app) {
    return new MgGeoJsonRestAdapter($app, $container["MgSiteConnection"], $container["FeatureSource"], $container["FeatureClass"], $container["AdapterConfig"]);
};
$container["MapImage"] = function($container) use ($app) {
    return new MgMapImageRestAdapter($app, $container["MgSiteConnection"], $container["FeatureSource"], $container["FeatureClass"], $container["AdapterConfig"]);
};

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
/*
$app->get("/", function() {
    echo "Hello World";
});
*/
$app->run();

?>