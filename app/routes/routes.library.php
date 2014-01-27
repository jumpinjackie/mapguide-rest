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

require_once dirname(__FILE__)."/../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../controller/tileservicecontroller.php";
require_once dirname(__FILE__)."/../controller/mappingservicecontroller.php";
require_once dirname(__FILE__)."/../controller/renderingservicecontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

// Resource Service APIs
$app->get("/library/:resourcePath+/data", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, "xml");
});
$app->get("/library/:resourcePath+/data.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
$app->get("/library/list", function() use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
$app->get("/library/list.:format", function($format) use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
$app->get("/library/:resourcePath+/list", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "list");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
$app->get("/library/:resourcePath+/list.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "list.".$format);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
$app->get("/library/:resourcePath+/data/:dataName", function($resourcePath, $dataName) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceData($resId, $dataName);
});
$app->get("/library/:resourcePath+/header", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, "xml");
});
$app->get("/library/:resourcePath+/header.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, $format);
});
$app->get("/library/:resourcePath+/content", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, "xml");
});
$app->get("/library/:resourcePath+/content.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, $format);
});
$app->get("/library/:resourcePath+/references", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, "xml");
});
$app->get("/library/:resourcePath+/references.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, $format);
});
$app->delete("/library/:resourcePath+", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResource($resId); 
});
// Feature Service APIs
$app->get("/library/:resourcePath+.FeatureSource/spatialcontexts", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, "xml");
});
$app->get("/library/:resourcePath+.FeatureSource/spatialcontexts.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, $format);
});
$app->get("/library/:resourcePath+.FeatureSource/schemas", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, "xml");
});
$app->get("/library/:resourcePath+.FeatureSource/schemas.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, $format);
});
$app->get("/library/:resourcePath+.FeatureSource/schema/:schemaName", function($resourcePath, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, "xml");
});
$app->get("/library/:resourcePath+.FeatureSource/schema.:format/:schemaName", function($resourcePath, $format, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, $format);
});
$app->get("/library/:resourcePath+.FeatureSource/classes/:schemaName", function($resourcePath, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, "xml");
});
$app->get("/library/:resourcePath+.FeatureSource/classes.:format/:schemaName", function($resourcePath, $format, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, $format);
});
$app->get("/library/:resourcePath+.FeatureSource/classdef/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, "xml");
});
$app->get("/library/:resourcePath+.FeatureSource/classdef.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
$app->get("/library/:resourcePath+.FeatureSource/classdef/:qualifiedClassName", function($resourcePath, $qualifiedClassName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
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
$app->get("/library/:resourcePath+.FeatureSource/classdef.:format/:qualifiedClassName", function($resourcePath, $format, $qualifiedClassName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
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
$app->get("/library/:resourcePath+/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, "xml");
});
$app->get("/library/:resourcePath+/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
// Tile Service APIs
$app->get("/library/:resourcePath+/tile/:groupName/:scaleIndex/:col/:row", function($resourcePath, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row);
});
// Mapping Service APIs
$app->get("/library/:resourcePath+.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($resourcePath, $scale, $geomtype, $themecat, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
// Rendering Service APIs
$app->get("/library/:resourcePath+.MapDefinition/image.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderMapDefinition($resId, $format);
});

?>