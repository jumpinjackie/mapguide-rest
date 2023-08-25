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


require_once dirname(__FILE__)."/../../controller/restservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/mapcontroller.php";
require_once dirname(__FILE__)."/../../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/tileservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/mappingservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/renderingservicecontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";
require_once dirname(__FILE__)."/../../core/app.php";

/**
 *     @SWG\Post(
 *        path="/session.{type}",
 *        operationId="CreateSession",
 *        summary="Creates a new MapGuide session",
 *        tags={"session"},
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="username", in="formData", required=true, type="string", description="The MapGuide username"),
 *          @SWG\Parameter(name="password", in="formData", required=false, type="string", description="The password"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session.:format", function($format) use ($app) {
    $ctrl = new MgRestServiceController(new AppServices($app));
    $ctrl->CreateSession($format);
});
/**
 *     @SWG\Delete(
 *        path="/session/{session}",
 *        operationId="DestroySession",
 *        summary="Destroys the specified session",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/session/:sessionId", function($sessionId) use ($app) {
    $ctrl = new MgRestServiceController(new AppServices($app));
    $ctrl->DestroySession($sessionId);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/timeout.{type}",
 *        operationId="GetSessionTimeout",
 *        summary="Gets the session timeout of the specified session",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/timeout.:format", function($sessionId, $format) use ($app) {
    $ctrl = new MgRestServiceController(new AppServices($app));
    $ctrl->GetSessionTimeout($sessionId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/image.{type}",
 *        operationId="RenderRuntimeMap",
 *        summary="Renders an image of the specified runtime map. Will also modify the map's state based on the parameters you specify",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="x", in="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\Parameter(name="y", in="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\Parameter(name="scale", in="query", required=true, type="number", description="The map scale to render"),
 *          @SWG\Parameter(name="width", in="query", required=true, type="integer", description="The width of the image"),
 *          @SWG\Parameter(name="height", in="query", required=true, type="integer", description="The height of the image"),
 *          @SWG\Parameter(name="keepselection", in="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\Parameter(name="clip", in="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\Parameter(name="dpi", in="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The image type", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *          @SWG\Parameter(name="showlayers", in="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\Parameter(name="showgroups", in="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *          @SWG\Parameter(name="hidelayers", in="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\Parameter(name="hidegroups", in="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/image.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController(new AppServices($app));
    $ctrl->RenderRuntimeMap($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/overlayimage.{type}",
 *        operationId="RenderDynamicOverlayImage",
 *        summary="Renders a dynamic overlay image of the specified runtime map. Will also modify the map's state based on the parameters you specify",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="selectioncolor", in="path", required=true, type="string", description="The selection color as HTML color string"),
 *          @SWG\Parameter(name="behavior", in="path", required=true, type="integer", description="A bitmask controlling rendering behavior. 1=Render Selection, 2=Render layers, 4=Keep Selection, 8=Render Base Layers (only for MapGuide Open Source 2.5 and above)"),
 *          @SWG\Parameter(name="x", in="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\Parameter(name="y", in="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\Parameter(name="scale", in="query", required=true, type="number", description="The map scale to render"),
 *          @SWG\Parameter(name="width", in="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\Parameter(name="height", in="query", required=false, type="integer", description="The height of the image"),
 *          @SWG\Parameter(name="keepselection", in="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\Parameter(name="clip", in="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\Parameter(name="dpi", in="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The image type", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *          @SWG\Parameter(name="showlayers", in="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\Parameter(name="showgroups", in="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *          @SWG\Parameter(name="hidelayers", in="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\Parameter(name="hidegroups", in="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/overlayimage.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController(new AppServices($app));
    $ctrl->RenderDynamicOverlayImage($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/legendimage.{type}",
 *        operationId="GetMapLegendImage",
 *        summary="Renders a legend image of the specified runtime map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="width", in="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\Parameter(name="height", in="query", required=false, type="integer", description="The height of the image"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The image type", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/legendimage.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController(new AppServices($app));
    $ctrl->RenderRuntimeMapLegend($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/layers.{type}",
 *        operationId="EnumerateMapLayers",
 *        summary="Gets the layers of the specified runtime map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="requestedfeatures", in="query", required=false, type="integer", description="A bitmask of the additional information that you would like returned. 2=icons, 4=Feature Source Information"),
 *          @SWG\Parameter(name="iconformat", in="query", required=false, type="string", description="The desired icon image format if icons are requested", enum={"PNG","JPG","PNG8","GIF"}),
 *          @SWG\Parameter(name="iconwidth", in="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconheight", in="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconsperscalerange", in="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\Parameter(name="group", in="query", required=false, type="string", description="Only return layers belonging to the specified group"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/layers.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->EnumerateMapLayers($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/layergroups.{type}",
 *        operationId="EnumerateMapLayerGroups",
 *        summary="Gets the layer groups of the specified runtime map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/layergroups.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->EnumerateMapLayerGroups($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/plot.{type}",
 *        operationId="GeneratePlot",
 *        summary="Plot the map to the specified type using the center and scale from the map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
**          @SWG\Parameter(name="papersize", in="query", required=true, type="string", description="The paper size", enum={"A3", "A4", "A5", "Letter", "Legal"}),
 *          @SWG\Parameter(name="orientation", in="query", required=true, type="string", description="The plot orientation L=Landscape, P=Portrait", enum={"L", "P"}),
 *          @SWG\Parameter(name="marginleft", in="query", required=false, type="number", description="left margin in inches"),
 *          @SWG\Parameter(name="marginright", in="query", required=false, type="number", description="right margin in inches"),
 *          @SWG\Parameter(name="margintop", in="query", required=false, type="number", description="top margin in inches"),
 *          @SWG\Parameter(name="marginbottom", in="query", required=false, type="number", description="bottom margin in inches"),
 *          @SWG\Parameter(name="printlayout", in="query", required=false, type="string", description="The PrintLayout resource to use for plotting. Only applies if plotting to DWF"),
 *          @SWG\Parameter(name="title", in="query", required=false, type="string", description="The title to put in the plot"),
 *          @SWG\Parameter(name="pdf_coords", in="query", required=false, type="boolean", description="Show coordinates in plot. PDF only"),
 *          @SWG\Parameter(name="pdf_north_arrow", in="query", required=false, type="boolean", description="Show north arrow in plot. PDF only"),
 *          @SWG\Parameter(name="pdf_scale_bar", in="query", required=false, type="boolean", description="Show scale bar in plot. PDF only"),
 *          @SWG\Parameter(name="pdf_disclaimer", in="query", required=false, type="boolean", description="Show disclaimer in plot. PDF only"),
 *          @SWG\Parameter(name="pdf_legend", in="query", required=false, type="boolean", description="Show legend in plot. PDF only"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The plot type", enum={"dwf", "pdf"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/plot.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMappingServiceController(new AppServices($app));
    $ctrl->GeneratePlot($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/description.{type}",
 *        operationId="DescribeRuntimeMap",
 *        summary="Describe an existing MgMap instance from the specified mapname and session id and returns detailed information about its layer/group structure if requested",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=false, type="string", description="Your MapGuide Session ID. If none specified you must pass the basic http authentication challenge"),
 *          @SWG\Parameter(name="mapName", in="path", required=false, type="string", description="The map name used to identify the MgMap instance"),
 *          @SWG\Parameter(name="requestedfeatures", in="query", required=false, type="integer", description="A bitmask of the information about the Runtime Map that you would like returned. 1=Layer/Group structure, 2=icons, 4=Feature Source Information"),
 *          @SWG\Parameter(name="iconformat", in="query", required=false, type="string", description="The desired icon image format if icons are requested", enum={"PNG","JPG","PNG8","GIF"}),
 *          @SWG\Parameter(name="iconwidth", in="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconheight", in="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconsperscalerange", in="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/description.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMappingServiceController(new AppServices($app));
    $ctrl->DescribeRuntimeMap($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/layersandgroups.{type}",
 *        operationId="UpdateMapLayersAndGroups",
 *        summary="Update the layers and groups of the runtime map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->put("/session/:sessionId/:mapName.Map/layersandgroups.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->UpdateMapLayersAndGroups($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Selection/xml",
 *        operationId="GetSelectionXml",
 *        summary="Gets the selection XML of the given map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Selection/xml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->GetSelectionXml($sessionId, $mapName);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Selection/layers.{type}",
 *        operationId="GetSelectionLayerNames",
 *        summary="Gets the layers of the current selection set",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Selection/layers.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->GetSelectionLayerNames($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Selection/overview.{type}",
 *        operationId="GetSelectionOverview",
 *        summary="Gets an overview of the current selection set with optional extent",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="bounds", in="query", required=false, type="boolean", description="If true, includes the bounds of the whole selection"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Selection/overview.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->GetSelectionOverview($sessionId, $mapName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Selection/features.{type}/{layerName}",
 *        operationId="GetSelectedFeatures",
 *        summary="Gets the features from the given layer in the selection set",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="layerName", in="path", required=true, type="string", description="The name of the layer in the selection set"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml, geojson or html", enum={"xml", "geojson", "html"}),
 *          @SWG\Parameter(name="mappedonly", in="query", required=false, type="boolean", description="Only return properties mapped in the Layer Definition"),
 *          @SWG\Parameter(name="includegeom", in="query", required=false, type="boolean", description="Include the geometry, only applicable when mappedonly=1"),
 *          @SWG\Parameter(name="displayproperties", in="query", required=false, type="boolean", description="Use the display name of mapped properties, only applicable when mappedonly=1 and format is HTML/GeoJSON"),
 *          @SWG\Parameter(name="transformto", in="query", required=false, type="string", description="The CS-Map coordinate system code to transform these features to"),
 *          @SWG\Parameter(name="pagesize", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the number of results for the page."),
 *          @SWG\Parameter(name="page", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the page number of the page. You must specify a valid page size value (> 0) for this parameter to apply."),
 *          @SWG\Parameter(name="orientation", in="query", required=false, type="string", description="The display orientation of feature attribuutes. Only applies if type is html. h=horizontal, v=vertical", enum={"h", "v"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Selection/features.:format/:layerName", function($sessionId, $mapName, $format, $layerName) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->GetSelectedFeatures($sessionId, $mapName, $layerName, $format);
})->name("get_selected_features_$namespace");
/**
 *     @SWG\Post(
 *        path="/session/{session}/{mapName}.Selection/xml",
 *        operationId="UpdateSelectionFromXml",
 *        summary="Updates selection XML of the given map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The new selection XML"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:mapName.Selection/xml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->UpdateSelectionFromXml($sessionId, $mapName);
});
/**
 *     @SWG\Put(
 *        path="/session/{session}/{mapName}.Selection",
 *        operationId="QueryMapFeatures",
 *        summary="Updates the map selection according to some spatial criteria",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="layernames", in="formData", required=false, type="string", description="A comma-separated list of layer names"),
 *          @SWG\Parameter(name="geometry", in="formData", required=false, type="string", description="The WKT of the intersecting geometry"),
 *          @SWG\Parameter(name="maxfeatures", in="formData", required=false, type="integer", description="The maximum number features to select as a result of this operation"),
 *          @SWG\Parameter(name="selectionvariant", in="formData", required=true, type="string", description="The geometry operator to apply", enum={"TOUCHES", "INTERSECTS", "WITHIN", "ENVELOPEINTERSECTS"}),
 *          @SWG\Parameter(name="selectioncolor", in="formData", required=false, type="string", description="The selection color"),
 *          @SWG\Parameter(name="selectionformat", in="formData", required=false, type="string", description="The selection image format", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *          @SWG\Parameter(name="persist", in="formData", required=false, type="boolean", description="If true, will cause this operation to modify the selection set"),
 *          @SWG\Parameter(name="requestdata", in="formData", required=true, type="string", description="A bitmask specifying the information to return in the response. 1=Attributes, 2=Inline Selection, 4=Tooltip, 8=Hyperlink"),
 *          @SWG\Parameter(name="featurefilter", in="formData", required=false, type="string", description="An XML selection string containing the required feature IDs"),
 *          @SWG\Parameter(name="layerattributefilter", in="formData", required=false, type="string", description="Bitmask value determining which layers will be queried. 1=Visible, 2=Selectable, 4=HasTooltips"),
 *          @SWG\Parameter(name="selectionxml", in="formData", required=false, type="boolean", description="Indicates if the 'featurefilter' parameter is to be treated as selection XML. Otherwise the input is treated as a SelectionUpdate XML document"),
 *          @SWG\Parameter(name="append", in="formData", required=false, type="boolean", description="Indicates if the this query selection indicated by the 'featurefilter' parameter should append to the current selection"),
 *          @SWG\Parameter(name="format", in="formData", required=false, type="string", description="The format of the response", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->put("/session/:sessionId/:mapName.Selection", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->QueryMapFeatures($sessionId, $mapName);
});
/**
 *     @SWG\Post(
 *        path="/session/{session}/{mapName}.Map",
 *        operationId="CreateMap",
 *        summary="Creates the runtime map",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The Map Definition XML"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName.Map", function($sessionId, $resName) use($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.Map");
    $ctrl = new MgMapController(new AppServices($app));
    $ctrl->CreateMap($resId);
})->name("session_resource_id_$namespace");
//
// NOTE:
// Although the session repository allows for resources of multiple depth, for the sake of simplicity the REST API only
// allows for interaction with session resources at the root of the repository. This is already the most common
// scenario and the one we will support (for now).
//

//======================== Feature Service APIs ===========================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/status",
 *        operationId="TestConnection",
 *        summary="Tests the connection status of a feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/status", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->TestConnection($resId);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/spatialcontexts.{type}",
 *        operationId="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/spatialcontexts.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->GetSpatialContexts($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/longtransactions.{type}",
 *        operationId="GetLongTransactions",
 *        summary="Gets long transactions of a feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="active", in="query", required=false, type="boolean", description="Return only active long transactions if true, otherwise returns all long transactions"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/longtransactions.:format", function($sessionId, $resName, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->GetLongTransactions($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/schemas",
 *        operationId="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schemas.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->GetSchemaNames($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/schema.{type}/{schemaName}",
 *        operationId="DescribeSchema",
 *        summary="Gets the full description of the specified schema",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The name of the schema to describe"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="classnames", in="query", required=false, type="string", description="The dot-separated list of class names"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schema.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->DescribeSchema($resId, $schemaName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/classes.{type}/{schemaName}",
 *        operationId="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The name of the schema to describe"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classes.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->GetClassNames($resId, $schemaName, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/classdef.{type}/{schemaName}/{className}",
 *        operationId="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classdef.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/classdef.{type}/{qualifiedClassName}",
 *        operationId="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="qualifiedClassName", in="path", required=true, type="string", description="The qualified class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classdef.:format/:qualifiedClassName", function($sessionId, $resName, $format, $qualifiedClassName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
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
/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}.FeatureSource/xml",
 *        operationId="CreateFeatureSource",
 *        summary="Creates the given Feature Source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The XML that describes the Feature Source to create", @SWG\Schema(ref="#/definitions/CreateFeatureSourceEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName.FeatureSource/xml", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->CreateFeatureSource($resId, "xml");
});
/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}.FeatureSource/json",
 *        operationId="CreateFeatureSource",
 *        summary="Creates the given Feature Source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The JSON that describes the Feature Source to create", @SWG\Schema(ref="#/definitions/CreateFeatureSourceEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName.FeatureSource/json", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->CreateFeatureSource($resId, "json");
});
/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="InsertFeatures",
 *        summary="Inserts one or more features into the given feature class for th specified feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The Feature Set XML describing the features to be inserted", @SWG\Schema(ref="#/definitions/InsertFeaturesEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->InsertFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Put(
 *        path="/session/{session}/{resName}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="UpdateFeatures",
 *        summary="Updates one or more features into the given feature class for th specified feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The XML envelope describing the features to be updated", @SWG\Schema(ref="#/definitions/UpdateFeaturesEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->put("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->UpdateFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Delete(
 *        path="/session/{session}/{resName}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="DeleteFeatures",
 *        summary="Deletes one or more features from the given feature class for th specified feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="filter", in="formData", required=true, type="string", description="The FDO filter determining what features to delete"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->DeleteFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="filter", in="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\Parameter(name="properties", in="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\Parameter(name="maxfeatures", in="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\Parameter(name="transformto", in="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\Parameter(name="bbox", in="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "geojson"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.LayerDefinition/features.{type}",
 *        operationId="SelectFeatures",
 *        summary="Queries features from the specified layer definition. Any hyperlink and tooltip expressions will be computed and returned in the response",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="filter", in="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\Parameter(name="properties", in="query", required=false, type="string", description="A comma-separated list of property names"),
 *          @SWG\Parameter(name="maxfeatures", in="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\Parameter(name="transformto", in="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\Parameter(name="mappedonly", in="query", required=false, type="boolean", description="If true, will use only the properties specified in the display mappings in the Layer Definition. Takes precedence over the properties parameter if both specified"),
 *          @SWG\Parameter(name="displayproperties", in="query", required=false, type="boolean", description="If true, will use any display mappings specified in the Layer Definition, only applicable when mappedonly=1 and format is HTML/GeoJSON"),
 *          @SWG\Parameter(name="includegeom", in="query", required=false, type="boolean", description="Include the geometry, only applicable when mappedonly=1"),
 *          @SWG\Parameter(name="bbox", in="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "geojson"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/features.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->SelectLayerFeatures($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/aggregates.{type}/{aggregateType}/{schemaName}/{className}",
 *        operationId="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="aggregateType", in="path", required=true, type="string", description="aggregate type", enum={"count", "bbox", "distinctvalues"}),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/aggregates.:format/:type/:schemaName/:className", function($sessionId, $resName, $format, $type, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController(new AppServices($app));
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, $format);
});
//========================= Resource Service APIs ================================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}/datalist.{type}",
 *        operationId="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName/datalist.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->EnumerateResourceData($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}/data/{dataName}",
 *        operationId="GetResourceData",
 *        summary="Gets the specified resource data item for the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->GetResourceData($resId, $dataName);
});
/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}/data/{dataName}",
 *        operationId="SetResourceData",
 *        summary="Sets the specified resource data item for the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *          @SWG\Parameter(name="type", in="formData", required=true, type="string", description="The type of resource data", enum={"File", "Stream", "String"}),
 *          @SWG\Parameter(name="data", in="formData", required=true, type="file", description="The resource data file to load"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->SetResourceData($resId, $dataName);
});
/**
 *     @SWG\Delete(
 *        path="/session/{session}/{resName}/data/{dataName}",
 *        operationId="DeleteResourceData",
 *        summary="Delete the specified resource data item for the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->DeleteResourceData($resId, $dataName);
});
/*
//Need to confirm if like EnumerateResources, this is not permitted on session repos
$app->get("/session/:sessionId/:resName/header", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->GetResourceHeader($resId, "xml");
});
$app->get("/session/:sessionId/:resName/header.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->GetResourceHeader($resId, $format);
});
*/

