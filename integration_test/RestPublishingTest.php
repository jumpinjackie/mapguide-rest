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
    private function createInsertXml($text, $geom, $session = null) {
        $xml = "<FeatureSet>";
        if ($session != null && $session != "") {
            $xml .= "<SessionID>" . $session . "</SessionID>";
        }
        $xml .= "<Features><Feature>";
        $xml .= "<Property><Name>RNAME</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>SHPGEOM</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</Feature></Features></FeatureSet>";
        return $xml;
    }
    private function createUpdateXml($filter, $text, $geom, $session = null) {
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
        return $xml;
    }
    private function createInsertJson($text, $geom, $session = null) {
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
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Insert - Session ID
        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Update - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "POST", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Update - Session ID
        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "PUT", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "POST", $this->createUpdateXml("Autogenerated_SDF_ID = " . $testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Update - Single Access - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/$testID2.xml", "POST", $this->createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Update - Single Access - Session ID
        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/$testID2.xml", "PUT", $this->createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Delete - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), "Foo", "Bar");
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), "Anonymous", "");
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Delete - Session ID
        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->anonymousSessionId));
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->adminSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->wfsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->wmsSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->authorSessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->user1SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/data/test_anonymous/.xml", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->user2SessionId));
        $this->assertEquals(403, $resp->getStatusCode());
        $this->assertXmlContent($resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        //Delete - single access - credentials
        //Delete - single access - Session ID
    }
}

?>