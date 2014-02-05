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

$app->post("/session", function() use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->CreateSession();
});
$app->delete("/session/:sessionId", function($sessionId) use ($app) {
    $ctrl = new MgRestServiceController($app);
    $ctrl->DestroySession($sessionId);
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

/**
 * NOTE:
 * Although the session repository allows for resources of multiple depth, for the sake of simplicity the REST API only
 * allows for interaction with session resources at the root of the repository. This is already the most common
 * scenario and the one we will support (for now).
 */

// Feature Service APIs
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