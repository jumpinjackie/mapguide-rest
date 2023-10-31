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

class SelectTest extends ServiceTest {
    protected function set_up() {
        parent::set_up();
        $resp = $this->apiTest("/services/copyresource", "POST", array(
            "session" => $this->anonymousSessionId,
            "source" => "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
            "destination" => "Session:" . $this->anonymousSessionId . "//Parcels.FeatureSource",
            "overwrite" => 1
        ));
        $this->assertStatusCodeIs(200, $resp);
        $resp = $this->apiTest("/services/copyresource", "POST", array(
            "session" => $this->anonymousSessionId,
            "source" => "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
            "destination" => "Session:" . $this->anonymousSessionId . "//Parcels.LayerDefinition",
            "overwrite" => 1
        ));
        $this->assertStatusCodeIs(200, $resp);
        $resp = $this->apiTest("/services/copyresource", "POST", array(
            "session" => $this->anonymousSessionId,
            "source" => "Library://Samples/Sheboygan/Data/VotingDistricts.FeatureSource",
            "destination" => "Session:" . $this->anonymousSessionId . "//VotingDistricts.FeatureSource",
            "overwrite" => 1
        ));
        $this->assertStatusCodeIs(200, $resp);
    }
    protected function tear_down() {
        parent::tear_down();
    }
    private function getSessionFsResourceUrlPart() {
        return "/session/" . $this->anonymousSessionId . "/Parcels.FeatureSource";
    }
    private function getLibraryFsResourceUrlPart() {
        return "/library/Samples/Sheboygan/Data/Parcels.FeatureSource";
    }
    private function getSessionLyrResourceUrlPart() {
        return "/session/" . $this->anonymousSessionId . "/Parcels.LayerDefinition";
    }
    private function getLibraryLyrResourceUrlPart() {
        return "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition";
    }
    private function getSessionVdFsResourceUrlPart() {
        return "/session/" . $this->anonymousSessionId . "/VotingDistricts.FeatureSource";
    }
    private function getLibraryVdFsResourceUrlPart() {
        return "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource";
    }
    private function __testBadRequest($fsResPart) {
        $resp = $this->apiTestWithCredentials("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXmlRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXmlSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testGeoJsonRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testGeoJsonSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testByLayerBadRequest($lyrResPart) {
        $resp = $this->apiTestWithCredentials("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testByLayerXmlRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testByLayerXmlSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testByLayerGeoJsonRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testByLayerGeoJsonSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testSchmittBadRequest($fsResPart) {
        $resp = $this->apiTestWithCredentials("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXmlSchmittRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXmlSchmittSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testGeoJsonSchmittRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testGeoJsonSchmittSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testByLayerSchmittBadRequest($lyrResPart) {
        $resp = $this->apiTestWithCredentials("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testByLayerXmlSchmittRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.xml", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testByLayerXmlSchmittSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testByLayerGeoJsonSchmittRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.geojson", "GET", array("maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testByLayerGeoJsonSchmittSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("session" => $this->adminSessionId, "maxfeatures" => 100, "filter" => "RNAME LIKE 'SCHMITT%'"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testProjectedPropertyListBadRequest($fsResPart) {
        $resp = $this->apiTestWithCredentials("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testProjectedPropertyListXmlRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testProjectedPropertyListXmlSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testProjectedPropertyListGeoJsonRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testProjectedPropertyListGeoJsonSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testProjectedPropertyListByLayerBadRequest($lyrResPart) {
        $resp = $this->apiTestWithCredentials("$lyrResPart/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$lyrResPart/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testProjectedPropertyListByLayerXmlRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testProjectedPropertyListByLayerXmlSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testProjectedPropertyListByLayerGeoJsonRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testProjectedPropertyListByLayerGeoJsonSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("properties" => "Autogenerated_SDF_ID,RNAME,SHPGEOM", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testXformBadRequest($fsResPart) {
        $resp = $this->apiTestWithCredentials("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXformXmlRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXformXmlSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$fsResPart/features.xml/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXformGeoJsonRawCredentials($fsResPart) {
        $resp = $this->apiTestAnon("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXformGeoJsonSessionId($fsResPart) {
        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$fsResPart/features.geojson/SHP_Schema/Parcels", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testXformByLayerBadRequest($lyrResPart) {
        $resp = $this->apiTestWithCredentials("$lyrResPart/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$lyrResPart/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXformByLayerXmlRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXformByLayerXmlSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.xml", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testXformByLayerGeoJsonRawCredentials($lyrResPart) {
        $resp = $this->apiTestAnon("$lyrResPart/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$lyrResPart/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testXformByLayerGeoJsonSessionId($lyrResPart) {
        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$lyrResPart/features.geojson", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "maxfeatures" => 100));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    private function __testBBOXSelectWithXformBadRequest($vdFsResPart) {
        $resp = $this->apiTestWithCredentials("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testBBOXSelectWithXformXmlRawCredentials($vdFsResPart) {
        $resp = $this->apiTestAnon("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testBBOXSelectWithXformXmlSessionId($vdFsResPart) {
        $resp = $this->apiTest("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testBBOXSelectWithXformGeoJsonRawCredentials($vdFsResPart) {
        $resp = $this->apiTestAnon("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testBBOXSelectWithXformGeoJsonSessionId($vdFsResPart) {
        $resp = $this->apiTest("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("transformto" => "WGS84.PseudoMercator", "session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testBBOXSelectWithoutXformBadRequest($vdFsResPart) {
        $resp = $this->apiTestWithCredentials("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testBBOXSelectWithoutXformXmlRawCredentials($vdFsResPart) {
        $resp = $this->apiTestAnon("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testBBOXSelectWithoutXformXmlSessionId($vdFsResPart) {
        $resp = $this->apiTest("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$vdFsResPart/features.xml/Default/VotingDistricts", "GET", array("session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    private function __testBBOXSelectWithoutXformGeoJsonRawCredentials($vdFsResPart) {
        $resp = $this->apiTestAnon("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }
    private function __testBBOXSelectWithoutXformGeoJsonSessionId($vdFsResPart) {
        $resp = $this->apiTest("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("session" => $this->anonymousSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$vdFsResPart/features.geojson/Default/VotingDistricts", "GET", array("session" => $this->adminSessionId, "bbox" => "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
    }

    public function testLibrary_BadRequest() {
        $this->__testBadRequest($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XmlRawCredentials() {
        $this->__testXmlRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XmlSessionId() {
        $this->__testXmlSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_GeoJsonRawCredentials() {
        $this->__testGeoJsonRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_GeoJsonSessionId() {
        $this->__testGeoJsonSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ByLayerBadRequest() {
        $this->__testByLayerBadRequest($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerXmlRawCredentials() {
        $this->__testByLayerXmlRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerXmlSessionId() {
        $this->__testByLayerXmlSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerGeoJsonRawCredentials() {
        $this->__testByLayerGeoJsonRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerGeoJsonSessionId() {
        $this->__testByLayerGeoJsonSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_SchmittBadRequest() {
        $this->__testSchmittBadRequest($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XmlSchmittRawCredentials() {
        $this->__testXmlSchmittRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XmlSchmittSessionId() {
        $this->__testXmlSchmittSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_GeoJsonSchmittRawCredentials() {
        $this->__testGeoJsonSchmittRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_GeoJsonSchmittSessionId() {
        $this->__testGeoJsonSchmittSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ByLayerSchmittBadRequest() {
        $this->__testByLayerSchmittBadRequest($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerXmlSchmittRawCredentials() {
        $this->__testByLayerXmlSchmittRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerXmlSchmittSessionId() {
        $this->__testByLayerXmlSchmittSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerGeoJsonSchmittRawCredentials() {
        $this->__testByLayerGeoJsonSchmittRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ByLayerGeoJsonSchmittSessionId() {
        $this->__testByLayerGeoJsonSchmittSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListBadRequest() {
        $this->__testProjectedPropertyListBadRequest($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListXmlRawCredentials() {
        $this->__testProjectedPropertyListXmlRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListXmlSessionId() {
        $this->__testProjectedPropertyListXmlSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListGeoJsonRawCredentials() {
        $this->__testProjectedPropertyListGeoJsonRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListGeoJsonSessionId() {
        $this->__testProjectedPropertyListGeoJsonSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListByLayerBadRequest() {
        $this->__testProjectedPropertyListByLayerBadRequest($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListByLayerXmlRawCredentials() {
        $this->__testProjectedPropertyListByLayerXmlRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListByLayerXmlSessionId() {
        $this->__testProjectedPropertyListByLayerXmlSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListByLayerGeoJsonRawCredentials() {
        $this->__testProjectedPropertyListByLayerGeoJsonRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_ProjectedPropertyListByLayerGeoJsonSessionId() {
        $this->__testProjectedPropertyListByLayerGeoJsonSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_XformBadRequest() {
        $this->__testXformBadRequest($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XformXmlRawCredentials() {
        $this->__testXformXmlRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XformXmlSessionId() {
        $this->__testXformXmlSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XformGeoJsonRawCredentials() {
        $this->__testXformGeoJsonRawCredentials($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XformGeoJsonSessionId() {
        $this->__testXformGeoJsonSessionId($this->getLibraryFsResourceUrlPart());
    }
    public function testLibrary_XformByLayerBadRequest() {
        $this->__testXformByLayerBadRequest($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_XformByLayerXmlRawCredentials() {
        $this->__testXformByLayerXmlRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_XformByLayerXmlSessionId() {
        $this->__testXformByLayerXmlSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_XformByLayerGeoJsonRawCredentials() {
        $this->__testXformByLayerGeoJsonRawCredentials($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_XformByLayerGeoJsonSessionId() {
        $this->__testXformByLayerGeoJsonSessionId($this->getLibraryLyrResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithXformBadRequest() {
        $this->__testBBOXSelectWithXformBadRequest($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithXformXmlRawCredentials() {
        $this->__testBBOXSelectWithXformXmlRawCredentials($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithXformXmlSessionId() {
        $this->__testBBOXSelectWithXformXmlSessionId($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithXformGeoJsonRawCredentials() {
        $this->__testBBOXSelectWithXformGeoJsonRawCredentials($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithXformGeoJsonSessionId() {
        $this->__testBBOXSelectWithXformGeoJsonSessionId($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithoutXformBadRequest() {
        $this->__testBBOXSelectWithoutXformBadRequest($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithoutXformXmlRawCredentials() {
        $this->__testBBOXSelectWithoutXformXmlRawCredentials($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithoutXformXmlSessionId() {
        $this->__testBBOXSelectWithoutXformXmlSessionId($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithoutXformGeoJsonRawCredentials() {
        $this->__testBBOXSelectWithoutXformGeoJsonRawCredentials($this->getLibraryVdFsResourceUrlPart());
    }
    public function testLibrary_BBOXSelectWithoutXformGeoJsonSessionId() {
        $this->__testBBOXSelectWithoutXformGeoJsonSessionId($this->getLibraryVdFsResourceUrlPart());
    }

    public function testSession_XmlRawCredentials() {
        $this->__testXmlRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XmlSessionId() {
        $this->__testXmlSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_GeoJsonRawCredentials() {
        $this->__testGeoJsonRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_GeoJsonSessionId() {
        $this->__testGeoJsonSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ByLayerXmlRawCredentials() {
        $this->__testByLayerXmlRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerXmlSessionId() {
        $this->__testByLayerXmlSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerGeoJsonRawCredentials() {
        $this->__testByLayerGeoJsonRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerGeoJsonSessionId() {
        $this->__testByLayerGeoJsonSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_XmlSchmittRawCredentials() {
        $this->__testXmlSchmittRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XmlSchmittSessionId() {
        $this->__testXmlSchmittSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_GeoJsonSchmittRawCredentials() {
        $this->__testGeoJsonSchmittRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_GeoJsonSchmittSessionId() {
        $this->__testGeoJsonSchmittSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ByLayerXmlSchmittRawCredentials() {
        $this->__testByLayerXmlSchmittRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerXmlSchmittSessionId() {
        $this->__testByLayerXmlSchmittSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerGeoJsonSchmittRawCredentials() {
        $this->__testByLayerGeoJsonSchmittRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ByLayerGeoJsonSchmittSessionId() {
        $this->__testByLayerGeoJsonSchmittSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListXmlRawCredentials() {
        $this->__testProjectedPropertyListXmlRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListXmlSessionId() {
        $this->__testProjectedPropertyListXmlSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListGeoJsonRawCredentials() {
        $this->__testProjectedPropertyListGeoJsonRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListGeoJsonSessionId() {
        $this->__testProjectedPropertyListGeoJsonSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListByLayerXmlRawCredentials() {
        $this->__testProjectedPropertyListByLayerXmlRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListByLayerXmlSessionId() {
        $this->__testProjectedPropertyListByLayerXmlSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListByLayerGeoJsonRawCredentials() {
        $this->__testProjectedPropertyListByLayerGeoJsonRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_ProjectedPropertyListByLayerGeoJsonSessionId() {
        $this->__testProjectedPropertyListByLayerGeoJsonSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_XformXmlRawCredentials() {
        $this->__testXformXmlRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XformXmlSessionId() {
        $this->__testXformXmlSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XformGeoJsonRawCredentials() {
        $this->__testXformGeoJsonRawCredentials($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XformGeoJsonSessionId() {
        $this->__testXformGeoJsonSessionId($this->getSessionFsResourceUrlPart());
    }
    public function testSession_XformByLayerXmlRawCredentials() {
        $this->__testXformByLayerXmlRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_XformByLayerXmlSessionId() {
        $this->__testXformByLayerXmlSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_XformByLayerGeoJsonRawCredentials() {
        $this->__testXformByLayerGeoJsonRawCredentials($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_XformByLayerGeoJsonSessionId() {
        $this->__testXformByLayerGeoJsonSessionId($this->getSessionLyrResourceUrlPart());
    }
    public function testSession_BBOXSelectWithXformXmlRawCredentials() {
        $this->__testBBOXSelectWithXformXmlRawCredentials($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithXformXmlSessionId() {
        $this->__testBBOXSelectWithXformXmlSessionId($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithXformGeoJsonRawCredentials() {
        $this->__testBBOXSelectWithXformGeoJsonRawCredentials($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithXformGeoJsonSessionId() {
        $this->__testBBOXSelectWithXformGeoJsonSessionId($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithoutXformXmlRawCredentials() {
        $this->__testBBOXSelectWithoutXformXmlRawCredentials($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithoutXformXmlSessionId() {
        $this->__testBBOXSelectWithoutXformXmlSessionId($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithoutXformGeoJsonRawCredentials() {
        $this->__testBBOXSelectWithoutXformGeoJsonRawCredentials($this->getSessionVdFsResourceUrlPart());
    }
    public function testSession_BBOXSelectWithoutXformGeoJsonSessionId() {
        $this->__testBBOXSelectWithoutXformGeoJsonSessionId($this->getSessionVdFsResourceUrlPart());
    }
}