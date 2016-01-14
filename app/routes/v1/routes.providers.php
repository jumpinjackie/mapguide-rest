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

require_once dirname(__FILE__)."/../../controller/featureservicecontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";

/**
 *     @SWG\Get(
 *        path="/providers.{type}",
 *        operationId="GetFeatureProviders",
 *        summary="Gets all registered FDO providers",
 *        tags={"providers"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/providers.:format", function($format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetFeatureProviders($format);
});
/**
 *     @SWG\Get(
 *        path="/providers/{providerName}/capabilities.{type}",
 *        operationId="GetProviderCapabilities",
 *        summary="Gets the capabilities of the given FDO provider",
 *        tags={"providers"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="providerName", in="path", required=true, type="string", description="The FDO Provider"),
 *          @SWG\Parameter(name="connection", in="query", required=true, type="string", description="The partial connection string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/providers/:providerName/capabilities.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetProviderCapabilities($providerName, $format);
});
/**
 *     @SWG\Get(
 *        path="/providers/{providerName}/datastores.{type}",
 *        operationId="EnumerateDataStores",
 *        summary="Enumerates the available data stores for this provider with the current connection string",
 *        tags={"providers"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="providerName", in="path", required=true, type="string", description="The FDO Provider"),
 *          @SWG\Parameter(name="connection", in="query", required=true, type="string", description="The partial connection string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/providers/:providerName/datastores.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->EnumerateDataStores($providerName, $format);
});
/**
 *     @SWG\Get(
 *        path="/providers/{providerName}/connectvalues.{type}/{propName}",
 *        operationId="GetConnectPropertyValues",
 *        summary="Enumerates the available values for a given connection property.",
 *        tags={"providers"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="providerName", in="path", required=true, type="string", description="The FDO Provider"),
 *          @SWG\Parameter(name="propName", in="path", required=true, type="string", description="The FDO Provider"),
 *          @SWG\Parameter(name="connection", in="query", required=false, type="string", description="The partial connection string"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/providers/:providerName/connectvalues.:format/:propName", function($providerName, $format, $propName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetConnectPropertyValues($providerName, $propName, $format);
});

?>