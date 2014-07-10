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
require_once dirname(__FILE__)."/../controller/viewercontroller.php";
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        summary="Gets the full description of the specified schema",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        summary="Gets the full description of the specified schema",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
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
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="InsertFeatures",
 *        summary="Inserts one or more features into the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowInsert=1 in its resource header otherwise inserts will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The Feature Set XML describing the features to be inserted")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=403, message="Feature Source is not configured to allow inserts"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/library/:resourcePath+.FeatureSource/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->InsertFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="PUT",
 *        nickname="UpdateFeatures",
 *        summary="Updates one or more features into the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowUpdate=1 in its resource header otherwise updates will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The XML envelope describing the features to be update")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=403, message="Feature Source is not configured to allow updates"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->put("/library/:resourcePath+.FeatureSource/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->UpdateFeatures($resId, $schemaName, $className);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/features/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteFeatures",
 *        summary="Deletes one or more features from the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowDelete=1 in its resource header otherwise deletes will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="filter", paramType="form", required=true, type="string", description="The FDO filter determining what features to delete")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=403, message="Feature Source is not configured to allow deletes"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/library/:resourcePath+.FeatureSource/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
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
$app->get("/library/:resourcePath+.FeatureSource/features/:schemaName/:className", function($resourcePath, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
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
$app->get("/library/:resourcePath+.FeatureSource/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectFeatures($resId, $schemaName, $className, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/aggregates/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']"),
 *          @SWG\parameter(name="property", paramType="query", required=false, type="string", description="The property name to get distinct values of. Only applies if aggregate type is 'distinctvalues'")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/aggregates/:type/:schemaName/:className", function($resourcePath, $type, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/aggregates.{type}/{aggregateType}/{schemaName}/{className}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="schemaName", paramType="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\parameter(name="className", paramType="path", required=true, type="string", description="The class name"),
 *          @SWG\parameter(name="aggregateType", paramType="path", required=true, type="string", description="aggregate type", enum="['count','bbox','distinctvalues']"),
 *          @SWG\parameter(name="property", paramType="query", required=false, type="string", description="The property name to get distinct values of. Only applies if aggregate type is 'distinctvalues'"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/aggregates.:format/:type/:schemaName/:className", function($resourcePath, $format, $type, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectAggregates($resId, $schemaName, $className, $type, $format);
});

//================================== Resource Service APIs ======================================================

/**
 * @SWG\Api(
 *     path="/library",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="ApplyResourcePackage",
 *        summary="Loads the specified package into the Site Repository",
 *        @SWG\consumes("multipart/form-data"),
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="MAX_FILE_SIZE", paramType="form", required=true, type="integer", description="Maximum file size"),
 *          @SWG\parameter(name="package", paramType="form", required=true, type="file", description="The package file to load")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/library", function() use ($app) {
    $ctrl = new MgResourceServiceController($app);
    $ctrl->ApplyResourcePackage();
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/datalist",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/datalist", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/datalist.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/datalist.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/list",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResources",
 *        summary="Lists the resources for the root of the Site Repository",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="depth", paramType="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\parameter(name="computechildren", paramType="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\parameter(name="type", paramType="query", required=false, type="string", description="An optional resource type to filter on", enum="['Folder','FeatureSource','LayerDefinition','MapDefinition','WebLayout','ApplicationDefinition','LoadProcedure','DrawingSource','SymbolLibrary','PrintLayout','SymbolDefinition']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/list", function() use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/list.{dataType}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResources",
 *        summary="Lists the resources for the root of the Site Repository",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="depth", paramType="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\parameter(name="computechildren", paramType="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\parameter(name="type", paramType="query", required=false, type="string", description="An optional resource type to filter on", enum="['Folder','FeatureSource','LayerDefinition','MapDefinition','WebLayout','ApplicationDefinition','LoadProcedure','DrawingSource','SymbolLibrary','PrintLayout','SymbolDefinition']"),
 *          @SWG\parameter(name="dataType", paramType="path", required=true, type="string", description="xml, json or html", enum="['xml','json','html']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/list.:format", function($format) use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/list",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResources",
 *        summary="Lists the resources for a given folder resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the folder resource ID"),
 *          @SWG\parameter(name="depth", paramType="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\parameter(name="computechildren", paramType="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\parameter(name="type", paramType="query", required=false, type="string", description="An optional resource type to filter on", enum="['Folder','FeatureSource','LayerDefinition','MapDefinition','WebLayout','ApplicationDefinition','LoadProcedure','DrawingSource','SymbolLibrary','PrintLayout','SymbolDefinition']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/list", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "list");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/list.{dataType}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResources",
 *        summary="Lists the resources for a given folder resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the folder resource ID"),
 *          @SWG\parameter(name="depth", paramType="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\parameter(name="computechildren", paramType="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\parameter(name="type", paramType="query", required=false, type="string", description="An optional resource type to filter on", enum="['Folder','FeatureSource','LayerDefinition','MapDefinition','WebLayout','ApplicationDefinition','LoadProcedure','DrawingSource','SymbolLibrary','PrintLayout','SymbolDefinition']"),
 *          @SWG\parameter(name="dataType", paramType="path", required=true, type="string", description="xml, json or html", enum="['xml','json','html']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/list.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "list.".$format);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/data/{dataName}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceData",
 *        summary="Gets the specified resource data item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="dataName", paramType="path", required=true, type="string", description="The name of the resource data to retrieve")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/data/:dataName", function($resourcePath, $dataName) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceData($resId, $dataName);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/header",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetResourceHeader",
 *        summary="Sets the resource header for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The resource XML header")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/library/:resourcePath+/header", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceHeader($resId);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/header",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceHeader",
 *        summary="Gets the specified resource header item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/header", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/header.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceHeader",
 *        summary="Gets the specified resource header item for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/header.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/content",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetResourceContent",
 *        summary="Sets the resource content for the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="body", paramType="body", required=true, type="string", description="The resource XML content")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/library/:resourcePath+/content", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContent($resId);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/content",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/content", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/content.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/content.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/references",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/references", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, "xml");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}/references.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+/references.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteResource",
 *        summary="Deletes the given resource",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the resource ID (including extension)")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/library/:resourcePath+", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResource($resId);
});
//============================== Tile Service APIs ============================================

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/xyz/{groupName}/{z}/{x}/{y}/tile.{format}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetTileXYZ",
 *        summary="Gets the specified tile for the given map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\parameter(name="z", paramType="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\parameter(name="x", paramType="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\parameter(name="y", paramType="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\parameter(name="format", paramType="path", required=true, type="string", description="The tile type", enum="['png', 'jpg', 'png8', 'gif', 'json']")
 *        ),
 *        @SWG\ResponseMessage(code=304, message="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.MapDefinition/xyz/:groupName/:z/:x/:y/tile.:format", function($resourcePath, $groupName, $z, $x, $y, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    //echo "ResId: ".$resId->ToString()."<br/>Group: $groupName<br/>X: $x<br/>Y: $y<br/>Z: $z<br/>Format: $format";
    //die;
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTileXYZ($resId, $groupName, $x, $y, $z, $format);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/tile/{groupName}/{scaleIndex}/{col}/{row}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\parameter(name="scaleIndex", paramType="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\parameter(name="col", paramType="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\parameter(name="row", paramType="path", required=true, type="integer", description="The row of the tile to fetch")
 *        ),
 *        @SWG\ResponseMessage(code=304, message="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.MapDefinition/tile/:groupName/:scaleIndex/:col/:row", function($resourcePath, $groupName, $scaleIndex, $col, $row) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, "img");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/tile.{type}/{groupName}/{scaleIndex}/{col}/{row}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\parameter(name="scaleIndex", paramType="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\parameter(name="col", paramType="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\parameter(name="row", paramType="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="The tile type", enum="['img']")
 *        ),
 *        @SWG\ResponseMessage(code=304, message="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.MapDefinition/tile.:format/:groupName/:scaleIndex/:col/:row", function($resourcePath, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});

//============================== Mapping Service APIs ==========================================

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.LayerDefinition/legend/{scale}/{geomType}/{themecat}/icon.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GenerateLegendImage",
 *        summary="Generates the specified icon for the given Layer Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Layer Definition"),
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
$app->get("/library/:resourcePath+.LayerDefinition/legend/:scale/:geomtype/:themecat/icon.:format", function($resourcePath, $scale, $geomtype, $themecat, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format);
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/plot",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GeneratePlotFromMapDefinition",
 *        summary="Plot the map to an EPlot DWF using the center and scale from the map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.MapDefinition/plot", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMappingServiceController($app);
    $ctrl->GeneratePlotFromMapDefinition($resId, "dwf");
});
/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/plot.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GeneratePlotFromMapDefinition",
 *        summary="Plot the map to the specified type using the center and scale from the map",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
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
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
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

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/image.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="RenderMapDefinition",
 *        summary="Renders an image of the specified map definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\parameter(name="x", paramType="query", required=true, type="integer", description="The X coordinate of the map center to render"),
 *          @SWG\parameter(name="y", paramType="query", required=true, type="integer", description="The Y coordinate of the map center to render"),
 *          @SWG\parameter(name="scale", paramType="query", required=true, type="double", description="The map scale to render"),
 *          @SWG\parameter(name="width", paramType="query", required=false, type="integer", description="The width of the image"),
 *          @SWG\parameter(name="height", paramType="query", required=false, type="integer", description="The height of the image"),
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
$app->get("/library/:resourcePath+.MapDefinition/image.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgRenderingServiceController($app);
    $ctrl->RenderMapDefinition($resId, $format);
});

// ----------------------------- Viewer/Preview Launchers ----------------------------- //

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.WebLayout/viewer",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="LaunchAJAXViewer",
 *        summary="Launch the AJAX Viewer for the specified Web Layout",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Web Layout")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.WebLayout/viewer", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".WebLayout";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchAjaxViewer($resId);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.ApplicationDefinition/{template}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="LaunchFusionViewer",
 *        summary="Launch the Fusion Viewer for the specified ApplicationDefinition using the given template",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Application Definition"),
 *          @SWG\parameter(name="template", paramType="path", required=true, type="string", description="The fusion template to invoke")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.ApplicationDefinition/viewer/:template", function($resourcePath, $template) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".ApplicationDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchFusionViewer($resId, $template);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.FeatureSource/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewFeatureSource",
 *        summary="Launches the schema report preview for the given Feature Source",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Feature Source")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.FeatureSource/preview", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.LayerDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewLayerDefinition",
 *        summary="Launches the AJAX viewer preview for the given Layer Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Layer Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.LayerDefinition/preview", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.MapDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewMapDefinition",
 *        summary="Launches the AJAX viewer preview for the given Map Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Map Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.MapDefinition/preview", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.SymbolDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewSymbolDefinition",
 *        summary="Launches the AJAX viewer preview for the given Symbol Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Symbol Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.SymbolDefinition/preview", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".SymbolDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

/**
 * @SWG\Api(
 *     path="/library/{resourcePath}.WatermarkDefinition/preview",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="PreviewWatermarkDefinition",
 *        summary="Launches the AJAX viewer preview for the given Watermark Definition",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="resourcePath", paramType="path", required=true, type="string", description="The path of the Watermark Definition")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/library/:resourcePath+.WatermarkDefinition/preview", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".WatermarkDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgViewerController($app);
    $ctrl->LaunchResourcePreview($resId);
});

?>
