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

class EnumerateResourceReferencesApiTest extends ServiceTest {
    protected function setUp() {
        parent::setUp();
        $resp = $this->apiTest("/services/copyresource", "POST", array(
            "session" => $this->anonymousSessionId,
            "source" => "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
            "destination" => "Session:" . $this->anonymousSessionId . "//Parcels.FeatureSource",
            "overwrite" => 1
        ));
        $this->assertStatusCodeIs(200, $resp);
    }
    protected function tearDown() {
        parent::tearDown();
    }
    private function getSessionResourceUrlPart() {
        return "/session/" . $this->anonymousSessionId . "/Parcels.FeatureSource";
    }
    private function getLibraryResourceUrlPart() {
        return "/library/Samples/Sheboygan/Data/Parcels.FeatureSource";
    }
    private function __testBadRequest($resPart, $bTestUnauth) {
        if ($bTestUnauth) {
            $resp = $this->apiTestWithCredentials("$resPart/references.xml", "GET", null, "Foo", "Bar");
            $this->assertStatusCodeIs(401, $resp);
            $this->assertXmlContent($resp);

            $resp = $this->apiTestWithCredentials("$resPart/references.xml", "GET", array("depth" => -1, "type" => "FeatureSource"), "Foo", "Bar");
            $this->assertStatusCodeIs(401, $resp);
            $this->assertXmlContent($resp);
        }
        $resp = $this->apiTestAnon("$resPart/references.sdjf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("$resPart/references.sdjf", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("$resPart/references.sdjf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("$resPart/references.sdjf", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    private function __testRawCredentials($resPart) {
        //XML
        $resp = $this->apiTestAnon("$resPart/references.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAnon("$resPart/references.xml", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$resPart/references.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("$resPart/references.xml", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        //JSON
        $resp = $this->apiTestAnon("$resPart/references.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAnon("$resPart/references.json", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$resPart/references.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("$resPart/references.json", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        //HTML
        $resp = $this->apiTestAnon("$resPart/references.html", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("$resPart/references.html", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("$resPart/references.html", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("$resPart/references.html", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    private function __testSessionId($resPart) {
        //XML
        $resp = $this->apiTest("$resPart/references.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$resPart/references.xml", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$resPart/references.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("$resPart/references.xml", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        //JSON
        $resp = $this->apiTest("$resPart/references.json", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$resPart/references.json", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$resPart/references.json", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("$resPart/references.json", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        
        //HTML
        $resp = $this->apiTest("$resPart/references.html", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("$resPart/references.html", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("$resPart/references.html", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("$resPart/references.html", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    public function testLibraryBadRequest() {
        $this->__testBadRequest($this->getLibraryResourceUrlPart(), true);
    }
    public function testLibraryRawCredentials() {
        $this->__testRawCredentials($this->getLibraryResourceUrlPart());
    }
    public function testLibrarySessionId() {
        $this->__testSessionId($this->getLibraryResourceUrlPart());
    }
    public function testSessionBadRequest() {
        $this->__testBadRequest($this->getSessionResourceUrlPart(), false);
    }
    public function testSessionRawCredentials() {
        $this->__testRawCredentials($this->getSessionResourceUrlPart());
    }
    public function testSessionSessionId() {
        $this->__testSessionId($this->getSessionResourceUrlPart());
    }
}