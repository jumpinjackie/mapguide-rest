<?php


require_once "featurexmladapter.php";
require_once "geojsonadapter.php";
require_once "mapimageadapter.php";
require_once "templateadapter.php";
require_once "csvadapter.php";

$app->container->FeatureSetXml = function() use ($app) {
    return new MgFeatureXmlRestAdapter(
        $app, 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath);
};
$app->container->FeatureSetGeoJson = function() use ($app) {
    return new MgGeoJsonRestAdapter(
        $app, 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath);
};
$app->container->MapImage = function() use ($app) {
    return new MgMapImageRestAdapter(
        $app, 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath);
};
$app->container->Template = function() use ($app) {
    return new MgTemplateRestAdapter(
        $app, 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath);
};
$app->container->FeatureSetCsv = function() use ($app) {
    return new MgCsvRestAdapter(
        $app, 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath);
};

?>