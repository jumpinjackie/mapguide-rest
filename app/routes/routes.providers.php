<?php

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