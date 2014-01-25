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