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

class GetFeaturesKmlTest extends ServiceTest {
    protected function set_up() {
        parent::set_up();
        $resp = $this->apiTest("/services/copyresource", "POST", array(
            "session" => $this->anonymousSessionId,
            "source" => "Library://Samples/Sheboygan/Layers/Districts.LayerDefinition",
            "destination" => "Session:" . $this->anonymousSessionId . "//Districts.LayerDefinition",
            "overwrite" => 1
        ));
        $this->assertStatusCodeIs(200, $resp);
    }
    protected function tear_down() {
        parent::tear_down();
    }
    private function getSessionLayerPart() {
        return "/session/" . $this->anonymousSessionId . "/Districts.LayerDefinition";
    }
    private function getLibraryLayerPart() {
        return "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition";
    }

    private function isCorsTesting() { return false; }

    private function __testBase($lyrPart) {
        if (!$this->isCorsTesting()) {
            $resp = $this->apiTestAnon("$lyrPart/kmlfeatures.kml", "GET", array());
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

            $resp = $this->apiTestAdmin("$lyrPart/kmlfeatures.kml", "GET", array());
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

            $resp = $this->apiTest("$lyrPart/kmlfeatures.kml", "GET", array());
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

            $resp = $this->apiTestAnon("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAdmin("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTest("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAnon("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAdmin("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTest("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAnon("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAdmin("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTest("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1));
            $this->assertStatusCodeIs(400, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);

            $resp = $this->apiTestAnon("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1, "bbox" => "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569"));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

            $resp = $this->apiTestAdmin("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1, "bbox" => "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569"));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

            $resp = $this->apiTest("$lyrPart/kmlfeatures.kml", "GET", array("width" => 640, "height" => 480, "draworder" => 1, "bbox" => "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569"));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");
        }
    }
    public function testLibrary() {
        $this->__testBase($this->getLibraryLayerPart());
    }
    public function testSession() {
        $this->__testBase($this->getSessionLayerPart());
    }
}