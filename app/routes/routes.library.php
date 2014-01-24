<?php

require_once dirname(__FILE__)."/../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../controller/tileservicecontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

// Resource Service APIs
$app->get("/library/:resourcePath+/datalist", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, "xml");
});
$app->get("/library/:resourcePath+/datalist.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
$app->get("/library/resourcelist", function() use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
$app->get("/library/resourcelist.:format", function($format) use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
$app->get("/library/:resourcePath+/resourcelist", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "resourcelist");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
$app->get("/library/:resourcePath+/resourcelist.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "resourcelist.".$format);
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

?>