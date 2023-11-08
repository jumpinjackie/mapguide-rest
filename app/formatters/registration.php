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
require_once "gmlgeometryoutputformatter.php";
require_once "georssgeometryoutputformatter.php";
require_once "centroidcommaseparatedgeometryoutputformatter.php";
require_once "envelopecommaseparatedgeometryoutputformatter.php";

require_once "defaultdatetimeoutputformatter.php";

//Geometry formatters
$container['GeomWKT'] = function($c) {
    return new MgWktGeometryOutputFormatter();
};
$container['GeomKML'] = function($c) {
    return new MgKmlGeometryOutputFormatter();
};
$container['GeomRSSSimple'] = function($c) {
    return new MgGeoRssSimpleGeometryOutputFormatter();
};
$container['GeomRSSGml'] = function($c) {
    return new MgGeoRssGmlGeometryOutputFormatter();
};
$container['GeomGml'] = function($c) {
    return new MgGmlGeometryOutputFormatter();
};
$container['EnvelopeCommaSeparated'] = function($c) {
    return new MgEnvelopeCommaSeparatedGeometryOutputFormatter();
};
$container['CentroidCommaSeparated'] = function($c) {
    return new MgCentroidCommaSeparatedGeometryOutputFormatter();
};

//Date formatters
$container['DateDefault'] = function($c) {
    return new MgDefaultDateTimeOutputFormatter();
};
$container['DateDMY'] = function($c) {
    return new MgDMYDateTimeFormatter();
};
$container['DateMDY'] = function($c) {
    return new MgMDYDateTimeFormatter();
};
$container['DateISO9601'] = function($c) {
    return new MgISO9601DateTimeFormatter();
};
$container['DateDMYFull'] = function($c) {
    return new MgDMYFullDateTimeFormatter();
};
$container['DateMDYFull'] = function($c) {
    return new MgMDYFullDateTimeFormatter();
};
$container['DateISO9601Full'] = function($c) {
    return new MgISO9601FullDateTimeFormatter();
};
$container['DateAtom'] = function($c) {
    return new MgAtomDateTimeFormatter();
};