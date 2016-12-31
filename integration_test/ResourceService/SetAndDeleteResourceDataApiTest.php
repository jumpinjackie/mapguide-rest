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

class SetResourceDataTest extends ServiceTest {
    private function getSessionResourceId() {
        return "/session/".$this->adminSessionId."/Empty.FeatureSource";
    }
    public function testSessionOperation() {
        $emptyFeatureSourceXml = '<?xml version="1.0" encoding="UTF-8"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%Empty.sdf</Value></Parameter></FeatureSource>';
        $resPart = $this->getSessionResourceId();
        $args = array("type" => "File", "data" => $this->makeContentBlob("<Test></Test>"));

        $resp = $this->apiTest("$resPart/content.xml", "POST", $emptyFeatureSourceXml);
        $this->assertStatusCodeIs(201, $resp);

        $resp = $this->apiTest("$resPart/data/test.xml", "POST", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        //Load the data item - All tests should work because the session id in the resource id is enough to provide valid credentials and is first under consideration
        $resp = $this->apiTestWithCredentials("$resPart/data/test.xml", "POST", $args, "Foo", "Bar");
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("$resPart/data/test.xml", "POST", $args);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("$resPart/data/test.xml", "POST", $args);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("$resPart/data/test.xml", "POST", $args);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        //Check the data item is on the list
        $resp = $this->apiTest("$resPart/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        $resp = $this->apiTestAdmin("$resPart/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        $resp = $this->apiTestAdmin("$resPart/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, "Expected valid json");
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
                break;
            }
        }
        $this->assertTrue($bFound);

        $resp = $this->apiTest("$resPart/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, "Expected valid json");
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
                break;
            }
        }
        $this->assertTrue($bFound);

        //Delete the item
        $resp = $this->apiTest("$resPart/data/test.xml", "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);

        //Now check the data item is no longer there
        $resp = $this->apiTestAdmin("$resPart/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        $resp = $this->apiTest("$resPart/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        $resp = $this->apiTestAdmin("$resPart/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, "Expected valid json");
        $this->assertFalse(isset($json->ResourceDataList->ResourceData), "Expected no ResourceData");

        $resp = $this->apiTest("$resPart/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, "Expected valid json");
        $this->assertFalse(isset($json->ResourceDataList->ResourceData), "Expected no ResourceData");
    }
    public function testLibraryBadRequests() {
        $args = array("type" => "File", "data" => $this->makeContentBlob("<Test></Test>"));
        
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $args, "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
        
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $args);
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    public function testLibraryOperation() {
        $args = array("type" => "File", "data" => $this->makeContentBlob("<Test></Test>"));
        //Load the data item
        $mergedArgs = array_merge($args, array("session" => $this->anonymousSessionId));
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $mergedArgs);
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $mergedArgs = array_merge($args, array("session" => $this->adminSessionId));
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $mergedArgs);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
        
        //Check that the data item is on the list - XML
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") !== FALSE);

        //Check that the data item is on the list - JSON
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertTrue($bFound);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertTrue($bFound);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertTrue($bFound);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertTrue($bFound);

        //Delete the item
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "DELETE", null);
        $this->assertStatusCodeIs(401, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);

        //Now check that the data is no longer there - XML
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);
        $this->assertTrue(strpos($resp->getContent(), ">test.xml</") === FALSE);

        //Now check that the data is no longer there - JSON
        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertFalse($bFound);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertFalse($bFound);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertFalse($bFound);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        $json = json_decode($resp->getContent());
        $bFound = false;
        foreach ($json->ResourceDataList->ResourceData as $resData) {
            if ($resData->Name == "test.xml") {
                $bFound = true;
            }
        }
        $this->assertFalse($bFound);
    }
}