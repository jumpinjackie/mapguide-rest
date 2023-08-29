<?php


require_once "featurexmladapter.php";
require_once "geojsonadapter.php";
require_once "mapimageadapter.php";
require_once "templateadapter.php";
require_once "csvadapter.php";
require_once dirname(__FILE__)."/../core/app.php";

$container['FeatureSetXml'] = function($c) {
    return new MgFeatureXmlRestAdapter(
        $c->get('AppServices'), 
        $c->get('MgSiteConnection'),
        $c->get('FeatureSource'),
        $c->get('FeatureClass'),
        $c->get('AdapterConfig'),
        $c->get('ConfigPath'),
        $c->get('IdentityProperty'));
};
$container['FeatureSetXmlDoc'] = function($c) {
    return MgFeatureXmlRestAdapter::GetDocumentor();
};
$container['FeatureSetXmlSessionID'] = function($c) {
    return new MgFeatureXmlSessionIDExtractor();
};
$container['FeatureSetGeoJson'] = function($c) {
    return new MgGeoJsonRestAdapter(
        $c->get('AppServices'), 
        $c->get('MgSiteConnection'),
        $c->get('FeatureSource'),
        $c->get('FeatureClass'),
        $c->get('AdapterConfig'),
        $c->get('ConfigPath'),
        $c->get('IdentityProperty'));
};
$container['FeatureSetGeoJsonDoc'] = function($c) {
    return MgGeoJsonRestAdapter::GetDocumentor();
};
$container['FeatureSetGeoJsonSessionID'] = function($c) {
    return new MgJsonSessionIDExtractor();
};
$container['MapImage'] = function($c) {
    return new MgMapImageRestAdapter(
        $c->get('AppServices'), 
        $c->get('MgSiteConnection'),
        $c->get('FeatureSource'),
        $c->get('FeatureClass'),
        $c->get('AdapterConfig'),
        $c->get('ConfigPath'),
        $c->get('IdentityProperty'));
};
$container['MapImageDoc'] = function($c) {
    return MgMapImageRestAdapter::GetDocumentor();
};
$container['Template'] = function($c) {
    return new MgTemplateRestAdapter(
        $c->get('AppServices'), 
        $c->get('MgSiteConnection'),
        $c->get('FeatureSource'),
        $c->get('FeatureClass'),
        $c->get('AdapterConfig'),
        $c->get('ConfigPath'),
        $c->get('IdentityProperty'));
};
$container['TemplateDoc'] = function($c) {
    return MgTemplateRestAdapter::GetDocumentor();
};
$container['FeatureSetCsv'] = function($c) {
    return new MgCsvRestAdapter(
        $c->get('AppServices'), 
        $c->get('MgSiteConnection'),
        $c->get('FeatureSource'),
        $c->get('FeatureClass'),
        $c->get('AdapterConfig'),
        $c->get('ConfigPath'),
        $c->get('IdentityProperty'));
};
$container['FeatureSetCsvDoc'] = function($c) {
    return MgCsvRestAdapter::GetDocumentor();
};