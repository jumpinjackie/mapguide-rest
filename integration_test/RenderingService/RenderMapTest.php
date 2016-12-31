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

class RenderMapTest extends ServiceTest {
    private function __testBase($mimeType, $extension) {
        //Various missing parameters
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array());
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);
        
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array());
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000));
        $this->assertStatusCodeIs(400, $resp);

        //Valid forms
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000, "width" => 320, "height" => 200));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000, "width" => 320, "height" => 200));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000, "width" => 320, "height" => 200, "session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.$extension", "GET", array("x" => -87.73, "y" => 43.74, "scale" => 8000, "width" => 320, "height" => 200, "session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
    }
    public function testPNG() {
        $this->__testBase(Configuration::MIME_PNG, "png");
    }
    public function testPNG8() {
        $this->__testBase(Configuration::MIME_PNG, "png8");
    }
    public function testJPG() {
        $this->__testBase(Configuration::MIME_JPEG, "jpg");
    }
    public function testGIF() {
        $this->__testBase(Configuration::MIME_GIF, "gif");
    }
}