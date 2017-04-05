<?php

//
//  Copyright (C) 2017 by Jackie Ng
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

require_once dirname(__FILE__)."/../../controller/processingcontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";

/**
 *     @SWG\Post(
 *        path="/processing/buffer",
 *        operationId="Buffer",
 *        summary="Performs a buffer operation",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="coordsys", in="formData", required=true, type="string", description="The coordinate system code of the input geometry"),
 *          @SWG\Parameter(name="distance", in="formData", required=true, type="number", description="The distance to buffer"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The desired output format of the buffered geometry. wkt or geojson", enum={"wkt", "geojson"}),
 *          @SWG\Parameter(name="units", in="formData", required=false, type="integer", description="The distance units. If not specified it will be assumed to be meters", enum={"m", "km", "mi", "ft"}),
 *          @SWG\Parameter(name="transformto", in="formData", required=false, type="integer", description="The coordinate system code to transform the buffered geometry to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/buffer", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->Buffer();
});

/**
 *     @SWG\Post(
 *        path="/processing/difference",
 *        operationId="Difference",
 *        summary="Performs a difference operation and returns a geometry that represents a point set difference between this geometric entity and another. Both geometries must be in the same coordinate system",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry_a", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="geometry_b", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The desired output format of the buffered geometry. wkt or geojson", enum={"wkt", "geojson"}),
 *          @SWG\Parameter(name="transformto", in="formData", required=false, type="integer", description="The coordinate system code to transform the buffered geometry to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/difference", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->Difference();
});

/**
 *     @SWG\Post(
 *        path="/processing/intersection",
 *        operationId="Intersection",
 *        summary="Performs a intersection operation and returns a geometry that represents the point set intersection of this geometry and another. Both geometries must be in the same coordinate system",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry_a", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="geometry_b", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The desired output format of the buffered geometry. wkt or geojson", enum={"wkt", "geojson"}),
 *          @SWG\Parameter(name="transformto", in="formData", required=false, type="integer", description="The coordinate system code to transform the buffered geometry to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/intersection", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->Intersection();
});

/**
 *     @SWG\Post(
 *        path="/processing/symmetricdifference",
 *        operationId="SymmetricDifference",
 *        summary="Performs a symmetric difference operation and returns a geometry that represents the point set symmetric difference of this geometry with another. Both geometries must be in the same coordinate system",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry_a", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="geometry_b", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The desired output format of the buffered geometry. wkt or geojson", enum={"wkt", "geojson"}),
 *          @SWG\Parameter(name="transformto", in="formData", required=false, type="integer", description="The coordinate system code to transform the buffered geometry to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/symmetricdifference", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->SymmetricDifference();
});

/**
 *     @SWG\Post(
 *        path="/processing/union",
 *        operationId="Union",
 *        summary="Performs a union operation and returns a geometry that represents the point set union of this geometry with another. Both geometries must be in the same coordinate system",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry_a", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="geometry_b", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The desired output format of the buffered geometry. wkt or geojson", enum={"wkt", "geojson"}),
 *          @SWG\Parameter(name="transformto", in="formData", required=false, type="integer", description="The coordinate system code to transform the buffered geometry to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/union", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->Union();
});

/**
 *     @SWG\Post(
 *        path="/processing/spatialpredicate",
 *        operationId="SpatialPredicate",
 *        summary="Performs a spatial predicate test between the two specified geometries. Both geometries must be in the same coordinate system",
 *        tags={"processing"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="geometry_a", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="geometry_b", in="formData", required=true, type="string", description="The geometry WKT"),
 *          @SWG\Parameter(name="operator", in="formData", required=true, type="string", description="The spatial operator to test", enum={"contains", "crosses", "disjoint", "equals", "intersects", "overlaps", "touches", "within"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/processing/spatialpredicate", function() use ($app) {
    $ctrl = new MgProcessingController($app);
    $ctrl->SpatialPredicate();
});