<?php

require_once "wktgeometryoutputformatter.php";

$app->container->GeomWKT = function() {
    return new MgWktGeometryOutputFormatter();
};

?>