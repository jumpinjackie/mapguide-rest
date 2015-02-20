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

require_once dirname(__FILE__)."/../../controller/datacontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";

/**
 * @SWG\Resource(
 *      apiVersion="1.0",
 *      swaggerVersion="1.2",
 *      description="Data Publishing Framework",
 *      resourcePath="/data"
 * )
 */

/**
 * @SWG\Api(
 *     path="/data/configs.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetDataConfigurations",
 *        summary="Enumerates all data configurations",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/data/configs.:format", function($format) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->EnumerateDataConfigurations($format);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/config",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetDataConfiguration",
 *        summary="Gets the data configuration for the given URI part",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->GetDataConfiguration($args);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/config",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetDataConfigurations",
 *        summary="Set the given data configuration",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part"),
 *          @SWG\parameter(name="data", paramType="form", required=true, type="file", description="The data configuration file to load")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->PutDataConfiguration($args);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/config",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteDataConfigurations",
 *        summary="Delete the given data configuration",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->DeleteConfiguration($args);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/files.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetDataConfigurationFiles",
 *        summary="Gets the list of files for the given data configuration",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/data/:args+/files.:format", function($args, $format) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->EnumerateDataFiles($args, $format);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/file",
 *     @SWG\Operation(
 *        method="POST",
 *        nickname="SetDataFile",
 *        summary="Upload a file for a given data configuration",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part"),
 *          @SWG\parameter(name="filename", paramType="form", required=true, type="string", description="The file name to upload to"),
 *          @SWG\parameter(name="data", paramType="form", required=true, type="file", description="The file to load")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->post("/data/:args+/file", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->PutDataFile($args);
});
/**
 * @SWG\Api(
 *     path="/data/{uriPart}/file",
 *     @SWG\Operation(
 *        method="DELETE",
 *        nickname="DeleteDataFile",
 *        summary="Deletes a file for a given data configuration",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="form", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="uriPart", paramType="path", required=true, type="string", description="The URI part"),
 *          @SWG\parameter(name="filename", paramType="form", required=true, type="string", description="The file name to upload to")
 *        ),
 *        @SWG\ResponseMessage(code=400, message="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->delete("/data/:args+/file", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->DeleteDataFile($args);
});

$app->get("/data/:args+/doc/index.html", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->GetApiDocViewer($args);
});
$app->get("/data/:args+/apidoc", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->GetApiDoc($args);
});
$app->get("/data/:args+/:filename", function($args, $filename) use ($app) {
    $tokens = explode(".", $filename);
    $ctrl = new MgDataController($app);
    if (count($tokens) == 2) {
        if (strlen($tokens[0]) === 0) {
            $ctrl->HandleGet($args, $tokens[1]);
        } else {
            $ctrl->HandleGetSingle($args, $tokens[0], $tokens[1]);
        }
    } else {
        $ctrl->HandleGet($args, substr($filename, 1));
    }
});
$app->post("/data/:args+/:filename", function($args, $filename) use ($app) {
    $tokens = explode(".", $filename);
    $ctrl = new MgDataController($app);
    if (count($tokens) == 2) {
        if (strlen($tokens[0]) === 0) {
            $ctrl->HandlePost($args, $tokens[1]);
        } else {
            $ctrl->HandlePostSingle($args, $tokens[0], $tokens[1]);
        }
    } else {
        $ctrl->HandlePost($args, substr($filename, 1));
    }
});
$app->put("/data/:args+/:filename", function($args, $filename) use ($app) {
    $tokens = explode(".", $filename);
    $ctrl = new MgDataController($app);
    if (count($tokens) == 2) {
        if (strlen($tokens[0]) === 0) {
            $ctrl->HandlePut($args, $tokens[1]);
        } else {
            $ctrl->HandlePutSingle($args, $tokens[0], $tokens[1]);
        }
    } else {
        $ctrl->HandlePut($args, substr($filename, 1));
    }
});
$app->delete("/data/:args+/:filename", function($args, $filename) use ($app) {
    $tokens = explode(".", $filename);
    $ctrl = new MgDataController($app);
    if (count($tokens) == 2) {
        if (strlen($tokens[0]) === 0) {
            $ctrl->HandleDelete($args, $tokens[1]);
        } else {
            $ctrl->HandleDeleteSingle($args, $tokens[0], $tokens[1]);
        }
    } else {
        $ctrl->HandleDelete($args, substr($filename, 1));
    }
});

?>