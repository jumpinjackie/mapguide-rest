<?php

$app->get("/admin/:args+", function($args) use ($app) {
    var_dump($args);
    var_dump($app->request->get());
});

?>