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

$app->get("/providers", function() use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetFeatureProviders("xml");
});
$app->get("/providers.:format", function($format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetFeatureProviders($format);
});
$app->get("/provider/:providerName/capabilities", function($providerName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetProviderCapabilities($providerName, "xml");
});
$app->get("/provider/:providerName/capabilities.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetProviderCapabilities($providerName, $format);
});
$app->get("/provider/:providerName/datastores", function($providerName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->EnumerateDataStores($providerName, "xml");
});
$app->get("/provider/:providerName/datastores.:format", function($providerName, $format) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->EnumerateDataStores($providerName, $format);
});
$app->get("/provider/:providerName/connectvalues/:propName", function($providerName, $propName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetConnectPropertyValues($providerName, $propName, "xml");
});
$app->get("/provider/:providerName/connectvalues.:format/:propName", function($providerName, $format, $propName) use ($app) {
    $ctrl = new MgFeatureServiceController($app);
    $ctrl->GetConnectPropertyValues($providerName, $propName, $format);
});

?>