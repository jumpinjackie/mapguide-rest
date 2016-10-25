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

class GetMapKmlTest extends ServiceTest {
    private function isCorsTesting() { return false; }

    public function testLibrary() {
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_KML, $resp);
        $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_KML, $resp);
        $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

        $resp = $this->apiTest("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_KML, $resp);
        $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") === FALSE, "Expected no mapagent callback urls in response");

        if (!$this->isCorsTesting()) {
            //Pass thru
            $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array("native" => 1));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");

            $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array("native" => 1));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");

            $resp = $this->apiTest("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", array("native" => 1));
            $this->assertStatusCodeIs(200, $resp);
            $this->assertMimeType(Configuration::MIME_KML, $resp);
            $this->assertTrue(strpos($resp->getContent(), "mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
        }
    }
}

?>