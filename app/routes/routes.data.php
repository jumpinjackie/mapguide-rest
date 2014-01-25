<?php

require_once dirname(__FILE__)."/../controller/datacontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

$app->get("/data/:args+/config", function($args) use ($app, $container) {
    $ctrl = new MgDataController($app, $container);
    $ctrl->GetDataConfiguration($args);
});
$app->get("/data/:args+/.:extension", function($args, $extension) use ($app, $container) {
    $ctrl = new MgDataController($app, $container);
    $ctrl->HandleGet($args, $extension);
});

?>