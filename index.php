<?php

require 'vendor/autoload.php';
require_once dirname(__FILE__)."/app/lib/pimple.php";
include dirname(__FILE__)."/../mapadmin/constants.php";

require_once dirname(__FILE__)."/app/adapters/featurexmladapter.php";
require_once dirname(__FILE__)."/app/adapters/geojsonadapter.php";

$webConfigPath = dirname(__FILE__)."/../webconfig.ini";
MgInitializeWebTier($webConfigPath);

$app = new \Slim\Slim();

$container = new Pimple();
$container["FeatureSetXml"] = function($container) use ($app) {
    return new MgFeatureXmlRestAdapter($app, $container["MgSiteConnection"], $container["FeatureSource"], $container["FeatureClass"], $container["AdapterConfig"]);
};
$container["FeatureSetGeoJson"] = function($container) use ($app) {
    return new MgGeoJsonRestAdapter($app, $container["MgSiteConnection"], $container["FeatureSource"], $container["FeatureClass"], $container["AdapterConfig"]);
};

$app->config("AppRootDir", dirname(__FILE__));
include "app/config.php";
include "app/routes/routes.data.php";
include "app/routes/routes.library.php";
include "app/routes/routes.session.php";
include "app/routes/routes.coordsys.php";
include "app/routes/routes.providers.php";
include "app/routes/routes.site.php";
include "app/routes/routes.admin.php";
$app->get("/", function() {
    echo "Hello World";
});

$app->run();

?>