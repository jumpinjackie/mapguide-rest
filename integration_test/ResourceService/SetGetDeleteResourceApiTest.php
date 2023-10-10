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

require_once dirname(__FILE__)."/../Config.php";
require_once dirname(__FILE__)."/../ServiceTest.php";

class SetGetDeleteResourceApiTest extends ServiceTest {
    protected function set_up() {
        parent::set_up();
    }
    protected function tear_down() {
        parent::tear_down();
    }
    private function getSessionResourceUrlPart() {
        return "/session/" . $this->anonymousSessionId . "/Empty.FeatureSource";
    }
    private function getLibraryResourceUrlPart() {
        return "/library/RestUnitTests/Empty.FeatureSource";
    }
    private function getSessionResourceUrlPart2() {
        return "/session/" . $this->anonymousSessionId . "/Empty.FeatureSource";
    }
    private function getLibraryResourceUrlPart2() {
        return "/library/RestUnitTests/Empty.FeatureSource";
    }
    private function createHeaderXml() {
        $xml = '<ResourceDocumentHeader xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="ResourceDocumentHeader-1.0.0.xsd">';
        $xml .= '<Security><Inherited>true</Inherited></Security>';
        $xml .= '<Metadata><Simple>';
        $xml .= "<Property><Name>HelloWorld</Name><Value>1</Value></Property>";
        $xml .= '</Simple></Metadata>';
        $xml .= '</ResourceDocumentHeader>';
        return $xml;
    }
    private function __testOperation($resPart, $bTestUnauth) {
        $emptyFeatureSourceXml = '<?xml version="1.0" encoding="UTF-8"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%Empty.sdf</Value></Parameter></FeatureSource>';
        if ($bTestUnauth) {
            $resp = $this->apiTestWithCredentials("$resPart/content.xml", "POST", array(), "Foo", "Bar");
            $this->assertStatusCodeIs(401, $resp);

            $resp = $this->apiTestAnon("$resPart/content.xml", "POST", $emptyFeatureSourceXml);
            $this->assertStatusCodeIs(401, $resp);
        }

        $resp = $this->apiTestAdmin("$resPart/content.xml", "POST", $emptyFeatureSourceXml);
        $this->assertStatusCodeIs(201, $resp);

        if ($bTestUnauth) {
            $resp = $this->apiTestAnon("$resPart/resource", "DELETE", null);
            $this->assertStatusCodeIs(401, $resp);
        }

        $resp = $this->apiTestAdmin("$resPart/resource", "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
    }
    private function __testOperationAltRoute($resPart, $resPart2, $bTestUnauth, $bTestGetHeader) {
        $emptyFeatureSourceXml = '<?xml version="1.0" encoding="UTF-8"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%Empty.sdf</Value></Parameter></FeatureSource>';
        if ($bTestUnauth) {
            $resp = $this->apiTestWithCredentials("$resPart2/contentorheader.xml", "POST", array(), "Foo", "Bar");
            $this->assertStatusCodeIs(401, $resp);

            $resp = $this->apiTestAnon("$resPart2/contentorheader.xml", "POST", array("content" => $this->makeContentBlob($emptyFeatureSourceXml)));
            $this->assertStatusCodeIs(401, $resp);
        }
        $resp = $this->apiTestAdmin("$resPart2/contentorheader.xml", "POST", array("content" => $this->makeContentBlob($emptyFeatureSourceXml)));
        $this->assertStatusCodeIs(201, $resp);

        if ($bTestGetHeader) {
            $resp = $this->apiTestAdmin("$resPart2/header.xml", "GET", null);
            $this->assertStatusCodeIs(200, $resp);
            $this->assertXmlContent($resp);
            $this->assertTrue(strpos($resp->getContent(), "<Name>HelloWorld</Name>") === FALSE);
        }

        $resp = $this->apiTestAdmin("$resPart2/contentorheader.xml", "POST", array("content" => $this->makeContentBlob($emptyFeatureSourceXml), "header" => $this->makeContentBlob($this->createHeaderXml())));
        $this->assertStatusCodeIs(201, $resp);

        if ($bTestGetHeader) {
            $resp = $this->apiTestAdmin("$resPart2/header.xml", "GET", null);
            $this->assertStatusCodeIs(200, $resp);
            $this->assertXmlContent($resp);
            $this->assertTrue(strpos($resp->getContent(), "<Name>HelloWorld</Name>") !== FALSE);
        }

        if ($bTestUnauth) {
            $resp = $this->apiTestAnon("$resPart2/resource", "DELETE", null);
            $this->assertStatusCodeIs(401, $resp);
        }

        $resp = $this->apiTestAdmin("$resPart2/resource", "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
    }
    public function testLibraryOperation() {
        $this->__testOperation($this->getLibraryResourceUrlPart(), true);
    }
    public function testLibraryOperationAltRoute() {
        $this->__testOperationAltRoute($this->getLibraryResourceUrlPart(), $this->getLibraryResourceUrlPart2(), true, true);
    }
    public function testSessionOperation() {
        $this->__testOperation($this->getSessionResourceUrlPart(), false);
    }
    public function testSessionOperationAltRoute() {
        $this->__testOperationAltRoute($this->getSessionResourceUrlPart(), $this->getSessionResourceUrlPart2(), false, false);
    }
}