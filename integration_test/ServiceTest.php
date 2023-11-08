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

require_once dirname(__FILE__)."/Config.php";
require_once dirname(__FILE__)."/IntegrationTest.php";

/**
 * This is the base class of all our integration tests. Provides common setup for
 * anon/admin session ids
 */
abstract class ServiceTest extends IntegrationTest {
    protected $anonymousSessionId;
    protected $adminSessionId;

    protected function set_up() {
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), "Anonymous", "");
        $this->assertStatusCodeIsNot(401, $resp);
        $this->anonymousSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->adminSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
    }
    protected function tear_down() {
        $resp = $this->apiTest("/session/".$this->anonymousSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->anonymousSessionId = null;
        $resp = $this->apiTest("/session/".$this->adminSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->adminSessionId = null;
    }
}