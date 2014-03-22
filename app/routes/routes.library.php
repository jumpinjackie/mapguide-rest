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

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="Site Repository",
 *      resourcePath="/library"
 * )
 */

//======================== Feature Service APIs =======================================

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/spatialcontexts",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/spatialcontexts", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/spatialcontexts.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/spatialcontexts.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSpatialContexts($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/schemas",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/schemas", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/schemas.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/schemas.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetSchemaNames($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/schema/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeSchema",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/schema/:schemaName", function($resourcePath, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/schema.{type}/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="DescribeSchema",
 *        summary="Gets the schema names of a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/schema.:format/:schemaName", function($resourcePath, $format, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DescribeSchema($resId, $schemaName, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classes/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/classes/:schemaName", function($resourcePath, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classes.{type}/{schemaName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/classes.:format/:schemaName", function($resourcePath, $format, $schemaName) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassNames($resId, $schemaName, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classdef/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/classdef/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classdef.{type}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/classdef.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetClassDefinition($resId, $schemaName, $className, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classdef/{qualifiedClassName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="qualifiedClassName", paramType="path", required=true, type="string", description="The qualified class name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
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
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/classdef.{type}/{qualifiedClassName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="qualifiedClassName", paramType="path", required=true, type="string", description="The qualified class name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
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
$app->post("/library/:resourcePath+/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->InsertFeatures($resId, $schemaName, $className);
});
$app->put("/library/:resourcePath+/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->UpdateFeatures($resId, $schemaName, $className);
});
$app->delete("/library/:resourcePath+/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DeleteFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/features.{type}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','geojson']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/aggregates/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/aggregates/:type/:schemaName/:className", function($resourcePath, $type, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/aggregates.{type}/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/aggregates.:format/:type/:schemaName/:className", function($resourcePath, $format, $type, $schemaName, $className) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, $format);
});

//================================== Resource Service APIs ======================================================


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
$app->post("/library/:resourcePath+/header", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceHeader($resId);
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
$app->post("/library/:resourcePath+/content", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContent($resId);
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
//============================== Tile Service APIs ============================================
$app->get("/library/:resourcePath+/tile/:groupName/:scaleIndex/:col/:row", function($resourcePath, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, "img");
});
$app->get("/library/:resourcePath+/tile.:format/:groupName/:scaleIndex/:col/:row", function($resourcePath, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});
//============================== Mapping Service APIs ==========================================
$app->get("/library/:resourcePath+.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($resourcePath, $scale, $geomtype, $themecat, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
$app->get("/library/:resourcePath+.MapDefinition/plot", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GeneratePlotFromMapDefinition($resId, "dwf");
});
$app->get("/library/:resourcePath+.MapDefinition/plot.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GeneratePlotFromMapDefinition($resId, $format);
});
//================================= Rendering Service APIs ==================================
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