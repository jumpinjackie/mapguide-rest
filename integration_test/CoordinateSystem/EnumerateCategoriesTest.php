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

class EnumerateCategoriesTest extends ServiceTest {
    public function __testOperation($extension, $mimeType) {
        
        //With raw credentials
        $resp = $this->apiTestAnon("/coordsys/categories.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTestAdmin("/coordsys/categories.$extension", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        //With session id
        $resp = $this->apiTest("/coordsys/categories.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);

        $resp = $this->apiTest("/coordsys/categories.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $extension);
    }
    public function testBadRequests() {
        $resp = $this->apiTestAnon("/coordsys/categories.sadgdsfd", "GET", array());
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/coordsys/categories.sadgdsfd", "GET", array());
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $extensions = array(
            array("extension" => "xml", "mime_type" => Configuration::MIME_XML),
            array("extension" => "json", "mime_type" => Configuration::MIME_JSON)
        );
        foreach ($extensions as $ext) {
            $extension = $ext["extension"];
            $mimeType = $ext["mime_type"];

            $resp = $this->apiTest("/coordsys/categories.$extension", "GET", array());
            $this->assertStatusCodeIs(401, $resp);
            $this->assertMimeType($mimeType, $resp);
            $this->assertContentKind($resp, $extension);
        }
    }
    public function testXml() {
        $this->__testOperation("xml", Configuration::MIME_XML);
    }
    public function testHtml() {
        $this->__testOperation("html", Configuration::MIME_HTML);
    }
    public function testJson() {
        $this->__testOperation("json", Configuration::MIME_JSON);
    }
}

?>