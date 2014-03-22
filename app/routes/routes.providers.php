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

require_once dirname(__FILE__)."/../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="FDO Provider Registry",
 *      resourcePath="/providers"
 * )
 */

/**
 * @SWG\Api(
 *     path="/providers",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetFeatureProviders",
 *        summary="Gets all registered FDO providers",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/providers", function() use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetFeatureProviders("xml");
});
/**
 * @SWG\Api(
 *     path="/providers.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetFeatureProviders",
 *        summary="Gets all registered FDO providers",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/providers.:format", function($format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetFeatureProviders($format);
});
/**
 * @SWG\Api(
 *     path="/providers/{providerName}/capabilities",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetProviderCapabilities",
 *        summary="Gets the capabilities of the given FDO provider",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="providerName", paramType="path", required=true, type="string", description="The FDO Provider")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/providers/:providerName/capabilities", function($providerName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetProviderCapabilities($providerName, "xml");
});
/**
 * @SWG\Api(
 *     path="/providers/{providerName}/capabilities.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetProviderCapabilities",
 *        summary="Gets the capabilities of the given FDO provider",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="providerName", paramType="path", required=true, type="string", description="The FDO Provider"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/providers/:providerName/capabilities.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetProviderCapabilities($providerName, $format);
});
$app->get("/providers/:providerName/datastores", function($providerName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->EnumerateDataStores($providerName, "xml");
});
$app->get("/providers/:providerName/datastores.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->EnumerateDataStores($providerName, $format);
});
$app->get("/providers/:providerName/connectvalues/:propName", function($providerName, $propName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetConnectPropertyValues($providerName, $propName, "xml");
});
$app->get("/providers/:providerName/connectvalues.:format/:propName", function($providerName, $format, $propName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetConnectPropertyValues($providerName, $propName, $format);
});

?>