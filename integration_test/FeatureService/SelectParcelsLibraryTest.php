<?php

//
//  Copyright (C) 2016 by Jackie Ng
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

require_once dirname(__FILE__)."/../Config.php";
require_once dirname(__FILE__)."/../ServiceTest.php";

class SelectParcelsLibraryTest extends ServiceTest {
    public function testBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testByLayerBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testByLayerXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testByLayerXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testByLayerGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testByLayerGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testSchmittBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXmlSchmittRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXmlSchmittSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testGeoJsonSchmittRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testGeoJsonSchmittSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testByLayerSchmittBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testByLayerXmlSchmittRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testByLayerXmlSchmittSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testByLayerGeoJsonSchmittRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testByLayerGeoJsonSchmittSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testProjectedPropertyListBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testProjectedPropertyListXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testProjectedPropertyListXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testProjectedPropertyListGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testProjectedPropertyListGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testProjectedPropertyListByLayerBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testProjectedPropertyListByLayerXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testProjectedPropertyListByLayerXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testProjectedPropertyListByLayerGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testProjectedPropertyListByLayerGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testXformBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXformXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXformXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXformGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXformGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testXformByLayerBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXformByLayerXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXformByLayerXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testXformByLayerGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testXformByLayerGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testBBOXSelectWithXformBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testBBOXSelectWithXformXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testBBOXSelectWithXformXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testBBOXSelectWithXformGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testBBOXSelectWithXformGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testBBOXSelectWithoutXformBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    public function testBBOXSelectWithoutXformXmlRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testBBOXSelectWithoutXformXmlSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.xml/Default/VotingDistricts", "GET", array("session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testBBOXSelectWithoutXformGeoJsonRawCredentials() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    public function testBBOXSelectWithoutXformGeoJsonSessionId() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", array("session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
}