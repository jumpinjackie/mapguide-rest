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

class AggregatesApiTest extends ServiceTest {
    private function __testAggregate($extension, $mimeType, $op, $extraArgs = array()) {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.$extension/$op/SHP_Schema/Parcels", "GET", $extraArgs, "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);

        //Raw credentials
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.$extension/$op/SHP_Schema/Parcels", "GET", $extraArgs);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.$extension/$op/SHP_Schema/Parcels", "GET", $extraArgs);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        //Session ID
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.$extension/$op/SHP_Schema/Parcels", "GET", (array("session" => $this->anonymousSessionId) + $extraArgs));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.$extension/$op/SHP_Schema/Parcels", "GET", (array("session" => $this->adminSessionId) + $extraArgs));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
    }
    public function testCountLibrary() {
        $this->__testAggregate("xml", Configuration::MIME_XML, "count");
        $this->__testAggregate("json", Configuration::MIME_JSON, "count");
    }
    public function testBBOXLibrary() {
        $this->__testAggregate("xml", Configuration::MIME_XML, "bbox");
        $this->__testAggregate("json", Configuration::MIME_JSON, "bbox");
    }
    public function testBBOXWithXformLibrary() {
        $this->__testAggregate("xml", Configuration::MIME_XML, "bbox", array("transform" => "WGS84.PseudoMercator"));
        $this->__testAggregate("json", Configuration::MIME_JSON, "bbox", array("transform" => "WGS84.PseudoMercator"));
    }
    public function testDistinctLibrary() {
        $this->__testAggregate("xml", Configuration::MIME_XML, "distinctvalues", array("property" => "RTYPE"));
        $this->__testAggregate("json", Configuration::MIME_JSON, "distinctvalues", array("property" => "RTYPE"));
    }
}