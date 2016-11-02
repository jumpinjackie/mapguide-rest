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

class EpsgForLL84Test extends ServiceTest {
    private function assertValidEpsgResponse($resp, $extension, $expected) {
        if ($extension == "json") {
            $json = json_decode($resp->getContent());
            $this->assertNotNull($json, $resp->dump());
            $this->assertEquals($expected, $json->PrimitiveValue->Value, "Expected EPSG of: $expected");
        } else if ($extension == "xml") {
            $this->assertTrue(strpos($resp->getContent(), "$expected") >= 0);
        }
    }

    public function __testOperation($extension, $mimeType) {
        $resp = $this->apiTest("/coordsys/mentor/LL84/epsg.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidEpsgResponse($resp, $extension, 4326);
        
        //With raw credentials
        $resp = $this->apiTestAnon("/coordsys/mentor/LL84/epsg.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidEpsgResponse($resp, $extension, 4326);

        $resp = $this->apiTestAdmin("/coordsys/mentor/LL84/epsg.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidEpsgResponse($resp, $extension, 4326);

        //With session id
        $resp = $this->apiTest("/coordsys/mentor/LL84/epsg.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidEpsgResponse($resp, $extension, 4326);

        $resp = $this->apiTest("/coordsys/mentor/LL84/epsg.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidEpsgResponse($resp, $extension, 4326);
    }
    public function testXml() {
        $this->__testOperation("xml", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testOperation("json", Configuration::MIME_JSON);
    }
}

?>