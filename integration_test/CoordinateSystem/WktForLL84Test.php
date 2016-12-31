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

require_once dirname(__FILE__)."/../ServiceTest.php";
require_once dirname(__FILE__)."/../Config.php";

class WktForLL84Test extends ServiceTest {
    private function assertValidWktResponse($resp, $extension, $expected) {
        if ($extension == "json") {
            $json = json_decode($resp->getContent());
            $this->assertNotNull($json, $resp->dump());
            $this->assertEquals($expected, $json->PrimitiveValue->Value, "Expected EPSG of: $expected");
        } else if ($extension == "xml") {
            $this->assertTrue(strpos($resp->getContent(), "$expected") >= 0);
        }
    }

    public function __testOperation($extension, $mimeType) {
        $wkt = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";

        $resp = $this->apiTest("/coordsys/mentor/LL84/wkt.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidWktResponse($resp, $extension, $wkt);

        //With raw credentials
        $resp = $this->apiTestAnon("/coordsys/mentor/LL84/wkt.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidWktResponse($resp, $extension, $wkt);

        $resp = $this->apiTestAdmin("/coordsys/mentor/LL84/wkt.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidWktResponse($resp, $extension, $wkt);

        //With session id
        $resp = $this->apiTest("/coordsys/mentor/LL84/wkt.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidWktResponse($resp, $extension, $wkt);

        $resp = $this->apiTest("/coordsys/mentor/LL84/wkt.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidWktResponse($resp, $extension, $wkt);
    }
    public function testXml() {
        $this->__testOperation("xml", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testOperation("json", Configuration::MIME_JSON);
    }
}