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
require_once dirname(__FILE__)."/../../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/mappingservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/restservicecontroller.php";
require_once dirname(__FILE__)."/../../core/app.php";

/**
 *     @SWG\Get(
 *        path="/services/fusiontemplates.{type}",
 *        operationId="EnumerateApplicationTemplates",
 *        summary="Enumerates available templates for a fusion application",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/services/fusiontemplates.:format", function($format) {
    $ctrl = new MgRestServiceController($this->get("AppServices"));
    $ctrl->EnumerateApplicationTemplates($format);
});
/**
 *     @SWG\Get(
 *        path="/services/fusionwidgets.{type}",
 *        operationId="EnumerateApplicationWidgets",
 *        summary="Enumerates available widgets for a fusion application",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/services/fusionwidgets.:format", function($format) {
    $ctrl = new MgRestServiceController($this->get("AppServices"));
    $ctrl->EnumerateApplicationWidgets($format);
});
/**
 *     @SWG\Get(
 *        path="/services/fusioncontainers.{type}",
 *        operationId="EnumerateApplicationContainers",
 *        summary="Enumerates available containers for a fusion application",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/services/fusioncontainers.:format", function($format) {
    $ctrl = new MgRestServiceController($this->get("AppServices"));
    $ctrl->EnumerateApplicationContainers($format);
});
/**
 *     @SWG\Post(
 *        path="/services/listunmanageddata.{responseType}",
 *        operationId="EnumerateUnmanagedData",
 *        summary="Enumerates files in an unmanaged aliased directory",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="path", in="formData", required=false, type="string", description=""),
 *          @SWG\Parameter(name="type", in="formData", required=true, type="string", description="", enum={"Folders","Files","Both"}),
 *          @SWG\Parameter(name="filter", in="formData", required=false, type="string", description=""),
 *          @SWG\Parameter(name="recursive", in="formData", required=true, type="boolean", description=""),
 *          @SWG\Parameter(name="responseType", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/services/listunmanageddata.:format", function($format) {
    $ctrl = new MgResourceServiceController($this->get("AppServices"));
    $ctrl->EnumerateUnmanagedData($format);
});
/**
 *     @SWG\Get(
 *        path="/services/getschemamapping.{type}",
 *        operationId="GetSchemaMapping",
 *        summary="Gets schema mapping of a feature source",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="provider", in="query", required=true, type="string", description="The FDO Provider"),
 *          @SWG\Parameter(name="connection", in="query", required=true, type="string", description="The partial connection string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/services/getschemamapping.:format", function($format) {
    $ctrl = new MgFeatureServiceController($this->get("AppServices"));
    $ctrl->GetSchemaMapping($format);
});

/**
 *     @SWG\Post(
 *        path="/services/copyresource",
 *        operationId="CopyResource",
 *        summary="Copies a resource from one resource ID to another",
 *        tags={"services"},
 *          @SWG\Parameter(name="source", in="formData", required=true, type="string", description="The Source Resource ID"),
 *          @SWG\Parameter(name="destination", in="formData", required=true, type="string", description="The Target Resource ID"),
 *          @SWG\Parameter(name="overwrite", in="formData", required=false, type="boolean", description="Indicates whether to overwrite the target resource if it exists"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/services/copyresource", function() {
    $ctrl = new MgResourceServiceController($this->get("AppServices"));
    $ctrl->CopyResource();
});
/**
 *     @SWG\Post(
 *        path="/services/moveresource",
 *        operationId="MoveResource",
 *        summary="Moves a resource from one resource ID to another",
 *        tags={"services"},
 *          @SWG\Parameter(name="source", in="formData", required=true, type="string", description="The Source Resource ID"),
 *          @SWG\Parameter(name="destination", in="formData", required=true, type="string", description="The Target Resource ID"),
 *          @SWG\Parameter(name="overwrite", in="formData", required=false, type="boolean", description="Indicates whether to overwrite the target resource if it exists"),
 *          @SWG\Parameter(name="cascade", in="formData", required=false, type="boolean", description="Indicates whether to cascade any reference changes in related documents as a result of this change"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/services/moveresource", function() {
    $ctrl = new MgResourceServiceController($this->get("AppServices"));
    $ctrl->MoveResource();
});
/**
 *     @SWG\Post(
 *        path="/services/transformcoords",
 *        operationId="TransformCoordinates",
 *        summary="Transforms the given coordinates from the specified source coordinate system to the target coordinate system",
 *        tags={"services"},
 *          @SWG\Parameter(name="from", in="formData", required=true, type="string", description="The Source Coordinate System Code"),
 *          @SWG\Parameter(name="to", in="formData", required=true, type="string", description="The Target Coordinate System Code"),
 *          @SWG\Parameter(name="coords", in="formData", required=true, type="string", description="A comma-delimited list of space-delimited coordinate pairs"),
 *          @SWG\Parameter(name="format", in="formData", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/services/transformcoords", function() {
    $ctrl = new MgCoordinateSystemController($this->get("AppServices"));
    $ctrl->TransformCoordinates();
});
/**
 *     @SWG\Post(
 *        path="/services/createmap.{type}",
 *        operationId="CreateRuntimeMap",
 *        summary="Creates a new Runtime Map (MgMap) instance from the specified map definition and returns detailed information about its layer/group structure if requested",
 *        tags={"services"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID. If none specified you must pass the basic http authentication challenge"),
 *          @SWG\Parameter(name="username", in="formData", required=false, type="string", description="The MapGuide username"),
 *          @SWG\Parameter(name="password", in="formData", required=false, type="string", description="The password"),
 *          @SWG\Parameter(name="mapdefinition", in="formData", required=true, type="string", description="The Map Definition ID to create a new runtime map from"),
 *          @SWG\Parameter(name="targetmapname", in="formData", required=false, type="string", description="The target map name to associate the Runtime Map by. By default, the name is generated from the Map Definition ID"),
 *          @SWG\Parameter(name="requestedfeatures", in="formData", required=false, type="integer", description="A bitmask of the information about the Runtime Map that you would like returned. 1=Layer/Group structure, 2=icons, 4=Feature Source Information"),
 *          @SWG\Parameter(name="iconformat", in="formData", required=false, type="string", description="The desired icon image format if icons are requested", enum={"PNG","JPG","PNG8","GIF"}),
 *          @SWG\Parameter(name="iconwidth", in="formData", required=false, type="integer", description="The desired width of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconheight", in="formData", required=false, type="integer", description="The desired height of generated icons if icons are requested"),
 *          @SWG\Parameter(name="iconsperscalerange", in="formData", required=false, type="integer", description="The number of icons to generate per scale range if icons are requested"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/services/createmap.:format", function($format) {
    $ctrl = new MgMappingServiceController($this->get("AppServices"));
    $ctrl->CreateRuntimeMap($format);
});