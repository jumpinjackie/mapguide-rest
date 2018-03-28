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

require_once dirname(__FILE__)."/../../controller/kmlservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/tileservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/mappingservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/renderingservicecontroller.php";
require_once dirname(__FILE__)."/../../controller/viewercontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";

//======================== Feature Service APIs =======================================

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/status.{type}",
 *        operationId="TestConnection",
 *        summary="Tests the connection status of a feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.FeatureSource/status.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->TestConnection($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/editcapabilities.{type}",
 *        operationId="GetRestEditCapabilities",
 *        summary="Gets the REST API edit capabilities of the given Feature Source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=200, description="Successful operation", @SWG\Schema(ref="#/definitions/RestEditCapabilities")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.FeatureSource/editcapabilities.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetEditCapabilities($resId, $format);
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}.FeatureSource/xml",
 *        operationId="CreateFeatureSource",
 *        summary="Creates the given Feature Source",
 *        tags={"library"},
 *        consumes={"application/xml"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The XML that describes the Feature Source to create"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+.FeatureSource/xml", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->CreateFeatureSource($resId, "xml");
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}.FeatureSource/json",
 *        operationId="CreateFeatureSource",
 *        summary="Creates the given Feature Source",
 *        tags={"library"},
 *        consumes={"application/json"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The JSON that describes the Feature Source to create", @SWG\Schema(ref="#/definitions/CreateFeatureSourceEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+.FeatureSource/json", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->CreateFeatureSource($resId, "json");
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}.FeatureSource/editcapabilities.{type}",
 *        operationId="SetRestEditCapabilities",
 *        summary="Sets the REST API edit capabilities of the given Feature Source",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The edit capabilities XML or JSON", @SWG\Schema(ref="#/definitions/RestEditCapabilities")),
 *        @SWG\Response(response=201, description="Capabilities set successfully"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+.FeatureSource/editcapabilities.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SetEditCapabilities($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/spatialcontexts.{type}",
 *        operationId="GetSpatialContexts",
 *        summary="Gets spatial contexts of a feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/longtransactions.{type}",
 *        operationId="GetLongTransactions",
 *        summary="Gets long transactions of a feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="active", in="query", required=false, type="boolean", description="Return only active long transactions if true, otherwise returns all long transactions"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.FeatureSource/longtransactions.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetLongTransactions($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/schemas.{type}",
 *        operationId="GetSchemaNames",
 *        summary="Gets the schema names of a feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/schema.{type}/{schemaName}",
 *        operationId="DescribeSchema",
 *        summary="Gets the full description of the specified schema",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="classnames", in="query", required=false, type="string", description="The dot-separated list of class names"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/classes.{type}/{schemaName}",
 *        operationId="GetClassNames",
 *        summary="Gets the class names of the given schema for a feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/classdef.{type}/{schemaName}/{className}",
 *        operationId="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/classdef.{type}/{qualifiedClassName}",
 *        operationId="GetClassDefinition",
 *        summary="Gets a class definition of the specified name from the feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="qualifiedClassName", in="path", required=true, type="string", description="The qualified class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Post(
 *     path="/library/{resourcePath}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="InsertFeatures",
 *        summary="Inserts one or more features into the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowInsert=1 in its resource header otherwise inserts will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The Feature Set XML describing the features to be inserted", @SWG\Schema(ref="#/definitions/InsertFeaturesEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=403, description="Feature Source is not configured to allow inserts"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+.FeatureSource/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->InsertFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Put(
 *        path="/library/{resourcePath}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        method="PUT",
 *        operationId="UpdateFeatures",
 *        summary="Updates one or more features into the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowUpdate=1 in its resource header otherwise updates will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The XML envelope describing the features to be update", @SWG\Schema(ref="#/definitions/UpdateFeaturesEnvelope")),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=403, description="Feature Source is not configured to allow updates"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->put("/library/:resourcePath+.FeatureSource/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->UpdateFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Delete(
 *        path="/library/{resourcePath}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        method="DELETE",
 *        operationId="DeleteFeatures",
 *        summary="Deletes one or more features from the given feature class for the specified feature source. The Feature Source in question must have _MgRestAllowDelete=1 in its resource header otherwise deletes will be forbidden. If _MgRestUseTransaction=1 in the resource header, transactions will be used.",
 *        tags={"library"},
 *        consumes={"application/xml", "application/json"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="filter", in="formData", required=true, type="string", description="The FDO filter determining what features to delete"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=403, description="Feature Source is not configured to allow deletes"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/library/:resourcePath+.FeatureSource/features.:format/:schemaName/:className", function($resourcePath, $format, $schemaName, $className) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".FeatureSource";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->DeleteFeatures($resId, $schemaName, $className, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/features.{type}/{schemaName}/{className}",
 *        operationId="SelectFeatures",
 *        summary="Queries features from the specified feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="filter", in="query", required=false, type="string", description="The FDO filter to apply"),
 *          @SWG\Parameter(name="properties", in="query", required=false, type="string", description="A comma-separated list of proprety names"),
 *          @SWG\Parameter(name="maxfeatures", in="query", required=false, type="integer", description="The maximum number of features to restrict this response to"),
 *          @SWG\Parameter(name="transformto", in="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\Parameter(name="bbox", in="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\Parameter(name="bboxistargetcs", in="query", required=false, type="boolean", description="Indicates if the bbox should be interpreted in the coordinate system specified in the transformto parameter"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml","geojson"}),
 *          @SWG\Parameter(name="orderby", in="query", required=false, type="string", description="A comma-separated list of property names"),
 *          @SWG\Parameter(name="orderoption", in="query", required=false, type="string", description="(asc)ending or (desc)ending", enum={"asc","desc"}),
 *          @SWG\Parameter(name="pagesize", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the number of results for the page."),
 *          @SWG\Parameter(name="page", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the page number of the page. You must specify a valid page size value (> 0) for this parameter to apply."),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.LayerDefinition/features.{type}",
 *        operationId="SelectLayerFeatures",
 *        summary="Queries features from the specified layer definition. Any hyperlink, tooltip and elevation expressions in the Layer Definition will be computed and returned in the response",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="properties", in="query", required=false, type="string", description="A comma-separated list of property names. Has no effect if format is CZML"),
 *          @SWG\Parameter(name="filter", in="query", required=false, type="string", description="The FDO filter to apply. This will be combined with whatever filter is defined in the layer"),
 *          @SWG\Parameter(name="maxfeatures", in="query", required=false, type="integer", description="The maximum number of features to restrict this response to"),
 *          @SWG\Parameter(name="transformto", in="query", required=false, type="string", description="The CS-Map coordinate system code to transform the resulting features into"),
 *          @SWG\Parameter(name="mappedonly", in="query", required=false, type="boolean", description="If true, will use only the properties specified in the display mappings in the Layer Definition. Takes precedence over the properties parameter if both specified. Has no effect if format is CZML"),
 *          @SWG\Parameter(name="displayproperties", in="query", required=false, type="boolean", description="If true, will use any display mappings specified in the Layer Definition, only applicable when mappedonly=1 and format is HTML/GeoJSON"),
 *          @SWG\Parameter(name="includegeom", in="query", required=false, type="boolean", description="Include the geometry, only applicable when mappedonly=1. Has no effect if format is CZML"),
 *          @SWG\Parameter(name="bbox", in="query", required=false, type="string", description="A comma-separated quartet (x1,y1,x2,y2) defining the spatial filter geometry"),
 *          @SWG\Parameter(name="bboxistargetcs", in="query", required=false, type="boolean", description="Indicates if the bbox should be interpreted in the coordinate system specified in the transformto parameter"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml, geojson, html or czml", enum={"xml","geojson","html","czml"}),
 *          @SWG\Parameter(name="pagesize", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the number of results for the page. You cannot specify this parameter for CZML output"),
 *          @SWG\Parameter(name="page", in="query", required=false, type="integer", description="Applies pagination on the query result. This specifies the page number of the page. You must specify a valid page size value (> 0) for this parameter to apply. You cannot specify this parameter for CZML output"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.LayerDefinition/features.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->SelectLayerFeatures($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/aggregates.{type}/{aggregateType}/{schemaName}/{className}",
 *        operationId="SelectAggregates",
 *        summary="Queries features from the specified feature source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="schemaName", in="path", required=true, type="string", description="The FDO schema name"),
 *          @SWG\Parameter(name="className", in="path", required=true, type="string", description="The class name"),
 *          @SWG\Parameter(name="aggregateType", in="path", required=true, type="string", description="aggregate type", enum={"count", "bbox", "distinctvalues"}),
 *          @SWG\Parameter(name="property", in="query", required=false, type="string", description="The property name to get distinct values of. Only applies if aggregate type is 'distinctvalues'"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Post(
 *        path="/library",
 *        operationId="ApplyResourcePackage",
 *        summary="Loads the specified package into the Site Repository",
 *        tags={"library"},
 *        consumes={"multipart/form-data"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="MAX_FILE_SIZE", in="formData", required=true, type="integer", description="Maximum file size"),
 *          @SWG\Parameter(name="package", in="formData", required=true, type="file", description="The package file to load"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library", function() use ($app) {
    $ctrl = new MgResourceServiceController($app);
    $ctrl->ApplyResourcePackage();
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/datalist.{type}",
 *        operationId="EnumerateResourceData",
 *        summary="Lists the resource data for a given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/datalist.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceData($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/list.{dataType}",
 *        operationId="EnumerateResources",
 *        summary="Lists the resources for the root of the Site Repository",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="depth", in="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\Parameter(name="computechildren", in="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\Parameter(name="type", in="query", required=false, type="string", description="An optional resource type to filter on", enum={"Folder", "FeatureSource", "LayerDefinition", "MapDefinition", "WebLayout", "ApplicationDefinition", "LoadProcedure", "DrawingSource", "SymbolLibrary", "PrintLayout", "SymbolDefinition"}),
 *          @SWG\Parameter(name="dataType", in="path", required=true, type="string", description="xml, json or html", enum={"xml", "json", "html"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/list.:format", function($format) use ($app) {
    $resId = new MgResourceIdentifier("Library://");
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/list.{dataType}",
 *        operationId="EnumerateResources",
 *        summary="Lists the resources for a given folder resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the folder resource ID"),
 *          @SWG\Parameter(name="depth", in="query", required=false, type="integer", description="The depth at which to enumerate"),
 *          @SWG\Parameter(name="computechildren", in="query", required=false, type="boolean", description="Indicates if data about children should be computed"),
 *          @SWG\Parameter(name="type", in="query", required=false, type="string", description="An optional resource type to filter on", enum={"Folder", "FeatureSource", "LayerDefinition", "MapDefinition", "WebLayout", "ApplicationDefinition", "LoadProcedure", "DrawingSource", "SymbolLibrary", "PrintLayout", "SymbolDefinition"}),
 *          @SWG\Parameter(name="dataType", in="path", required=true, type="string", description="xml, json or html", enum={"xml", "json", "html"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/list.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath, "list.".$format);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResources($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/data/{dataName}",
 *        operationId="GetResourceData",
 *        summary="Gets the specified resource data item for the given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/data/:dataName", function($resourcePath, $dataName) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceData($resId, $dataName);
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}/data/{dataName}",
 *        operationId="SetResourceData",
 *        summary="Sets the specified resource data item for the given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *          @SWG\Parameter(name="type", in="formData", required=true, type="string", description="The type of resource data", enum={"File", "Stream", "String"}),
 *          @SWG\Parameter(name="data", in="formData", required=true, type="file", description="The resource data file to load"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+/data/:dataName", function($resourcePath, $dataName) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceData($resId, $dataName);
});
/**
 *     @SWG\Delete(
 *        path="/library/{resourcePath}/data/{dataName}",
 *        operationId="DeleteResourceData",
 *        summary="Deletes the specified resource data item for the given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="dataName", in="path", required=true, type="string", description="The name of the resource data to retrieve"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/library/:resourcePath+/data/:dataName", function($resourcePath, $dataName) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResourceData($resId, $dataName);
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}/header.{type}",
 *        operationId="SetResourceHeader",
 *        summary="Sets the resource header for the given resource",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The resource XML header"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+/header.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceHeader($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/header.{type}",
 *        operationId="GetResourceHeader",
 *        summary="Gets the specified resource header item for the given resource",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/header.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceHeader($resId, $format);
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}/contentorheader.{type}",
 *        operationId="SetResourceContentOrHeader",
 *        summary="Sets the resource content and/or header for the given resource",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="content", in="formData", required=true, type="file", description="The resource XML content"),
 *          @SWG\Parameter(name="header", in="formData", required=true, type="file", description="The resource XML header"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+/contentorheader.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContentOrHeader($resId, $format);
});
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}/content.{type}",
 *        operationId="SetResourceContent",
 *        summary="Sets the resource content for the given resource",
 *        tags={"library"},
 *        consumes={"application/json", "application/xml"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="body", in="body", required=true, type="string", description="The resource XML content"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/library/:resourcePath+/content.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->SetResourceContent($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/html",
 *        operationId="ResourceInfoHtml",
 *        summary="Generates a HTML information page for the given resource ID",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID (including extension)"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/html", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceInfo($resId, "html");
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/content.{type}",
 *        operationId="GetResourceContent",
 *        summary="Gets the specified resource content for the given resource ID",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/content.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->GetResourceContent($resId, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}/references.{type}",
 *        operationId="EnumerateResourceReferences",
 *        summary="Lists the resources that reference the given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+/references.:format", function($resourcePath, $format) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->EnumerateResourceReferences($resId, $format);
});
/**
 *     @SWG\Delete(
 *        path="/library/{resourcePath}",
 *        operationId="DeleteResource",
 *        summary="Deletes the given resource",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the resource ID (including extension)"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/library/:resourcePath+", function($resourcePath) use ($app) {
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgResourceServiceController($app);
    $ctrl->DeleteResource($resId);
});
//============================== Tile Service APIs ============================================

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/xyz/{groupName}/{z}/{x}/{y}/tile.{format}",
 *        operationId="GetTileXYZ",
 *        summary="Gets the specified tile for the given map definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\Parameter(name="z", in="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\Parameter(name="x", in="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\Parameter(name="y", in="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\Parameter(name="format", in="path", required=true, type="string", description="The tile type", enum={"png", "jpg", "png8", "gif", "json"}),
 *        @SWG\Response(response=304, description="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/xyz/{groupName}/{layerName}/{z}/{x}/{y}/tile.{format}",
 *        operationId="GetTileXYZForLayer",
 *        summary="Gets the specified tile for the given map definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\Parameter(name="layerName", in="path", required=true, type="string", description="The name of the layer within this group"),
 *          @SWG\Parameter(name="z", in="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\Parameter(name="x", in="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\Parameter(name="y", in="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\Parameter(name="format", in="path", required=true, type="string", description="The tile type", enum={"json"}),
 *        @SWG\Response(response=304, description="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.MapDefinition/xyz/:groupName/:layerName/:z/:x/:y/tile.:format", function($resourcePath, $groupName, $layerName, $z, $x, $y, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    //echo "ResId: ".$resId->ToString()."<br/>Group: $groupName<br/>X: $x<br/>Y: $y<br/>Z: $z<br/>Format: $format";
    //die;
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTileXYZForLayer($resId, $groupName, $layerName, $x, $y, $z, $format);
});
/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/tile.{type}/{groupName}/{scaleIndex}/{col}/{row}",
 *        operationId="GetTile",
 *        summary="Gets the specified tile for the given map definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The tiled group of the Map Definition"),
 *          @SWG\Parameter(name="scaleIndex", in="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\Parameter(name="col", in="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\Parameter(name="row", in="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The tile type", enum={"img"}),
 *        @SWG\Response(response=304, description="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.TileSetDefinition/tile.{type}/{groupName}/{scaleIndex}/{col}/{row}",
 *        operationId="GetTile",
 *        summary="Gets the specified tile for the given tile set definition.",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Tile Set Definition"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The tiled group of the Tile Set Definition"),
 *          @SWG\Parameter(name="scaleIndex", in="path", required=true, type="integer", description="The finite scale index"),
 *          @SWG\Parameter(name="col", in="path", required=true, type="integer", description="The column of the tile to fetch"),
 *          @SWG\Parameter(name="row", in="path", required=true, type="integer", description="The row of the tile to fetch"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="The tile type", enum={"img"}),
 *        @SWG\Response(response=304, description="This tile has not been modified. Your previously fetched tile is still the current one"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.TileSetDefinition/tile.:format/:groupName/:scaleIndex/:col/:row", function($resourcePath, $format, $groupName, $scaleIndex, $col, $row) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".TileSetDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgTileServiceController($app);
    $ctrl->GetTile($resId, $groupName, $scaleIndex, $col, $row, $format);
});

//============================== Mapping Service APIs ==========================================

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.LayerDefinition/legend/{scale}/{geomType}/{themecat}/icon.{type}",
 *        operationId="GenerateLegendImage",
 *        summary="Generates the specified icon for the given Layer Definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Layer Definition"),
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/plot.{type}",
 *        operationId="GeneratePlotFromMapDefinition",
 *        summary="Plot the map to the specified type using the center and scale from the map",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="x", in="query", required=true, type="integer", description="The X coordinate of the map center to plot"),
 *          @SWG\Parameter(name="y", in="query", required=true, type="integer", description="The Y coordinate of the map center to plot"),
 *          @SWG\Parameter(name="scale", in="query", required=true, type="number", description="The map scale to plot"),
 *          @SWG\Parameter(name="dpi", in="query", required=false, type="integer", description="The map display DPI to plot at. Defaults to 96 if not specified. Only applicable for PDF plots"),
 *          @SWG\Parameter(name="papersize", in="query", required=true, type="string", description="The paper size", enum={"A3", "A4", "A5", "Letter", "Legal"}),
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/image.{type}",
 *        operationId="RenderMapDefinition",
 *        summary="Renders an image of the specified map definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.WebLayout/viewer",
 *        operationId="LaunchAJAXViewer",
 *        summary="Launch the AJAX Viewer for the specified Web Layout",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Web Layout"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.ApplicationDefinition/{template}",
 *        operationId="LaunchFusionViewer",
 *        summary="Launch the Fusion Viewer for the specified ApplicationDefinition using the given template",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Application Definition"),
 *          @SWG\Parameter(name="template", in="path", required=true, type="string", description="The fusion template to invoke"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.FeatureSource/preview",
 *        operationId="PreviewFeatureSource",
 *        summary="Launches the schema report preview for the given Feature Source",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Feature Source"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.LayerDefinition/preview",
 *        operationId="PreviewLayerDefinition",
 *        summary="Launches the AJAX viewer preview for the given Layer Definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Layer Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/preview",
 *        operationId="PreviewMapDefinition",
 *        summary="Launches the AJAX viewer preview for the given Map Definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.SymbolDefinition/preview",
 *        operationId="PreviewSymbolDefinition",
 *        summary="Launches the AJAX viewer preview for the given Symbol Definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Symbol Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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
 *     @SWG\Get(
 *        path="/library/{resourcePath}.WatermarkDefinition/preview",
 *        operationId="PreviewWatermarkDefinition",
 *        summary="Launches the AJAX viewer preview for the given Watermark Definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Watermark Definition"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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

// =========================================== KML Service APIs ====================================================

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.MapDefinition/kml",
 *        operationId="GetMapKml",
 *        summary="Gets the KML for the specified map definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="native", in="query", required=false, type="boolean", description="If true, this operation will simply pass through to the mapagent. This is much faster, but note that all network link URLs will be referring to the mapagent instead of downstream RESTful layer KML URLs."),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/library/:resourcePath+.MapDefinition/kml", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetMapKml($resId, "kml");
});

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.LayerDefinition/kml",
 *        operationId="GetMapKml",
 *        summary="Gets the KML for the specified layer definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Layer Definition"),
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
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
$app->get("/library/:resourcePath+.LayerDefinition/kml", function($resourcePath) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetLayerKml($resId, "kml");
});

/**
 *     @SWG\Get(
 *        path="/library/{resourcePath}.LayerDefinition/kmlfeatures.{type}",
 *        operationId="GetFeaturesKml",
 *        summary="Gets the features KML for the specified layer definition",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Layer Definition"),
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
$app->get("/library/:resourcePath+.LayerDefinition/kmlfeatures.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".LayerDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgKmlServiceController($app);
    $ctrl->GetFeaturesKml($resId, $format);
});