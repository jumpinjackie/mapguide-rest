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

class EnumerateResourceReferencesApiTest extends ServiceTest {
    public function testBadRequest() {
        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestWithCredentials("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("depth" => -1, "type" => "FeatureSource"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null);
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(400, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    public function testRawCredentials() {
        //XML
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        //JSON
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        //HTML
        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAnon("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTestAdmin("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
    public function testSessionId() {
        //XML
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertXmlContent($resp);

        //JSON
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertJsonContent($resp);
        
        //HTML
        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("session" => $this->anonymousSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);

        $resp = $this->apiTest("/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", array("session" => $this->adminSessionId, "depth" => -1, "type" => "LayerDefinition"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_HTML, $resp);
    }
}