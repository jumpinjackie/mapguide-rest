<?php

$app->config(array(
    "GeoRest.ConfigPath" => "./conf",
    "MapGuide.PhysicalTilePath" => "C:/Program Files/OSGeo/MapGuide/Server/Repositories/TileCache",
    "Locale" => "en"
));

$bundlePath = dirname(__FILE__)."/text/".$app->config("Locale");
if (!file_exists($bundlePath))
    $bundlePath = dirname(__FILE__)."/text/en";
if (!file_exists($bundlePath))
    throw new Exception("Could not find string bundle at: ".$bundlePath);
SetLocalizedFilesPath($bundlePath);
//print_r("Using string bundle at: ".$bundlePath);

?>