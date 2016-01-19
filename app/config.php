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
    //Cors.AccessControlAllowOrigin
    //
    //If specified, will append the specified value as the Access-Control-Allow-Origin response header
    //
    //NOTE: CORS will not be enaled for KML service operations when native=1 is passed 
    "Cors.AccessControlAllowOrigin" => "*",

    //
    //GeoRest.ConfigPath
    //
    //The root path where RESTful data source configuration files are stored
    "GeoRest.ConfigPath" => "./conf/data",

    //
    //MapGuide.FeatureSourceConfiguration
    //
    //If this property is defined, access to the given feature source through the REST API will be governed
    //by the configuration within
    //
    //This property also doubles as a whitelist when defined. When active, APIs that operate on feature sources 
    //will only be allowed if the given feature source id is defined in this list. If this property is omitted 
    //or the property is an empty array, the whitelist is not active
    //
    //Security resolution rules:
    // - If a Feature Source key is present
    //    - If it has an empty array value, all actions and representations are allowed for all
    //    - If it is not empty, action and representation security rules are as follows below
    // - If a Feature Source key is not present, it will fall back to the rules defined in the "Global" node
    // - If an Action is specified
    //    - If it has an empty array value, it is allowed for all.
    //    - If it is not empty, only users/groups/roles within the specified ACL are allowed to invoke this operation
    // - If a Representation is specified
    //    - If it has an empty array value, it is allowed for all.
    //    - If it is not empty, only users/groups/roles within the specified ACL are allowed to request the given resource in the specified representation
    //
    //See example configurations below for more details
    //
    //NOTE: Session-based feature sources are exempt from these whitelisting rules
    /*
    "MapGuide.FeatureSourceConfiguration" => array(
        //
        // The "Globals" configuration specifies ACLs for operations that do not 
        // operate on a specific feature source
        //
        "Globals" => array(
            "Actions" => array(
                "GETCONNECTIONPROPERTYVALUES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "ENUMERATEDATASTORES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETPROVIDERCAPABILITIES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETFEATUREPROVIDERS" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETSCHEMAMAPPING" => array(
                    "AllowRoles" => array("Author", "Administrator")
                )
            )
        ),
        //
        // This Sheboygan Parcels example demonstrates a possible approach to locking down access 
        // to a given feature source
        //
        //  - The following operations are only allowed by authors and administrators:
        //     - Operations that would expose details about the underlying data store
        //     - Operations that insert/update/delete data
        //     - Operations that describe the feature service configuration and capabilities of the MapGuide Server
        //
        //  - Anonymous users can only query data and only access a single class definition 
        //    (and not the ability to walk the entire structure of the data store). An application
        //    will presumably hand down the necessary schema/class name.
        //
        "Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
            "Actions" => array(
                "GETCONNECTIONPROPERTYVALUES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "TESTCONNECTION" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETSPATIALCONTEXTS" => array(),
                "GETLONGTRANSACTIONS" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETSCHEMAS" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "CREATEFEATURESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DESCRIBESCHEMA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETCLASSES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETCLASSDEFINITION" => array(),
                "GETEDITCAPABILITIES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SETEDITCAPABILITIES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "INSERTFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "UPDATEFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DELETEFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SELECTAGGREGATES" => array(),
                "SELECTFEATURES" => array()
            ),
            //
            // For this configuration, all representations are allowed
            //
            "Representations" => array(
                "xml" => array(),
                "json" => array(),
                "geojson" => array(),
                "html" => array(),
                "kml" => array(),
                "czml" => array()
            )
        )
    ),
    */
    //
    //MapGuide.ResourceConfiguration
    //
    //If this property is defined, access to the given resource/folder through the REST API will be governed
    //by the configuration within
    //
    //This property also doubles as a whitelist when defined. When active, APIs that operate on resources/folders 
    //will only be allowed if the given resource id is defined in this list. If this property is omitted 
    //or the property is an empty array, the whitelist is not active.
    //
    //It follows the same security resolution rules as MapGuide.FeatureSourceConfiguration
    //
    //NOTE: Session-based feature sources are exempt from these whitelisting rules
    /*
    "MapGuide.ResourceConfiguration" => array(
        //
        // The "Globals" configuration specifies ACLs for operations that do not 
        // operate on a specific feature source
        //
        "Globals" => array(
            "Actions" => array(
                "ENUMERATERESOURCES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "ENUMERATERESOURCEDATA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "ENUMERATERESOURCEREFERENCES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETRESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETRESOURCEDATA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETRESOURCEHEADER" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SETRESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SETRESOURCEDATA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SETRESOURCEHEADER" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "APPLYRESOURCEPACKAGE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DELETERESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DELETERESOURCEDATA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "COPYRESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "MOVERESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETRESOURCEINFO" => array(
                    "AllowRoles" => array("Author", "Administrator")
                )
            )
        ),
        //
        // This Sheboygan Parcels example demonstrates a possible approach to locking down access 
        // to a given feature source
        //
        //  - The following operations are only allowed by authors and administrators:
        //     - Operations that would expose details about the underlying data store
        //     - Operations that insert/update/delete data
        //     - Operations that describe the feature service configuration and capabilities of the MapGuide Server
        //
        //  - Anonymous users can only query data and only access a single class definition 
        //    (and not the ability to walk the entire structure of the data store). An application
        //    will presumably hand down the necessary schema/class name.
        //
        "Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
            "Actions" => array(
                "GETCONNECTIONPROPERTYVALUES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "TESTCONNECTION" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETSPATIALCONTEXTS" => array(),
                "GETLONGTRANSACTIONS" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETSCHEMAS" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "CREATEFEATURESOURCE" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DESCRIBESCHEMA" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETCLASSES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "GETCLASSDEFINITION" => array(),
                "GETEDITCAPABILITIES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SETEDITCAPABILITIES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "INSERTFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "UPDATEFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "DELETEFEATURES" => array(
                    "AllowRoles" => array("Author", "Administrator")
                ),
                "SELECTAGGREGATES" => array(),
                "SELECTFEATURES" => array()
            ),
            //
            // For this configuration, all representations are allowed
            //
            "Representations" => array(
                "xml" => array(),
                "json" => array(),
                "geojson" => array(),
                "html" => array(),
                "kml" => array(),
                "czml" => array()
            )
        )
    ),
    */
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
    //MapGuide.XYZTileBuffer
    //
    //On installations of MapGuide before 3.0, XYZ tile support is done through the RenderMap API. Unfortunately, this
    //produces an undesirable cropping effect on labels and symbology if they lie on the boundaries of the rendered tile
    //
    //To workaround that, you can specify a buffer value here that will render the tile as a (256 * n) x (256 * n) image
    //where n = 2 x [this value]
    //
    //The buffered tile is then cropped back to the original size
    //
    "MapGuide.XYZTileBuffer" => 90,
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
    //Cache.XYZTileRoot
    //
    //If specfied, defines the absolute root path of where all cached XYZ tiles produced by mapguide-rest will reside. You must
    //ensure that the web server has appropriate permissions to write files and create directories in this directory.
    //
    //If specified, will override the value of Cache.RootDir for the purpose of tile storage. Other cache-able items will still
    //be stored in the path defined by Cache.RootDir
    //
    //"Cache.XYZTileRoot" => "C:/tilecache",
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