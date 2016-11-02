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

class TransformCoordsTest extends ServiceTest {
    public function __testOperation($extension, $mimeType) {
        $params = array("format" => $extension, "from" => "LL84", "to" => "WGS84.PseudoMercator", "coords" => "-87.1 43.2,-87.2 43.3,-87.4 43.1");
        $paramsWithPadding = array("format" => $extension, "from" => "LL84", "to" => "WGS84.PseudoMercator", "coords" => "-87.1 43.2, -87.2 43.3, -87.4 43.1");
        $paramsBadCoords = array("format" => $extension, "from" => "LL84", "to" => "WGS84.PseudoMercator", "coords" => "-87.1 43.2,-87.2,43.3,-87.4 43.1");
        $paramsBogusCs = array("format" => $extension, "from" => "LL84", "to" => "Foobar", "coords" => "-87.1 43.2,-87.2 43.3,-87.4 43.1");
        $paramsIncomplete1 = array("format" => $extension, "coords" => "-87.1 43.2,-87.2 43.3,-87.4 43.1");
        $paramsIncomplete2 = array("format" => $extension, "from" => "LL84", "coords" => "-87.1 43.2,-87.2 43.3,-87.4 43.1");

        $anonU = Configuration::getAnonLogin();
        $adminU = Configuration::getAdminLogin();

        $badParamSets = array(
            array("params" => $paramsBogusCs, "expected_status" => 500),
            array("params" => $paramsIncomplete1, "expected_status" => 400),
            array("params" => $paramsIncomplete2, "expected_status" => 400),
            array("params" => $paramsBadCoords, "expected_status" => 500)
        );

        $goodParamSets = array(
            $params,
            $paramsWithPadding
        );

        foreach ($badParamSets as $pair) {
            $parameters = $pair["params"];
            $expectedStatus = $pair["expected_status"];

            $resp = $this->apiTestWithCredentials("/services/transformcoords", "POST", $parameters, $anonU->user, $anonU->pass);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestWithCredentials("/services/transformcoords", "POST", $parameters, $adminU->user, $adminU->pass);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestAnon("/services/transformcoords", "POST", $parameters);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestAdmin("/services/transformcoords", "POST", $parameters);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);
        }

        foreach ($goodParamSets as $parameters) {
            $expectedStatus = 200;

            $resp = $this->apiTestWithCredentials("/services/transformcoords", "POST", $parameters, $anonU->user, $anonU->pass);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestWithCredentials("/services/transformcoords", "POST", $parameters, $adminU->user, $adminU->pass);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestAnon("/services/transformcoords", "POST", $parameters);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);

            $resp = $this->apiTestAdmin("/services/transformcoords", "POST", $parameters);
            $this->assertStatusCodeIs($expectedStatus, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);
        }
    }
    public function testXml() {
        $this->__testOperation("xml", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testOperation("json", Configuration::MIME_JSON);
    }
}

?>