<?php

//
//  Copyright (C) 2023 by Jackie Ng
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

// With MGOS 4.0, the constants.php is no longer required as said constants are finally baked into the
// PHP extension itself, but since we are currently supporting multiple MG versions, we need a way to determine
// programmatically if we need to include constants.php, and the simplest way to simply test class_exists() on
// known MG constant classes
if (!class_exists("MgPropertyType") || !class_exists("MgMimeType") || !class_exists("MgServiceType") || !class_exists("MgRepositoryType")) {
    //This is a quick and dirty way to inject the MapGuide Server version. That version number is stamped on
    //resizableadmin.php from the Site Administrator, since we're already pulling in its constants, we can pull
    //this in as well
    include dirname(__FILE__)."/../mapadmin/resizableadmin.php";
    include dirname(__FILE__)."/../mapadmin/constants.php";

    //Shim some constants we know haven't been properly exposed in previous versions of MapGuide
    if (!class_exists("MgImageFormats")) {
        class MgImageFormats
        {
            const Gif = "GIF";
            const Jpeg = "JPG";
            const Png = "PNG";
            const Png8 = "PNG8";
            const Raw = "RAW";
            const Tiff = "TIF";
        }
    }
} else {
    // However not all is rosy so far. If these constant classes already exist, the we don't include constants.php (yay)
    // but that also means we cannot include resizableadmin.php to get version number as that include chain includes
    // constants.php
    //
    // TODO: A proper fix is to provide this version number as part of the MapGuide API and ask for it here
    //
    // Until then, let's just assume if we get here, that we are working with a MGOS 4.0 installation and just hard-code
    // that version here. If this not the case, change the version here.
    define( 'SITE_ADMINISTRATOR_VERSION', "4.0.0.0" );
}

?>