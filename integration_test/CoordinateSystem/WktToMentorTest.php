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

class WktToMentorTest extends ServiceTest {
    private function assertValidMentorResponse($resp, $extension, $expected) {
        if ($extension == "json") {
            $json = json_decode($resp->getContent());
            $this->assertNotNull($json, $resp->dump());
            $this->assertEquals($expected, $json->PrimitiveValue->Value, "Expected: $expected");
        }
    }

    public function __testOperation($extension, $mimeType) {
        $goodWkt = 'GEOGCS["LL84",DATUM["WGS84",SPHEROID["WGS84",6378137.000,298.25722293]],PRIMEM["Greenwich",0],UNIT["Degree",0.01745329251994]]';
        $badWkt = 'This is not a valid coordinate system wkt';

        // ------------------ Good WKT --------------------- //

        //With raw credentials
        $resp = $this->apiTestAnon("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $goodWkt));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidMentorResponse($resp, $extension, "LL84");

        $resp = $this->apiTestAdmin("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $goodWkt));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidMentorResponse($resp, $extension, "LL84");

        //With session id
        $resp = $this->apiTest("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $goodWkt, "session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidMentorResponse($resp, $extension, "LL84");

        $resp = $this->apiTest("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $goodWkt, "session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertValidMentorResponse($resp, $extension, "LL84");

        // ------------------ Bad WKT --------------------- //

        //With raw credentials
        $resp = $this->apiTestAnon("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $badWkt));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $badWkt));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //With session id
        $resp = $this->apiTest("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $badWkt, "session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/coordsys/wkttomentor.$extension", "POST", array("wkt" => $badWkt, "session" => $this->adminSessionId));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
    }
    public function testXml() {
        $this->__testOperation("xml", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testOperation("json", Configuration::MIME_JSON);
    }
}

?>