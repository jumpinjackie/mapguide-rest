<?php


require_once "featurexmladapter.php";
require_once "geojsonadapter.php";
require_once "mapimageadapter.php";
require_once "templateadapter.php";
require_once "csvadapter.php";
require_once dirname(__FILE__)."/../core/app.php";

$app->container->FeatureSetXml = function() use ($app) {
    return new MgFeatureXmlRestAdapter(
        new AppServices($app), 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath,
        $app->container->IdentityProperty);
};
$app->container->FeatureSetXmlDoc = function() use ($app) {
    return MgFeatureXmlRestAdapter::GetDocumentor();
};
$app->container->FeatureSetXmlSessionID = function() use ($app) {
    return new MgFeatureXmlSessionIDExtractor();
};
$app->container->FeatureSetGeoJson = function() use ($app) {
    return new MgGeoJsonRestAdapter(
        new AppServices($app), 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath,
        $app->container->IdentityProperty);
};
$app->container->FeatureSetGeoJsonDoc = function() use ($app) {
    return MgGeoJsonRestAdapter::GetDocumentor();
};
$app->container->FeatureSetGeoJsonSessionID = function() use ($app) {
    return new MgJsonSessionIDExtractor();
};
$app->container->MapImage = function() use ($app) {
    return new MgMapImageRestAdapter(
        new AppServices($app), 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath,
        $app->container->IdentityProperty);
};
$app->container->MapImageDoc = function() use ($app) {
    return MgMapImageRestAdapter::GetDocumentor();
};
$app->container->Template = function() use ($app) {
    return new MgTemplateRestAdapter(
        new AppServices($app), 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath,
        $app->container->IdentityProperty);
};
$app->container->TemplateDoc = function() use ($app) {
    return MgTemplateRestAdapter::GetDocumentor();
};
$app->container->FeatureSetCsv = function() use ($app) {
    return new MgCsvRestAdapter(
        new AppServices($app), 
        $app->container->MgSiteConnection, 
        $app->container->FeatureSource,
        $app->container->FeatureClass,
        $app->container->AdapterConfig,
        $app->container->ConfigPath,
        $app->container->IdentityProperty);
};
$app->container->FeatureSetCsvDoc = function() use ($app) {
    return MgCsvRestAdapter::GetDocumentor();
};