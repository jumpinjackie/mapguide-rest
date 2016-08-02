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

class EnumerateResourceDataTest extends ServiceTest {
    public function testCommon() {
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "POST", null);
        $this->assertStatusCodeIs(404, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "PUT", null);
        $this->assertStatusCodeIs(404, $resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    private function __testBase($extension, $mimeType) {
        //Bad credentials
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", null, "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("depth" => "-1", "type" => "LayerDefinition"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //Raw credentials
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //Session ID
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("session" => $this->anonymousSessionId, "depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.$extension", "GET", array("session" => $this->adminSessionId, "depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
    }
    public function testXml() {
        $this->__testBase("xml", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testBase("json", Configuration::MIME_JSON);
    }
    public function testHtml() {
        $this->__testBase("html", Configuration::MIME_HTML);
    }
}

?>