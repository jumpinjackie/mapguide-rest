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

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="Coordinate System Catalog",
 *      resourcePath="/coordsys"
 * )
 */

/**
 * @SWG\Api(
 *     path="/coordsys/baselibrary",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetBaseLibrary",
 *        summary="Returns the base library name for this coordinate system library",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/baselibrary", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->GetBaseLibrary();
});
/**
 * @SWG\Api(
 *     path="/coordsys/validatewkt",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="ValidateWkt",
 *        summary="Checks if the given coordinate system WKT string is valid",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="wkt", paramType="form", required=false, type="string", description="The Coordinate System WKT string")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/coordsys/validatewkt", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ValidateWkt();
});
/**
 * @SWG\Api(
 *     path="/coordsys/wkttoepsg",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="WktToEpsg",
 *        summary="Converts the given Coordinate System WKT string to its equivalent EPSG code",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="wkt", paramType="form", required=false, type="string", description="The Coordinate System WKT string")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/coordsys/wkttoepsg", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->WktToEpsg();
});
/**
 * @SWG\Api(
 *     path="/coordsys/wkttomentor",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="WktToMentor",
 *        summary="Converts the given Coordinate System WKT string to its equivalent CS-Map coordinate system code",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="wkt", paramType="form", required=false, type="string", description="The Coordinate System WKT string")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/coordsys/wkttomentor", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->WktToMentor();
});
/**
 * @SWG\Api(
 *     path="/coordsys/categories",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateCategories",
 *        summary="Enumerates coordinate system categories",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/categories", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCategories("xml");
});
/**
 * @SWG\Api(
 *     path="/coordsys/categories.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateCategories",
 *        summary="Enumerates coordinate system categories and returns the result in the desired format",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/categories.:format", function($format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCategories($format); 
});
/**
 * @SWG\Api(
 *     path="/coordsys/category/{category}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateCoordinateSystemsByCategory",
 *        summary="Enumerates coordinate systems under the specified category",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="category", paramType="path", required=true, type="string", description="The Coordinate System Category")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/category/:category", function($category) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCoordinateSystemsByCategory($category, "xml");
});
/**
 * @SWG\Api(
 *     path="/coordsys/category/{category}.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateCoordinateSystemsByCategory",
 *        summary="Enumerates coordinate systems under the specified category",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="category", paramType="path", required=true, type="string", description="The Coordinate System Category"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/category.:format/:category", function($format, $category) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->EnumerateCoordinateSystemsByCategory($category, $format);
});
/**
 * @SWG\Api(
 *     path="/coordsys/mentor/{cscode}/epsg",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertCsCodeToEpsg",
 *        summary="Converts the given CS-Map coordinate system code to EPSG",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="cscode", paramType="path", required=true, type="string", description="The CS-Map Coordinate System Code")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/mentor/:cscode/epsg", function($cscode) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToEpsg($cscode, "xml");
});
/**
 * @SWG\Api(
 *     path="/coordsys/mentor/{cscode}/epsg.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertCsCodeToEpsg",
 *        summary="Converts the given CS-Map coordinate system code to EPSG",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="cscode", paramType="path", required=true, type="string", description="The CS-Map Coordinate System Code"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/mentor/:cscode/epsg.:format", function($cscode, $format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToEpsg($cscode, $format);
});
/**
 * @SWG\Api(
 *     path="/coordsys/mentor/{cscode}/wkt",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertCsCodeToWkt",
 *        summary="Converts the given CS-Map coordinate system code to WKT",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="cscode", paramType="path", required=true, type="string", description="The CS-Map Coordinate System Code")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/mentor/:cscode/wkt", function($cscode) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToWkt($cscode, "xml"); 
});
/**
 * @SWG\Api(
 *     path="/coordsys/mentor/{cscode}/wkt.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertCsCodeToWkt",
 *        summary="Converts the given CS-Map coordinate system code to WKT",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="cscode", paramType="path", required=true, type="string", description="The CS-Map Coordinate System Code"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/mentor/:cscode/wkt.:format", function($cscode, $format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertCsCodeToWkt($cscode, $format); 
});
/**
 * @SWG\Api(
 *     path="/coordsys/epsg/{epsg}/mentor",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to its CS-Map coordinate system code",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="epsg", paramType="path", required=true, type="string", description="The EPSG code")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/epsg/:epsg/mentor", function($epsg) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToCsCode($epsg, "xml");
});
/**
 * @SWG\Api(
 *     path="/coordsys/epsg/{epsg}/mentor.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to its CS-Map coordinate system code",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="epsg", paramType="path", required=true, type="string", description="The EPSG code"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/epsg/:epsg/mentor.:format", function($epsg, $format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToCsCode($epsg, $format);
});
/**
 * @SWG\Api(
 *     path="/coordsys/epsg/{epsg}/wkt",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to WKT",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="epsg", paramType="path", required=true, type="string", description="The EPSG code")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/epsg/:epsg/wkt", function($epsg) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToWkt($epsg, "xml");
});
/**
 * @SWG\Api(
 *     path="/coordsys/epsg/{epsg}/wkt.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="ConvertEpsgToCsCode",
 *        summary="Converts the given EPSG code to WKT",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="epsg", paramType="path", required=true, type="string", description="The EPSG code"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/coordsys/epsg/:epsg/wkt.:format", function($epsg, $format) use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->ConvertEpsgToWkt($epsg, $format);
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