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
    "GeoRest.ConfigPath" => "./conf/data",
    "MapGuide.PhysicalTilePath" => "C:/Program Files/OSGeo/MapGuide/Server/Repositories/TileCache",
    "MapGuide.TileImageFormat" => "png",
    "Cache.RootDir" => "./cache",
    "Locale" => "en",
    //All sizes here in mm
    "PDF.PaperSizes" => array(
        "A3" => array(297.0, 420.0),        // (297x420 mm ; 11.69x16.54 In)
        "A4" => array(210.0, 297.0),        // (210x297 mm ; 8.27x11.69 In)
        "A5" => array(148.0, 210.0),        // (148x210 mm ; 5.83x8.27 in)
        "Letter" => array(216.0, 279.0),    // (216x279 mm ; 8.50x11.00 In)
        "Legal" => array(216.0, 356.0)      // (216x356 mm ; 8.50x14.00 In)
    )
);

?>