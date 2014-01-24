<?php

require 'vendor/autoload.php';
include dirname(__FILE__)."/../mapadmin/constants.php";

$webConfigPath = dirname(__FILE__)."/../webconfig.ini";
MgInitializeWebTier($webConfigPath);

$app = new \Slim\Slim();
include "app/config.php";
include "app/routes/routes.data.php";
include "app/routes/routes.library.php";
include "app/routes/routes.session.php";
include "app/routes/routes.coordsys.php";
include "app/routes/routes.providers.php";
include "app/routes/routes.site.php";
include "app/routes/routes.admin.php";
$app->get("/", function() {
    echo "Hello World";
});

$app->run();

?>