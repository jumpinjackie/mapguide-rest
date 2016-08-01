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
        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertNotEquals(401, $resp->getStatusCode());
        $this->user1SessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
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
    private function createInsertXml($text, $geom, $session) {
        $xml = "<FeatureSet>";
        if ($session != null && $session != "") {
            $xml .= "<SessionID>" . $session . "</SessionID>";
        }
        $xml .= "<Features><Feature>";
        $xml .= "<Property><Name>RNAME</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>SHPGEOM</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</Feature></Features></FeatureSet>";
        return xml;
    }
    private function createUpdateXml($filter, $text, $geom, $session) {
        $xml = "<UpdateOperation>";
        if ($session != null && $session != "") {
            $xml .= "<SessionID>" . $session . "</SessionID>";
        }
        if ($filter != null && $filter != "") {
            $xml .= "<Filter>" . $filter . "</Filter>";
        }
        $xml .= "<UpdateProperties>";
        $xml .= "<Property><Name>RNAME</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>SHPGEOM</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</UpdateProperties>";
        $xml .= "</UpdateOperation>";
        return xml;
    }
    private function createInsertJson($text, $geom, $session) {
        $sessionPart = "";
        if (typeof(session) != 'undefined' && session != null && session != "") {
            $sessionPart = "\"SessionID\": $session,\n";
        }
        $json = "{
            \"FeatureSet\": {
                $sessionPart
                \"Features\": {
                    \"Feature\": [
                        { 
                            \"Property\": [
                                { \"Name\": \"RNAME\", \"Value\": $text },
                                { \"Name\": \"SHPGEOM\", \"Value\": $geom }
                            ] 
                        }
                    ]
                }
            }
        }";
        return $json;
    }

    private function createUpdateJson($filter, $text, $geom, $session) {
        $sessionPart = "";
        if ($session != null && $session != "") {
            $sessionPart = "\"SessionID\": $session,\n";
        }
        $filterPart = "";
        if ($filter != null && $filter != "") {
            $filterPart = "\"Filter\": $filter,\n";
        }
        $json = "{
            \"UpdateOperation\": {
                $sessionPart
                $filterPart
                \"UpdateProperties\": {
                    \"Property\": [
                        { \"Name\": \"RNAME\", \"Value\": $text },
                        { \"Name\": \"SHPGEOM\", \"Value\": $geom }
                    ] 
                }
            }
        }";
        return $json;
    }
    
    public function testACLAnonymous() {
        $testID1 = 42;
        $testID2 = 43;
        $testID3 = 1234;

        //With credentials
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //With session ids
        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "GET", array("session" => $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Single access - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/" . $testID3. ".xml", "GET", array(), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Single access - Session ID
        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/" . $testID3. ".xml", "GET", array("session" => $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Insert - Credentials
        //Insert - Session ID
        //Update - Credentials
        //Update - Session ID
        //Update - Single Access - Credentials
        //Update - Single Access - Session ID
        //Delete - Credentials
        //Delete - Session ID
    }
}

?>