/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}/contentorheader.{type}",
 *        operationId="SetResourceContentOrHeader",
 *        summary="Sets the resource content for the given resource. This API exists to provide consistency with the library-based version",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="content", in="formData", required=true, type="file", description="The resource XML content"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName/contentorheader.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->SetResourceContentOrHeader($resId, $format);
});
/**
 *     @SWG\Post(
 *        path="/session/{session}/{resName}/content.{type}",
 *        operationId="SetResourceContent",
 *        summary="Sets the resource content for the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The resource XML content"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/session/:sessionId/:resName/content.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->SetResourceContent($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}/content.{type}",
 *        operationId="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName/content.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->GetResourceContent($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}/references.{type}",
 *        operationId="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName/references.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->EnumerateResourceReferences($resId, $format);
});
/**
 *     @SWG\Delete(
 *        path="/session/{session}/{resName}",
 *        operationId="DeleteResource",
 *        summary="Deletes the given resource",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The resource name (including extension)"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/session/:sessionId/:resName", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController(new AppServices($app));
    $ctrl->DeleteResource($resId);
});
//================================== Tile Service APIs =======================================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}/tile.{type}/{groupName}/{scaleIndex}/{col}/{row}",
 *        operationId="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\Parameter(name="scaleIndex", in="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\Parameter(name="col", in="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\Parameter(name="row", in="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The tile type", enum={"img"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/tile.:format/:groupName/:scaleIndex/:col/:row", function($sessionId, $resName, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgTileServiceController(new AppServices($app));
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});
//============================== Mapping Service APIs =====================================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.LayerDefinition/legend/{scale}/{geomType}/{themecat}/icon.{type}",
 *        operationId="GenerateLegendImage",
 *        summary="Generates the specified icon for the given Layer Definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Layer Definition"),
 *          @SWG\Parameter(name="scale", in="path", required=true, type="number", description="The scale at which the symbolization is requested"),
 *          @SWG\Parameter(name="geomType", in="path", required=true, type="integer", description="The type of symbolization required: 1=Point, 2=Line, 3=Area, 4=Composite"),
 *          @SWG\Parameter(name="themecat", in="path", required=true, type="integer", description="The value indicating which theme category swatch to return. Used when there is a theme defined at this scale"),
 *          @SWG\Parameter(name="width", in="query", required=true, type="integer", description="The requested image width in pixels"),
 *          @SWG\Parameter(name="height", in="query", required=true, type="integer", description="The requested image height in pixels"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The icon image type", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($sessionId, $resName, $scale, $geomtype, $themecat, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgMappingServiceController(new AppServices($app));
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
//============================= Rendering Service APIs ====================================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.MapDefinition/image.{type}",
 *        operationId="RenderMapDefinition",
 *        summary="Renders an image of the specified map definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\Parameter(name="x", in="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\Parameter(name="y", in="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\Parameter(name="scale", in="query", required=true, type="number", description="The map scale to render"),
 *          @SWG\Parameter(name="width", in="query", required=true, type="integer", description="The width of the image"),
 *          @SWG\Parameter(name="height", in="query", required=true, type="integer", description="The height of the image"),
 *          @SWG\Parameter(name="keepselection", in="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\Parameter(name="clip", in="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\Parameter(name="dpi", in="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The image type", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/image.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgRenderingServiceController(new AppServices($app));
    $ctrl->RenderMapDefinition($resId, $format);
});

// ----------------------------- Viewer/Preview Launchers ----------------------------- //

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.WebLayout/viewer",
 *        operationId="LaunchAJAXViewer",
 *        summary="Launch the AJAX Viewer for the specified Web Layout",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.WebLayout/viewer", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.WebLayout");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchAjaxViewer($resId);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.ApplicationDefinition/viewer/{template}",
 *        operationId="LaunchFusionViewer",
 *        summary="Launch the Fusion Viewer for the specified ApplicationDefinition using the given template",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\Parameter(name="template", in="path", required=true, type="string", description="The fusion template to invoke"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.ApplicationDefinition/viewer/:template", function($sessionId, $resName, $template) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.ApplicationDefinition");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchFusionViewer($resId, $template);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.FeatureSource/preview",
 *        operationId="PreviewFeatureSource",
 *        summary="Launches the schema report preview for the given Feature Source",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchResourcePreview($resId);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.LayerDefinition/preview",
 *        operationId="PreviewLayerDefinition",
 *        summary="Launches the AJAX viewer preview for the given Layer Definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchResourcePreview($resId);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.MapDefinition/preview",
 *        operationId="PreviewMapDefinition",
 *        summary="Launches the AJAX viewer preview for the given Map Definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchResourcePreview($resId);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.SymbolDefinition/preview",
 *        operationId="PreviewSymbolDefinition",
 *        summary="Launches the AJAX viewer preview for the given Symbol Definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.SymbolDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.SymbolDefinition");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchResourcePreview($resId);
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.WatermarkDefinition/preview",
 *        operationId="PreviewWatermarkDefinition",
 *        summary="Launches the AJAX viewer preview for the given Watermark Definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The name of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.WatermarkDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.WatermarkDefinition");
    $ctrl = new MgViewerController(new AppServices($app));
    $ctrl->LaunchResourcePreview($resId);
});

// =========================================== KML Service APIs ====================================================

/**
 *     @SWG\Get(
 *        path="/session/{session}/{mapName}.Map/kml",
 *        operationId="GetMapKml",
 *        summary="Gets the KML for the specified map definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="mapName", in="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\Parameter(name="native", in="query", required=false, type="boolean", description="If true, this operation will simply pass through to the mapagent. This is much faster, but note that all network link URLs will be referring to the mapagent instead of downstream RESTful layer KML URLs."),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:mapName.Map/kml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgKmlServiceController(new AppServices($app));
    $ctrl->GetSessionMapKml($sessionId, $mapName, "kml");
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.MapDefinition/kml",
 *        operationId="GetMapKml",
 *        summary="Gets the KML for the specified map definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="native", in="query", required=false, type="boolean", description="If true, this operation will simply pass through to the mapagent. This is much faster, but note that all network link URLs will be referring to the mapagent instead of downstream RESTful layer KML URLs."),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/kml", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgKmlServiceController(new AppServices($app));
    $ctrl->GetMapKml($resId, "kml");
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.LayerDefinition/kml",
 *        operationId="GetMapKml",
 *        summary="Gets the KML for the specified layer definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The feature source name"),
 *          @SWG\Parameter(name="bbox", in="query", required=true, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry. Coordinates must be LL84 coordinates"),
 *          @SWG\Parameter(name="dpi", in="query", required=true, type="integer", description="Display DPI. Default is 96"),
 *          @SWG\Parameter(name="width", in="query", required=true, type="integer", description="The display width of the KML viewport"),
 *          @SWG\Parameter(name="height", in="query", required=true, type="integer", description="The display height of the KML viewport"),
 *          @SWG\Parameter(name="draworder", in="query", required=true, type="integer", description="The draw order of this layer"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/kml", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgKmlServiceController(new AppServices($app));
    $ctrl->GetLayerKml($resId, "kml");
});

/**
 *     @SWG\Get(
 *        path="/session/{session}/{resName}.LayerDefinition/kmlfeatures.{type}",
 *        operationId="GetFeaturesKml",
 *        summary="Gets the features KML for the specified layer definition",
 *        tags={"session"},
 *          @SWG\Parameter(name="session", in="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resName", in="path", required=true, type="string", description="The layer definition name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="kml or kmz", enum={"kml", "kmz"}),
 *          @SWG\Parameter(name="bbox", in="query", required=true, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry. Coordinates must be LL84 coordinates"),
 *          @SWG\Parameter(name="dpi", in="query", required=true, type="integer", description="Display DPI. Default is 96"),
 *          @SWG\Parameter(name="width", in="query", required=true, type="integer", description="The display width of the KML viewport"),
 *          @SWG\Parameter(name="height", in="query", required=true, type="integer", description="The display height of the KML viewport"),
 *          @SWG\Parameter(name="draworder", in="query", required=true, type="integer", description="The draw order of this layer"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/kmlfeatures.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgKmlServiceController(new AppServices($app));
    $ctrl->GetFeaturesKml($resId, $format);
});