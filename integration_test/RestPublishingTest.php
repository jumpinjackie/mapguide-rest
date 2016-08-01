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
require_once dirname(__FILE__)."/Config.php";

class RestPublishingTests extends IntegrationTest {
    private $anonymousSessionId;
    private $wfsSessionId;
    private $wmsSessionId;
    private $authorSessionId;
    private $adminSessionId;
    private $user1SessionId;
    private $user2SessionId;

    protected function setUp() {
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), "Anonymous", "");
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->anonymousSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->adminSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->wfsSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->wmsSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->authorSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), "User1", "user1");
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->user1SessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), "User2", "user2");
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->user2SessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
    }
    protected function tearDown() {
        $resp = $this->apiTest("/session/".$this->anonymousSessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->anonymousSessionId = null;
        $resp = $this->apiTest("/session/".$this->adminSessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->adminSessionId = null;
        $resp = $this->apiTest("/session/".$this->wfsSessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->wfsSessionId = null;
        $resp = $this->apiTest("/session/".$this->wmsSessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->wmsSessionId = null;
        $resp = $this->apiTest("/session/".$this->authorSessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->authorSessionId = null;
        $resp = $this->apiTest("/session/".$this->user1SessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->user1SessionId = null;
        $resp = $this->apiTest("/session/".$this->user2SessionId, "DELETE", null);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->user2SessionId = null;
    }
    public function testHasIds() {
        $this->assertNotNull($this->anonymousSessionId);
        $this->assertNotNull($this->wfsSessionId);
        $this->assertNotNull($this->wmsSessionId);
        $this->assertNotNull($this->authorSessionId);
        $this->assertNotNull($this->adminSessionId);
        $this->assertNotNull($this->user1SessionId);
        $this->assertNotNull($this->user2SessionId);
    }
}

?>