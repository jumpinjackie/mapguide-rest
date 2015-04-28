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

return array(
    //
    //debug
    //
    //Enable/disable debugging facilities in the slim framework
    "debug" => false,

    //
    //Error.OutputStackTrace
    //
    //If false, stack trace information will not be outputted in error responses
    "Error.OutputStackTrace" => false,

    //
    //MapGuide.MapAgentUrl
    //
    //The mapagent endpoint relative to the root of mapguide-rest
    "MapGuide.MapAgentUrl" => "../mapagent/mapagent.fcgi",

    //
    //GeoRest.ConfigPath
    //
    //The root path where RESTful data source configuration files are stored
    "GeoRest.ConfigPath" => "./conf/data",

    //
    //MapGuide.PhysicalTilePath
    //
    //The root path where MapGuide-generated tiles are stored. This is to allow for mapguide-rest to
    //take advantage of 304 http caching of generated tiles. This only works when mapguide-rest and the
    //MapGuide Server reside on the same physical host.
    //
    //If this path is invalid or un-reachable, no caching headers will be applied to generated tiles as
    //mapguide-rest will not be able to access the physical tiles to read the timestamps
    "MapGuide.PhysicalTilePath" => "C:/Program Files/OSGeo/MapGuide/Server/Repositories/TileCache",
    //
    //MapGuide.TileImageFormat
    //
    //The image format of generated tiles. This must match the same image format as defined in serverconfig.ini
    //
    //This is used in combination with the above property to determine the physical location of generated tiles
    //in order to determine and/or apply 304 http caching
    "MapGuide.TileImageFormat" => "png",
    //
    //Cache.RootDir
    //
    //The root path of where all cache-able items produced by mapguide-rest will reside. You must ensure that the
    //web server has appropriate permissions to write files and create directories in this directory.
    //
    //Examples of cache-able items include:
    // - Compiled smarty templates
    // - XYZ image tiles
    // - XYZ vector tiles
    "Cache.RootDir" => "./cache",
    //
    //Locale
    //
    //The locale to use for error messages returned by mapguide-rest. Exceptions thrown by MapGuide will still use the locale configured in serverconfig.ini
    //and webconfig.ini
    //
    //Also the configured locale will determine the paths of the following:
    //
    // String text bundle: res/lang/<locale>.php
    // XSL stylesheets: res/xsl/<locale>
    // Smarty templates: res/templates/<locale>
    "Locale" => "en",
    //
    //PDF.PaperSizes
    //
    //An associative array of valid PDF paper sizes. All sizes here in mm
    "PDF.PaperSizes" => array(
        "A3" => array(297.0, 420.0),        // (297x420 mm ; 11.69x16.54 In)
        "A4" => array(210.0, 297.0),        // (210x297 mm ; 8.27x11.69 In)
        "A5" => array(148.0, 210.0),        // (148x210 mm ; 5.83x8.27 in)
        "Letter" => array(216.0, 279.0),    // (216x279 mm ; 8.50x11.00 In)
        "Legal" => array(216.0, 356.0)      // (216x356 mm ; 8.50x14.00 In)
    )
);

?>