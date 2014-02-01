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

require_once "wktgeometryoutputformatter.php";
require_once "kmlgeometryoutputformatter.php";

require_once "defaultdatetimeoutputformatter.php";

$app->container->GeomWKT = function() {
    return new MgWktGeometryOutputFormatter();
};
$app->container->GeomKML = function() {
    return new MgKmlGeometryOutputFormatter();
};

$app->container->DateDefault = function() {
    return new MgDefaultDateTimeOutputFormatter();
};
$app->container->DateDMY = function() {
    return new MgDMYDateTimeFormatter();
};
$app->container->DateMDY = function() {
    return new MgMDYDateTimeFormatter();
};
$app->container->DateISO9601 = function() {
    return new MgISO9601DateTimeFormatter();
};
$app->container->DateDMYFull = function() {
    return new MgDMYFullDateTimeFormatter();
};
$app->container->DateMDYFull = function() {
    return new MgMDYFullDateTimeFormatter();
};
$app->container->DateISO9601Full = function() {
    return new MgISO9601FullDateTimeFormatter();
};

?>