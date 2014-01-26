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
    $ctrl->ConvertEpsgToWkt($epsg);
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