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

require_once dirname(__FILE__)."/../../controller/coordinatesystemcontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";
require_once dirname(__FILE__)."/../../core/app.php";

/**
 *     @SWG\Get(
 *        path="/coordsys/baselibrary.{type}",
 *        operationId="GetBaseLibrary",
 *        summary="Returns the base library name for this coordinate system library",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/baselibrary.{format}", function($req, $resp, $args) {
    $format = $args['format'];
    $app = $this->get("AppServices");
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->GetBaseLibrary($format);
    return $app->Done();

});
/**
 *     @SWG\Post(
 *        path="/coordsys/validatewkt.{type}",
 *        operationId="ValidateWkt",
 *        summary="Checks if the given coordinate system WKT string is valid",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="wkt", in="formData", required=false, type="string", description="The Coordinate System WKT string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/coordsys/validatewkt.{format}", function($req, $resp, $args) {
    $format = $args['format'];
    $app = $this->get("AppServices");
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ValidateWkt($format);
    return $app->Done();
});
/**
 *     @SWG\Post(
 *        path="/coordsys/wkttoepsg.{type}",
 *        operationId="WktToEpsg",
 *        summary="Converts the given Coordinate System WKT string to its equivalent EPSG code",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="wkt", in="formData", required=false, type="string", description="The Coordinate System WKT string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/coordsys/wkttoepsg.:format", function($format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->WktToEpsg($format);
});
/**
 *     @SWG\Post(
 *         path="/coordsys/wkttomentor.{type}",
 *        operationId="WktToMentor",
 *        summary="Converts the given Coordinate System WKT string to its equivalent CS-Map coordinate system code",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="wkt", in="formData", required=false, type="string", description="The Coordinate System WKT string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/coordsys/wkttomentor.:format", function($format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->WktToMentor($format);
});
/**
 *     @SWG\Get(
 *        path="/coordsys/categories.{type}",
 *        operationId="EnumerateCategories",
 *        summary="Enumerates coordinate system categories and returns the result in the desired format",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/categories.{format}", function($req, $resp, $args) {
    $format = $args['format'];
    $app = $this->get("AppServices");
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCategories($format);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/coordsys/category/{category}.{type}",
 *        operationId="EnumerateCoordinateSystemsByCategory",
 *        summary="Enumerates coordinate systems under the specified category",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="category", in="path", required=true, type="string", description="The Coordinate System Category"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}, enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/category.:format/:category", function($format, $category) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->EnumerateCoordinateSystemsByCategory($category, $format);
});
/**
 *     @SWG\Get(
 *        path="/coordsys/mentor/{cscode}/epsg.{type}",
 *        operationId="ConvertCsCodeToEpsg",
 *        summary="Converts the given CS-Map coordinate system code to EPSG",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="cscode", in="path", required=true, type="string", description="The CS-Map Coordinate System Code"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/mentor/:cscode/epsg.:format", function($cscode, $format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertCsCodeToEpsg($cscode, $format);
});
/**
 *     @SWG\Get(
 *        path="/coordsys/mentor/{cscode}/wkt.{type}",
 *        operationId="ConvertCsCodeToWkt",
 *        summary="Converts the given CS-Map coordinate system code to WKT",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="cscode", in="path", required=true, type="string", description="The CS-Map Coordinate System Code"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/mentor/:cscode/wkt.:format", function($cscode, $format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertCsCodeToWkt($cscode, $format); 
});
/**
 *     @SWG\Get(
 *        path="/coordsys/epsg/{epsg}/mentor.{type}",
 *        operationId="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to its CS-Map coordinate system code",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="epsg", in="path", required=true, type="string", description="The EPSG code"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/epsg/:epsg/mentor.:format", function($epsg, $format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertEpsgToCsCode($epsg, $format);
});
/**
 *     @SWG\Get(
 *        path="/coordsys/epsg/{epsg}/wkt.{type}",
 *        operationId="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to WKT",
 *        tags={"coordsys"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="epsg", in="path", required=true, type="string", description="The EPSG code"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/coordsys/epsg/:epsg/wkt.:format", function($epsg, $format) {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertEpsgToWkt($epsg, $format);
});
/*
$app->post("/coordsys/tomentor/:wkt+", function($wkt) {
    $wktStr = implode("/", $wkt);
    echo $wktStr;
    die;
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertWktToCsCode($wktStr);
});
$app->post("/coordsys/toepsg/:wkt+", function($wkt) {
    $wktStr = implode("/", $wkt);
    echo $wktStr;
    die;
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->ConvertWktToEpsg($wktStr);
});
*/