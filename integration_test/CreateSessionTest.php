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

require_once dirname(__FILE__)."/IntegrationTest.php";

class CreateSessionTest extends IntegrationTest {
    public function testCreateAnon() {
        $resp = $this->apiTestAnon("/session.json", "POST", array());
        $this->assertEquals(201, $resp->getStatusCode());
    }
    public function testCreateAdmin() {
        $resp = $this->apiTestAdmin("/session.json", "POST", array());
        $this->assertEquals(201, $resp->getStatusCode());
    }
    public function testCredentialsViaForm() {
        $resp = $this->apiTest("/session.json", "POST", array("username" => "Anonymous"));
        $this->assertEquals(201, $resp->getStatusCode());

        $resp = $this->apiTest("/session.json", "POST", array("username" => "Administrator", "password" => "admin"));
        $this->assertEquals(201, $resp->getStatusCode());
    }
    public function testUnauthorized() {
        $resp = $this->apiTest("/session.json", "POST", array());
        $this->assertEquals(401, $resp->getStatusCode());
    }
}

?>