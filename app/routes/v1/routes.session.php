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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to render"),
 *          @SWG\parameter(name="width", paramType="query", required=true, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=true, type="integer", description="The height of the image"),
 *          @SWG\parameter(name="keepselection", paramType="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\parameter(name="clip", paramType="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\parameter(name="dpi", paramType="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The image type", enum="['PNG','PNG8','JPG','GIF']"),
 *          @SWG\parameter(name="showlayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="showgroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids"),
 *          @SWG\parameter(name="hidelayers", paramType="query", required=false, type="string", description="A comma-separated list of layer object ids"),
 *          @SWG\parameter(name="hidegroups", paramType="query", required=false, type="string", description="A comma-separated list of layer group ids")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *     path="/session/{session}/{mapName}.Map/legendimage.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetMapLegendImage",
 *        summary="Renders a legend image of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="width", paramType="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=false, type="integer", description="The height of the image"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The image type", enum="['PNG','PNG8','JPG','GIF']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/legendimage.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderRuntimeMapLegend($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/layers",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateMapLayers",
 *        summary="Gets the layers of the specified runtime map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="requestedfeatures", paramType="query", required=false, type="integer", description="A bitmask of the additional information that you would like returned. 2=icons, 4=Feature Source Information"),
 *          @SWG\parameter(name="iconformat", paramType="query", required=false, type="string", description="The desired icon image format if icons are requested", enum="['PNG','JPG','PNG8','GIF']"),
 *          @SWG\parameter(name="iconwidth", paramType="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconheight", paramType="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconsperscalerange", paramType="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\parameter(name="group", paramType="query", required=false, type="string", description="Only return layers belonging to the specified group")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']"),
 *          @SWG\parameter(name="requestedfeatures", paramType="query", required=false, type="integer", description="A bitmask of the additional information that you would like returned. 2=icons, 4=Feature Source Information"),
 *          @SWG\parameter(name="iconformat", paramType="query", required=false, type="string", description="The desired icon image format if icons are requested", enum="['PNG','JPG','PNG8','GIF']"),
 *          @SWG\parameter(name="iconwidth", paramType="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconheight", paramType="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconsperscalerange", paramType="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\parameter(name="group", paramType="query", required=false, type="string", description="Only return layers belonging to the specified group")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="papersize", paramType="query", required=true, type="string", description="The paper size", enum="['A3','A4','A5','Letter','Legal']"),
 *          @SWG\parameter(name="orientation", paramType="query", required=true, type="string", description="The plot orientation L=Landscape, P=Portrait", enum="['L','P']"),
 *          @SWG\parameter(name="marginleft", paramType="query", required=false, type="double", description="left margin in inches"),
 *          @SWG\parameter(name="marginright", paramType="query", required=false, type="double", description="right margin in inches"),
 *          @SWG\parameter(name="margintop", paramType="query", required=false, type="double", description="top margin in inches"),
 *          @SWG\parameter(name="marginbottom", paramType="query", required=false, type="double", description="bottom margin in inches"),
 *          @SWG\parameter(name="printlayout", paramType="query", required=false, type="string", description="The PrintLayout resource to use for plotting. Only applies if plotting to DWF"),
 *          @SWG\parameter(name="title", paramType="query", required=false, type="string", description="The title to put in the plot")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        summary="Plot the map to the specified type using the center and scale from the map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
**          @SWG\parameter(name="papersize", paramType="query", required=true, type="string", description="The paper size", enum="['A3','A4','A5','Letter','Legal']"),
 *          @SWG\parameter(name="orientation", paramType="query", required=true, type="string", description="The plot orientation L=Landscape, P=Portrait", enum="['L','P']"),
 *          @SWG\parameter(name="marginleft", paramType="query", required=false, type="double", description="left margin in inches"),
 *          @SWG\parameter(name="marginright", paramType="query", required=false, type="double", description="right margin in inches"),
 *          @SWG\parameter(name="margintop", paramType="query", required=false, type="double", description="top margin in inches"),
 *          @SWG\parameter(name="marginbottom", paramType="query", required=false, type="double", description="bottom margin in inches"),
 *          @SWG\parameter(name="printlayout", paramType="query", required=false, type="string", description="The PrintLayout resource to use for plotting. Only applies if plotting to DWF"),
 *          @SWG\parameter(name="title", paramType="query", required=false, type="string", description="The title to put in the plot"),
 *          @SWG\parameter(name="layeredpdf", paramType="query", required=false, type="boolean", description="Indicates whether to produce a layered PDF. Only applies if plotting PDFs. This is slower than regular PDF plot, but produces a PDF with the same layer structure as the map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The plot type", enum="['dwf','pdf']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *     path="/session/{session}/{mapName}.Map/description",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeRuntimeMap",
 *        summary="Describe an existing MgMap instance from the specified mapname and session id and returns detailed information about its layer/group structure if requested",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID. If none specified you must pass the basic http authentication challenge"),
 *          @SWG\parameter(name="mapName", paramType="path", required=false, type="string", description="The map name used to identify the MgMap instance"),
 *          @SWG\parameter(name="requestedfeatures", paramType="query", required=false, type="integer", description="A bitmask of the information about the Runtime Map that you would like returned. 1=Layer/Group structure, 2=icons, 4=Feature Source Information"),
 *          @SWG\parameter(name="iconformat", paramType="query", required=false, type="string", description="The desired icon image format if icons are requested", enum="['PNG','JPG','PNG8','GIF']"),
 *          @SWG\parameter(name="iconwidth", paramType="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconheight", paramType="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconsperscalerange", paramType="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/description", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->DescribeRuntimeMap($sessionId, $mapName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/description.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeRuntimeMap",
 *        summary="Describe an existing MgMap instance from the specified mapname and session id and returns detailed information about its layer/group structure if requested",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=false, type="string", description="Your MapGuide Session ID. If none specified you must pass the basic http authentication challenge"),
 *          @SWG\parameter(name="mapName", paramType="path", required=false, type="string", description="The map name used to identify the MgMap instance"),
 *          @SWG\parameter(name="requestedfeatures", paramType="query", required=false, type="integer", description="A bitmask of the information about the Runtime Map that you would like returned. 1=Layer/Group structure, 2=icons, 4=Feature Source Information"),
 *          @SWG\parameter(name="iconformat", paramType="query", required=false, type="string", description="The desired icon image format if icons are requested", enum="['PNG','JPG','PNG8','GIF']"),
 *          @SWG\parameter(name="iconwidth", paramType="query", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconheight", paramType="query", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\parameter(name="iconsperscalerange", paramType="query", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/description.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->DescribeRuntimeMap($sessionId, $mapName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Selection/xml",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSelectionXml",
 *        summary="Gets the selection XML of the given map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="layerName", paramType="path", required=true, type="string", description="The name of the layer in the selection set"),
 *          @SWG\parameter(name="mappedonly", paramType="path", required=false, type="boolean", description="Only return properties mapped in the Layer Definition"),
 *          @SWG\parameter(name="transformto", paramType="path", required=false, type="string", description="The CS-Map coordinate system code to transform these features to")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="layerName", paramType="path", required=true, type="string", description="The name of the layer in the selection set"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml, geojson or html", enum="['xml','geojson','html']"),
 *          @SWG\parameter(name="mappedonly", paramType="path", required=false, type="boolean", description="Only return properties mapped in the Layer Definition"),
 *          @SWG\parameter(name="transformto", paramType="path", required=false, type="string", description="The CS-Map coordinate system code to transform these features to"),
 *          @SWG\parameter(name="pagesize", paramType="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the number of results for the page."),
 *          @SWG\parameter(name="page", paramType="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the page number of the page. You must specify a valid page size value (> 0) for this parameter to apply."),
 *          @SWG\parameter(name="orientation", paramType="query", required=false, type="string", description="The display orientation of feature attribuutes. Only applies if type is html. h=horizontal, v=vertical", enum="['h','v']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The new selection XML")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the session resource (including extension)"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The new selection XML")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/status",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="TestConnection",
 *        summary="Tests the connection status of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/status", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->TestConnection($resId);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/spatialcontexts",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/spatialcontexts", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/spatialcontexts.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/spatialcontexts.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/longtransactions",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetLongTransactions",
 *        summary="Gets long transactions of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="active", paramType="query", required=false, type="boolean", description="Return only active long transactions if true, otherwise returns all long transactions")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/longtransactions", function($sessionId, $resName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetLongTransactions($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/longtransactions.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetLongTransactions",
 *        summary="Gets long transactions of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']"),
 *          @SWG\parameter(name="active", paramType="query", required=false, type="boolean", description="Return only active long transactions if true, otherwise returns all long transactions")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/longtransactions.:format", function($sessionId, $resName, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetLongTransactions($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/schemas",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schemas", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/schemas",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schemas.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/schema/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeSchema",
 *        summary="Gets the full description of the specified schema",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The name of the schema to describe"),
 *          @SWG\parameter(name="classnames", paramType="query", required=false, type="string", description="The dot-separated list of class names")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schema/:schemaName", function($sessionId, $resName, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/schema.{type}/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeSchema",
 *        summary="Gets the full description of the specified schema",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The name of the schema to describe"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']"),
 *          @SWG\parameter(name="classnames", paramType="query", required=false, type="string", description="The dot-separated list of class names")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/schema.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classes/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The name of the schema to describe")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classes/:schemaName", function($sessionId, $resName, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classes.{type}/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The name of the schema to describe"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classes.:format/:schemaName", function($sessionId, $resName, $format, $schemaName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classdef/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef.{type}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/classdef.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef/{qualifiedClassName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="qualifiedClassName", paramType="path", required=true, type="string", description="The qualified class name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
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
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef.{type}/{qualifiedClassName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="qualifiedClassName", paramType="path", required=true, type="string", description="The qualified class name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
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
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="InsertFeatures",
 *        summary="Inserts one or more features into the given feature class for th specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The Feature Set XML describing the features to be inserted")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->InsertFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="PUT",
 *        nickname="UpdateFeatures",
 *        summary="Updates one or more features into the given feature class for th specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The XML envelope describing the features to be update")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->put("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->UpdateFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/classdef/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteFeatures",
 *        summary="Deletes one or more features from the given feature class for th specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="filter", paramType="form", required=true, type="string", description="The FDO filter determining what features to delete")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DeleteFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="filter", paramType="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\parameter(name="properties", paramType="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\parameter(name="maxfeatures", paramType="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\parameter(name="transformto", paramType="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\parameter(name="bbox", paramType="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/features/:schemaName/:className", function($sessionId, $resName, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/features.{type}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="filter", paramType="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\parameter(name="properties", paramType="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\parameter(name="maxfeatures", paramType="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\parameter(name="transformto", paramType="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\parameter(name="bbox", paramType="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','geojson']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/features.:format/:schemaName/:className", function($sessionId, $resName, $format, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/features",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified layer definition. Any hyperlink and tooltip expressions will be computed and returned in the response",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="filter", paramType="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\parameter(name="properties", paramType="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\parameter(name="maxfeatures", paramType="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\parameter(name="transformto", paramType="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\parameter(name="bbox", paramType="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/features", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectLayerFeatures($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/features.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified layer definition. Any hyperlink and tooltip expressions will be computed and returned in the response",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="filter", paramType="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\parameter(name="properties", paramType="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\parameter(name="maxfeatures", paramType="query", required=false, type="string", description="The maximum number of features to restrict this response to"),
 *          @SWG\parameter(name="transformto", paramType="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\parameter(name="bbox", paramType="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','geojson']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/features.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectLayerFeatures($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/aggregates/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/aggregates/:type/:schemaName/:className", function($sessionId, $resName, $type, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/aggregates.{type}/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/aggregates.:format/:type/:schemaName/:className", function($sessionId, $resName, $format, $type, $schemaName, $className) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, $format);
});
//========================= Resource Service APIs ================================

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/datalist",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The resource name (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/datalist", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/datalist.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/datalist.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/data/{dataName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceData",
 *        summary="Gets the specified resource data item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="dataName", paramType="path", required=true, type="string", description="The name of the resource data to retrieve")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceData($resId, $dataName);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/data/{dataName}",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetResourceData",
 *        summary="Sets the specified resource data item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="dataName", paramType="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *          @SWG\parameter(name="type", paramType="form", required=true, type="string", description="The type of resource data", enum="['File','Stream','String']"),
 *          @SWG\parameter(name="data", paramType="form", required=true, type="file", description="The resource data file to load")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceData($resId, $dataName);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/data/{dataName}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteResourceData",
 *        summary="Delete the specified resource data item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="dataName", paramType="path", required=true, type="string", description="The name of the resource data to retrieve")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/session/:sessionId/:resName/data/:dataName", function($sessionId, $resName, $dataName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResourceData($resId, $dataName);
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

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/content",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetResourceContent",
 *        summary="Sets the resource content for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The resource XML content")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/session/:sessionId/:resName/content", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContent($resId);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/content",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/content", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/content.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/content.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/references",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/references", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/references.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName/references.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteResource",
 *        summary="Deletes the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The resource name (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/session/:sessionId/:resName", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResource($resId); 
});
//================================== Tile Service APIs =======================================

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/tile/{groupName}/{scaleIndex}/{col}/{row}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\parameter(name="scaleIndex", paramType="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\parameter(name="col", paramType="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\parameter(name="row", paramType="path", required=true, type="integer", description="The row of the tile to fetch")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/tile/:groupName/:scaleIndex/:col/:row", function($sessionId, $resName, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, "img");
});
/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}/tile.{type}/{groupName}/{scaleIndex}/{col}/{row}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\parameter(name="scaleIndex", paramType="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\parameter(name="col", paramType="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\parameter(name="row", paramType="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The tile type", enum="['img']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/tile.:format/:groupName/:scaleIndex/:col/:row", function($sessionId, $resName, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});
//============================== Mapping Service APIs =====================================

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/legend/{scale}/{geomType}/{themecat}/icon.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GenerateLegendImage",
 *        summary="Generates the specified icon for the given Layer Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Layer Definition"),
 *          @SWG\parameter(name="scale", paramType="path", required=true, type="double", description="The scale at which the symbolization is requested"),
 *          @SWG\parameter(name="geomType", paramType="path", required=true, type="integer", description="The type of symbolization required: 1=Point, 2=Line, 3=Area, 4=Composite"),
 *          @SWG\parameter(name="themecat", paramType="path", required=true, type="integer", description="The value indicating which theme category swatch to return. Used when there is a theme defined at this scale"),
 *          @SWG\parameter(name="width", paramType="query", required=true, type="integer", description="The requested image width in pixels"),
 *          @SWG\parameter(name="height", paramType="query", required=true, type="integer", description="The requested image height in pixels"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The icon image type", enum="['PNG','PNG8','JPG','GIF']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($sessionId, $resName, $scale, $geomtype, $themecat, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
//============================= Rendering Service APIs ====================================

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.MapDefinition/image.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="RenderMapDefinition",
 *        summary="Renders an image of the specified map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to render"),
 *          @SWG\parameter(name="width", paramType="query", required=true, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=true, type="integer", description="The height of the image"),
 *          @SWG\parameter(name="keepselection", paramType="query", required=false, type="boolean", description="Indicates whether any selection should be retained"),
 *          @SWG\parameter(name="clip", paramType="query", required=false, type="boolean", description="Apply clipping"),
 *          @SWG\parameter(name="dpi", paramType="query", required=false, type="integer", description="The display DPI. If not specified, defaults to 96"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The image type", enum="['PNG','PNG8','JPG','GIF']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/image.:format", function($sessionId, $resName, $format) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderMapDefinition($resId, $format);
});

// ----------------------------- Viewer/Preview Launchers ----------------------------- //

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.WebLayout/viewer",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="LaunchAJAXViewer",
 *        summary="Launch the AJAX Viewer for the specified Web Layout",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.WebLayout/viewer", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.WebLayout");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchAjaxViewer($resId);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.ApplicationDefinition/viewer/{template}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="LaunchFusionViewer",
 *        summary="Launch the Fusion Viewer for the specified ApplicationDefinition using the given template",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition"),
 *          @SWG\parameter(name="template", paramType="path", required=true, type="string", description="The fusion template to invoke")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.ApplicationDefinition/viewer/:template", function($sessionId, $resName, $template) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.ApplicationDefinition");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchFusionViewer($resId, $template);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.FeatureSource/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewFeatureSource",
 *        summary="Launches the schema report preview for the given Feature Source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.FeatureSource/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.FeatureSource");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewLayerDefinition",
 *        summary="Launches the AJAX viewer preview for the given Layer Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.MapDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewMapDefinition",
 *        summary="Launches the AJAX viewer preview for the given Map Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.SymbolDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewSymbolDefinition",
 *        summary="Launches the AJAX viewer preview for the given Symbol Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.SymbolDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.SymbolDefinition");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.WatermarkDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewWatermarkDefinition",
 *        summary="Launches the AJAX viewer preview for the given Watermark Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The name of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.WatermarkDefinition/preview", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.WatermarkDefinition");
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

// =========================================== KML Service APIs ====================================================

/**
 * @SWG\Api(
 *     path="/session/{session}/{mapName}.Map/kml",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetMapKml",
 *        summary="Gets the KML for the specified map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="mapName", paramType="path", required=true, type="string", description="The name of the runtime map"),
 *          @SWG\parameter(name="native", paramType="query", required=false, type="boolean", description="If true, this operation will simply pass through to the mapagent. This is much faster, but note that all network link URLs will be referring to the mapagent instead of downstream RESTful layer KML URLs.")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:mapName.Map/kml", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetSessionMapKml($sessionId, $mapName, "kml");
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.MapDefinition/kml",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetMapKml",
 *        summary="Gets the KML for the specified map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="native", paramType="query", required=false, type="boolean", description="If true, this operation will simply pass through to the mapagent. This is much faster, but note that all network link URLs will be referring to the mapagent instead of downstream RESTful layer KML URLs.")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.MapDefinition/kml", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.MapDefinition");
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetMapKml($resId, "kml");
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/kml",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetMapKml",
 *        summary="Gets the KML for the specified layer definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="bbox", paramType="query", required=true, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry. Coordinates must be LL84 coordinates"),
 *          @SWG\parameter(name="dpi", paramType="query", required=true, type="integer", description="Display DPI. Default is 96"),
 *          @SWG\parameter(name="width", paramType="query", required=true, type="integer", description="The display width of the KML viewport"),
 *          @SWG\parameter(name="height", paramType="query", required=true, type="integer", description="The display height of the KML viewport"),
 *          @SWG\parameter(name="draworder", paramType="query", required=true, type="integer", description="The draw order of this layer")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/kml", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetLayerKml($resId, "kml");
});

/**
 * @SWG\Api(
 *     path="/session/{session}/{resName}.LayerDefinition/kmlfeatures",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetFeaturesKml",
 *        summary="Gets the features KML for the specified layer definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="path", required=true, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resName", paramType="path", required=true, type="string", description="The feature source name"),
 *          @SWG\parameter(name="bbox", paramType="query", required=true, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry. Coordinates must be LL84 coordinates"),
 *          @SWG\parameter(name="dpi", paramType="query", required=true, type="integer", description="Display DPI. Default is 96"),
 *          @SWG\parameter(name="width", paramType="query", required=true, type="integer", description="The display width of the KML viewport"),
 *          @SWG\parameter(name="height", paramType="query", required=true, type="integer", description="The display height of the KML viewport"),
 *          @SWG\parameter(name="draworder", paramType="query", required=true, type="integer", description="The draw order of this layer")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/session/:sessionId/:resName.LayerDefinition/kmlfeatures", function($sessionId, $resName) use ($app) {
    $resId = new MgResourceIdentifier("Session:$sessionId//$resName.LayerDefinition");
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetFeaturesKml($resId, "kml");
});

?>