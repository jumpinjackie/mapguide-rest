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

class ListUsersTest extends ServiceTest {
    public function testBadRequest() {
        $resp = $this->apiTest("/site/groups/Everyone/users.xml", "GET", null);
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);
    }
    public function testRawCredentials() {
        $resp = $this->apiTestAnon("/site/groups/Everyone/users.xml", "GET", null);
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/site/groups/Everyone/users.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
    public function testSessionId() {
        $resp = $this->apiTest("/site/groups/Everyone/users.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/site/groups/Everyone/users.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
    }
}