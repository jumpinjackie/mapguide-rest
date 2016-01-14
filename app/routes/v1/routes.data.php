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
 *     @SWG\Get(
 *        path="/data/configs.{type}",
 *        operationId="GetDataConfigurations",
 *        summary="Enumerates all data configurations",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/data/configs.:format", function($format) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->EnumerateDataConfigurations($format);
});
/**
 *     @SWG\Get(
 *        path="/data/{uriPart}/config",
 *        operationId="GetDataConfiguration",
 *        summary="Gets the data configuration for the given URI part",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->GetDataConfiguration($args);
});
/**
 *     @SWG\Post(
 *        path="/data/{uriPart}/config",
 *        operationId="SetDataConfigurations",
 *        summary="Set the given data configuration",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *          @SWG\Parameter(name="data", in="formData", required=true, type="file", description="The data configuration file to load"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->PutDataConfiguration($args);
});
/**
 *     @SWG\Delete(
 *        path="/data/{uriPart}/config",
 *        operationId="DeleteDataConfigurations",
 *        summary="Delete the given data configuration",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->delete("/data/:args+/config", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->DeleteConfiguration($args);
});
/**
 *     @SWG\Get(
 *        path="/data/{uriPart}/files.{type}",
 *        operationId="GetDataConfigurationFiles",
 *        summary="Gets the list of files for the given data configuration",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/data/:args+/files.:format", function($args, $format) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->EnumerateDataFiles($args, $format);
});
/**
 *     @SWG\Post(
 *        path="/data/{uriPart}/file",
 *        operationId="SetDataFile",
 *        summary="Upload a file for a given data configuration",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *          @SWG\Parameter(name="filename", in="formData", required=true, type="string", description="The file name to upload to"),
 *          @SWG\Parameter(name="data", in="formData", required=true, type="file", description="The file to load"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->post("/data/:args+/file", function($args) use ($app) {
    $ctrl = new MgDataController($app);
    $ctrl->PutDataFile($args);
});
/**
 *     @SWG\Delete(
 *        path="/data/{uriPart}/file",
 *        operationId="DeleteDataFile",
 *        summary="Deletes a file for a given data configuration",
 *        tags={"data"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="uriPart", in="path", required=true, type="string", description="The URI part"),
 *          @SWG\Parameter(name="filename", in="formData", required=true, type="string", description="The file name to upload to"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
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