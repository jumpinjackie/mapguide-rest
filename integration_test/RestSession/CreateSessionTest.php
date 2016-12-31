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

require_once dirname(__FILE__)."/../IntegrationTest.php";
require_once dirname(__FILE__)."/../Config.php";

class CreateSessionTest extends IntegrationTest {
    public function testRemovedImplicitRoutes() {
        $resp = $this->apiTest("/session", "GET", array());
        $this->assertStatusCodeIs(404, $resp);
        $resp = $this->apiTest("/session", "PUT", array());
        $this->assertStatusCodeIs(404, $resp);
        $resp = $this->apiTest("/session", "POST", array());
        $this->assertStatusCodeIs(404, $resp);
        $resp = $this->apiTest("/session", "DELETE", array());
        $this->assertStatusCodeIs(404, $resp);
    }
    public function testCreateAnon() {
        $resp = $this->apiTestAnon("/session.json", "POST", array());
        $this->assertStatusCodeIs(201, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAnon("/session.xml", "POST", array());
        $this->assertStatusCodeIs(201, $resp);
        $this->assertXmlContent($resp);
    }
    public function testCreateAdmin() {
        $resp = $this->apiTestAdmin("/session.json", "POST", array());
        $this->assertStatusCodeIs(201, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/session.xml", "POST", array());
        $this->assertStatusCodeIs(201, $resp);
        $this->assertXmlContent($resp);
    }
    public function testCredentialsViaForm() {
        $resp = $this->apiTest("/session.json", "POST", array("username" => "Anonymous"));
        $this->assertStatusCodeIs(201, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/session.json", "POST", array("username" => "Administrator", "password" => "admin"));
        $this->assertStatusCodeIs(201, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/session.xml", "POST", array("username" => "Anonymous"));
        $this->assertStatusCodeIs(201, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/session.xml", "POST", array("username" => "Administrator", "password" => "admin"));
        $this->assertStatusCodeIs(201, $resp);
        $this->assertXmlContent($resp);
    }
    public function testUnauthorized() {
        $resp = $this->apiTest("/session.json", "POST", array());
        $this->assertStatusCodeIs(401, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/session.xml", "POST", array());
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);
    }
}