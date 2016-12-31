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

class EnumerateResourcesApiTest extends ServiceTest {
    private function __testBase($extension, $mimeType) {
        //Bad credentials
        $resp = $this->apiTestWithCredentials("/library/Samples/list.$extension", "GET", null, "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/list.$extension", "GET", array("depth" => "-1", "type" => "FeatureSource"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //With raw credentials
        $resp = $this->apiTestAnon("/library/Samples/list.$extension", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestAnon("/library/Samples/list.$extension", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/list.$extension", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/list.$extension", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //With session id
        $resp = $this->apiTest("/library/Samples/list.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/list.$extension", "GET", array("session" => $this->anonymousSessionId, "depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/list.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/list.$extension", "GET", array("session" => $this->adminSessionId, "depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);
    }
    public function testCommon() {
        $resp = $this->apiTest("/library/Samples/", "GET", null);
        $this->assertStatusCodeIs(404, $resp);

        $resp = $this->apiTest("/library/Samples/list", "POST", array());
        $this->assertStatusCodeIs(404, $resp);

        $resp = $this->apiTest("/library/Samples/list", "POST", array("depth" => "-1", "type" => "FeatureSource"));
        $this->assertStatusCodeIs(404, $resp);

        //Bad representations
        $resp = $this->apiTestAnon("/library/Samples/list.foo", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("/library/Samples/list.foo", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/list.foo", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/list.foo", "GET", array("depth" => "-1", "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

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