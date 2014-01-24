<?php

require_once dirname(__FILE__)."/../controller/coordinatesystemcontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

$app->get("/coordsys/categories", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCategories("xml");
});
$app->get("/coordsys/categories.:format", function($format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCategories($format); 
});
$app->get("/coordsys/category/:category", function($category) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCoordinateSystemsByCategory($category, "xml");
});
$app->get("/coordsys/category.:format/:category", function($format, $category) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCoordinateSystemsByCategory($category, $format);
});
$app->get("/coordsys/mentor/:cscode/epsg", function($cscode) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToEpsg($cscode);
});
$app->get("/coordsys/mentor/:cscode/wkt", function($cscode) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToWkt($cscode); 
});
$app->get("/coordsys/epsg/:epsg/mentor", function($epsg) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToCsCode($epsg);
});
$app->get("/coordsys/epsg/:epsg/wkt", function($epsg) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToWkt($wkt);
});
/*
$app->post("/coordsys/tomentor/:wkt+", function($wkt) use ($app) {
    $wktStr = implode("/", $wkt);
    echo $wktStr;
    die;
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertWktToCsCode($wktStr);
});
$app->post("/coordsys/toepsg/:wkt+", function($wkt) use ($app) {
    $wktStr = implode("/", $wkt);
    echo $wktStr;
    die;
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertWktToEpsg($wktStr);
});
*/

?>