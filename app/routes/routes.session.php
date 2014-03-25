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


require_once dirname(__FILE__)."/../controller/restservicecontroller.php";
require_once dirname(__FILE__)."/../controller/mapcontroller.php";
require_once dirname(__FILE__)."/../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../controller/tileservicecontroller.php";
require_once dirname(__FILE__)."/../controller/mappingservicecontroller.php";
require_once dirname(__FILE__)."/../controller/renderingservicecontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="Session Repository",
 *      resourcePath="/session"
 * )
 */

/**
 * @SWG\Api(
 *     path="/session",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="CreateSession",
 *        summary="Creates a new MapGuide session",
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session", function() use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->CreateSession();
});
/**
 * @SWG\Api(
 *     path="/session/{session}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DestroySession",
 *        summary="Destroys the specified session",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/session/:sessionId", function($sessionId) use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->DestroySession($sessionId);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/timeout",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSessionTimeout",
 *        summary="Gets the session timeout of the specified session",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/timeout", function($sessionId) use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->GetSessionTimeout($sessionId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/timeout.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSessionTimeout",
 *        summary="Gets the session timeout of the specified session",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/timeout.:format", function($sessionId, $format) use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->GetSessionTimeout($sessionId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/image.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="RenderRuntimeMap",
 *        summary="Renders an image of the specified runtime map. Will also modify the map's state based on the parameters you specify",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to render"),
 *          @SWG\parameter(name="width", paramType="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=false, type="integer", description="The height of the image"),
 *          @SWG\parameter(name="keepselection", paramType="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\parameter(name="clip", paramType="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\parameter(name="dpi", paramType="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The image type", enum="['PNG','PNG8','JPG','GIF']"),
 *          @SWG\parameter(name="showlayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="showgroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *          @SWG\parameter(name="hidelayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="hidegroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/image.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderRuntimeMap($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/overlayimage.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="RenderDynamicOverlayImage",
 *        summary="Renders a dynamic overlay image of the specified runtime map. Will also modify the map's state based on the parameters you specify",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="selectioncolor", paramType="path", required=true, type="string", description="The selection color as HTML color string"),
 *          @SWG\parameter(name="behavior", paramType="path", required=true, type="integer", description="A bitmask controlling rendering behavior. 1=Render Selection, 2=Render layers, 4=Keep Selection, 8=Render Base Layers (only for MapGuide Open Source 2.5 and above)"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to render"),
 *          @SWG\parameter(name="width", paramType="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=false, type="integer", description="The height of the image"),
 *          @SWG\parameter(name="keepselection", paramType="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\parameter(name="clip", paramType="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\parameter(name="dpi", paramType="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The image type", enum="['PNG','PNG8','JPG','GIF']"),
 *          @SWG\parameter(name="showlayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="showgroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *          @SWG\parameter(name="hidelayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="hidegroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/overlayimage.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderDynamicOverlayImage($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/layers",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateMapLayers",
 *        summary="Gets the layers of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/layers", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayers($sessionId, $mapName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/layers.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateMapLayers",
 *        summary="Gets the layers of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/layers.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayers($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/layergroups",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateMapLayerGroups",
 *        summary="Gets the layer groups of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/layergroups", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayerGroups($sessionId, $mapName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/layergroups.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateMapLayerGroups",
 *        summary="Gets the layer groups of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/layergroups.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayerGroups($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/plot",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GeneratePlot",
 *        summary="Plot the map to an EPlot DWF using the center and scale from the map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to plot"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to plot"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to plot"),
 *          @SWG\parameter(name="pagewidth", paramType="query", required=false, type="double", description="page width in inches"),
 *          @SWG\parameter(name="pageheight", paramType="query", required=false, type="double", description="page height in inches"),
 *          @SWG\parameter(name="marginleft", paramType="query", required=false, type="double", description="left margin in inches"),
 *          @SWG\parameter(name="marginright", paramType="query", required=false, type="double", description="right margin in inches"),
 *          @SWG\parameter(name="margintop", paramType="query", required=false, type="double", description="top margin in inches"),
 *          @SWG\parameter(name="marginbottom", paramType="query", required=false, type="double", description="bottom margin in inches"),
 *          @SWG\parameter(name="printlayout", paramType="query", required=false, type="string", description="The PrintLayout resource to use for plotting"),
 *          @SWG\parameter(name="title", paramType="query", required=false, type="string", description="The title to put in the plot")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/plot", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GeneratePlot($sessionId, $mapName, "dwf");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/plot.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GeneratePlot",
 *        summary="Plot the map to the specified using the center and scale from the map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to plot"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to plot"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to plot"),
 *          @SWG\parameter(name="pagewidth", paramType="query", required=false, type="double", description="page width in inches"),
 *          @SWG\parameter(name="pageheight", paramType="query", required=false, type="double", description="page height in inches"),
 *          @SWG\parameter(name="marginleft", paramType="query", required=false, type="double", description="left margin in inches"),
 *          @SWG\parameter(name="marginright", paramType="query", required=false, type="double", description="right margin in inches"),
 *          @SWG\parameter(name="margintop", paramType="query", required=false, type="double", description="top margin in inches"),
 *          @SWG\parameter(name="marginbottom", paramType="query", required=false, type="double", description="bottom margin in inches"),
 *          @SWG\parameter(name="printlayout", paramType="query", required=false, type="string", description="The PrintLayout resource to use for plotting. Only applies if plotting to DWF"),
 *          @SWG\parameter(name="title", paramType="query", required=false, type="string", description="The title to put in the plot"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The plot type", enum="['dwf']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/plot.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GeneratePlot($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/xml",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectionXml",
 *        summary="Gets the selection XML of the given map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Selection/xml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->GetSelectionXml($sessionId, $mapName);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/layers",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectionLayerNames",
 *        summary="Gets the layers of the current selection set",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Selection/layers", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->GetSelectionLayerNames($sessionId, $mapName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/layers.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectionLayerNames",
 *        summary="Gets the layers of the current selection set",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Selection/layers.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->GetSelectionLayerNames($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/features/{layerName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectedFeatures",
 *        summary="Gets the features from the given layer in the selection set",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="layerName", paramType="path", required=true, type="string", description="The name of the layer in the selection set"),
 *          @SWG\parameter(name="mappedonly", paramType="path", required=true, type="boolean", description="Only return properties mapped in the Layer Definition"),
 *          @SWG\parameter(name="transformto", paramType="path", required=true, type="string", description="The CS-Map coordinate system code to transform these features to")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Selection/features/:layerName", function($sessionId, $mapName, $layerName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->GetSelectedFeatures($sessionId, $mapName, $layerName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/features.{type}/{layerName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectedFeatures",
 *        summary="Gets the features from the given layer in the selection set",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="layerName", paramType="path", required=true, type="string", description="The name of the layer in the selection set"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','geojson']"),
 *          @SWG\parameter(name="mappedonly", paramType="path", required=true, type="boolean", description="Only return properties mapped in the Layer Definition"),
 *          @SWG\parameter(name="transformto", paramType="path", required=true, type="string", description="The CS-Map coordinate system code to transform these features to")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Selection/features.:format/:layerName", function($sessionId, $mapName, $format, $layerName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->GetSelectedFeatures($sessionId, $mapName, $layerName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/xml",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="UpdateSelectionFromXml",
 *        summary="Updates selection XML of the given map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The new selection XML")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session/:sessionId/:mapName.Selection/xml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->UpdateSelectionFromXml($sessionId, $mapName);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection",
 *     @SWG\Operation(
 *        method="PUT",
 *        nickname="QueryMapFeatures",
 *        summary="Updates the map selection according to some spatial critieria",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="layernames", paramType="form", required=false, type="string", description="A comma-separated list of layer names"),
 *          @SWG\parameter(name="geometry", paramType="form", required=true, type="string", description="The WKT of the intersecting geometry"),
 *          @SWG\parameter(name="maxfeatures", paramType="form", required=false, type="integer", description="The maximum number features to select as a result of this operation"),
 *          @SWG\parameter(name="selectionvariant", paramType="form", required=true, type="string", description="The geometry operator to apply", enum="['TOUCHES','INTERSECTS','WITHIN','ENVELOPEINTERSECTS']"),
 *          @SWG\parameter(name="selectioncolor", paramType="form", required=false, type="string", description="The selection color"),
 *          @SWG\parameter(name="selectionformat", paramType="form", required=false, type="string", description="The selection image format", enum="['PNG','PNG8','JPG','GIF']"),
 *          @SWG\parameter(name="persist", paramType="form", required=false, type="boolean", description="If true, will cause this operation to modify the selection set"),
 *          @SWG\parameter(name="requestdata", paramType="form", required=true, type="string", description="A bitmask specifying the information to return in the response. 1=Attributes, 2=Inline Selection, 4=Tooltip, 8=Hyperlink"),
 *          @SWG\parameter(name="featurefilter", paramType="form", required=false, type="string", description="An XML selection string containing the required feature IDs"),
 *          @SWG\parameter(name="layerattributefilter", paramType="form", required=false, type="string", description="Bitmask value determining which layers will be queried. 1=Visible, 2=Selectable, 4=HasTooltips"),
 *          @SWG\parameter(name="format", paramType="form", required=false, type="string", description="The format of the response", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->put("/session/:sessionId/:mapName.Selection", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->QueryMapFeatures($sessionId, $mapName);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetResource",
 *        summary="Sets the resource XML for a session-based resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the session resource (including extension)"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The new selection XML")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session/:sessionId/:resName", function($sessionId, $resName) use($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    if ($resId->GetResourceType() == MgResourceType::Map) {
        $ctrl = new MgMapController($app);
        $ctrl->CreateMap($resId);
    } else {
        $ctrl = new MgResourceServiceController($app);
        $ctrl->SetResource($resId);
    }
})->name("session_resource_id");

//
// NOTE:
// Although the session repository allows for resources of multiple depth, for the sake of simplicity the REST API only
// allows for interaction with session resources at the root of the repository. This is already the most common
// scenario and the one we will support (for now).
//

//======================== Feature Service APIs ===========================

$app->get("/session/:sessionId/:resName.FeatureSource/spatialcontexts", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/spatialcontexts.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/schemas", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/schemas.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/schema/:schemaName", function($sessionId, $resName, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/schema.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/classes/:schemaName", function($sessionId, $resName, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/classes.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/classdef/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/classdef.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/classdef/:qualifiedClassName", function($sessionId, $resName, $qualifiedClassName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $tokens = explode(":", $qualifiedClassName);
    $schemaName = "";
    $className = "";
    if (count($tokens) == 2) {
        $schemaName = $tokens[0];
        $className = $tokens[1];
    } else {
        $className = $qualifiedClassName;
    }
    $ctrl->GetClassDefinition($resId, $schemaName, $className, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/classdef.:format/:qualifiedClassName", function($sessionId, $resName, $format, $qualifiedClassName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $tokens = explode(":", $qualifiedClassName);
    $schemaName = "";
    $className = "";
    if (count($tokens) == 2) {
        $schemaName = $tokens[0];
        $className = $tokens[1];
    } else {
        $className = $qualifiedClassName;
    }
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
$app->post("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->InsertFeatures($resId, $schemaName, $className);
});
$app->put("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->UpdateFeatures($resId, $schemaName, $className);
});
$app->delete("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DeleteFeatures($resId, $schemaName, $className);
});
$app->get("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
$app->get("/session/:sessionId/:resName.FeatureSource/aggregates/:type/:schemaName/:className", function($sessionId, $resName, $type, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, "xml");
});
$app->get("/session/:sessionId/:resName.FeatureSource/aggregates.:format/:type/:schemaName/:className", function($sessionId, $resName, $format, $type, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, $format);
});
// Resource Service APIs
$app->get("/session/:sessionId/:resName/datalist", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, "xml");
});
$app->get("/session/:sessionId/:resName/datalist.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
$app->get("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceData($resId, $dataName);
});
/*
//Need to confirm if like EnumerateResources, this is not permitted on session repos
$app->get("/session/:sessionId/:resName/header", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, "xml");
});
$app->get("/session/:sessionId/:resName/header.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, $format);
});
*/
$app->post("/session/:sessionId/:resName/content", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContent($resId);
});
$app->get("/session/:sessionId/:resName/content", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, "xml");
});
$app->get("/session/:sessionId/:resName/content.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, $format);
});
$app->get("/session/:sessionId/:resName/references", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, "xml");
});
$app->get("/session/:sessionId/:resName/references.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, $format);
});
$app->delete("/session/:sessionId/:resName", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResource($resId); 
});
// Tile Service APIs
$app->get("/session/:sessionId/:resName/tile/:groupName/:scaleIndex/:col/:row", function($sessionId, $resName, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, "img");
});
$app->get("/session/:sessionId/:resName/tile.:format/:groupName/:scaleIndex/:col/:row", function($sessionId, $resName, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});
// Mapping Service APIs
$app->get("/session/:sessionId/:resName.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($sessionId, $resName, $scale, $geomtype, $themecat, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
// Rendering Service APIs
$app->get("/session/:sessionId/:resName.MapDefinition/image.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderMapDefinition($resId, $format);
});

?>