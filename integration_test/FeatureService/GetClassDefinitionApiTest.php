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

class GetClassDefinitionApiTest extends ServiceTest {
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
    private function __testCommon($resPart) {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.sdigud/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.sdigud/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        //HTML not supported
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        // ================ Alternate Route ================== //
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.sdigud/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.sdigud/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        //HTML not supported
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    private function __testBase($resPart, $extension, $mimeType, $bTestUnauth) {
        if ($bTestUnauth) {
            $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema/Parcels", "GET", null);
            $this->assertStatusCodeIs(401, $resp);
            $this->assertMimeType($mimeType, $resp);
        }
        //Raw credentials
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema/Parcels", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //Session ID
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema/Parcels", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema/Parcels", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        // ================ Alternate Route ================== //
        if ($bTestUnauth) {
            $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema:Parcels", "GET", null);
            $this->assertStatusCodeIs(401, $resp);
            $this->assertMimeType($mimeType, $resp);
        }

        //Raw credentials
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema:Parcels", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //Session ID
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema:Parcels", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.$extension/SHP_Schema:Parcels", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
    }
    public function testLibraryCommon() {
        $this->__testCommon($this->getLibraryResourceUrlPart());
    }
    public function testLibraryXml() {
        $this->__testBase($this->getLibraryResourceUrlPart(), "xml", Configuration::MIME_XML, true);
    }
    public function testLibraryJson() {
        $this->__testBase($this->getLibraryResourceUrlPart(), "json", Configuration::MIME_JSON, true);
    }
    //public function testLibraryHtml() {
    //    $this->__testBase($this->getLibraryResourceUrlPart(), "html", Configuration::MIME_HTML);
    //}
    public function testSessionCommon() {
        $this->__testCommon($this->getSessionResourceUrlPart());
    }
    public function testSessionXml() {
        $this->__testBase($this->getSessionResourceUrlPart(), "xml", Configuration::MIME_XML, false);
    }
    public function testSessionJson() {
        $this->__testBase($this->getSessionResourceUrlPart(), "json", Configuration::MIME_JSON, false);
    }
    //public function testSessionHtml() {
    //    $this->__testBase($this->getSessionResourceUrlPart(), "html", Configuration::MIME_HTML);
    //}
}