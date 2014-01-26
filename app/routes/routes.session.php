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
require_once dirname(__FILE__)."/../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../controller/mapcontroller.php";
require_once dirname(__FILE__)."/../controller/renderingservicecontroller.php";

$app->post("/session", function() use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->CreateSession();
});
$app->get("/session/:sessionId/:mapName.Map/image.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderRuntimeMap($sessionId, $mapName, $format);
});
$app->get("/session/:sessionId/:mapName.Map/overlayimage.:format", function($sessionId, $mapName, $format) use ($app) {
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderDynamicOverlayImage($sessionId, $mapName, $format);
});
$app->get("/session/:sessionId/:mapName.Map/layers", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayers($sessionId, $mapName);
});
$app->get("/session/:sessionId/:mapName.Map/layergroups", function($sessionId, $mapName) use ($app) {
    $ctrl = new MgMapController($app);
    $ctrl->EnumerateMapLayerGroups($sessionId, $mapName);
});
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

?>