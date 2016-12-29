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

class CrudLibraryTest extends ServiceTest {
    private function createInsertXml($text, $geom) {
        $xml = "<FeatureSet><Features><Feature>";
        $xml .= "<Property><Name>Text</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>Geometry</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</Feature></Features></FeatureSet>";
        return $xml;
    }
    private function createInsertJson($text, $geomWkt) {
        $json = "{
            \"FeatureSet\": {
                \"Features\": {
                    \"Feature\": [
                        {
                            \"Property\": [
                                { \"Name\": \"Text\", \"Value\": \"$text\" },
                                { \"Name\": \"Geometry\", \"Value\": \"$geomWkt\" }
                            ]
                        }
                    ]
                }
            }
        }";
        return $json;
    }
    private function createInsertPayload($kind, $text, $geom) {
        if ($kind === "xml") {
            return $this->createInsertXml($text, $geom);
        } else if ($kind === "json") {
            return $this->createInsertJson($text, $geom);
        }
        throw new Exception("Unknown kind: $kind");
    }
    private function createUpdateXml($filter, $text, $geom) {
        $xml = "<UpdateOperation>";
        $xml .= "<Filter>" . $filter . "</Filter>";
        $xml .= "<UpdateProperties>";
        $xml .= "<Property><Name>Text</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>Geometry</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</UpdateProperties>";
        $xml .= "</UpdateOperation>";
        return $xml;
    }
    private function createUpdateJson($filter, $text, $geomWkt) {
        $json = "{
            \"UpdateOperation\": {
                \"Filter\": \"$filter\",
                \"UpdateProperties\": {
                    \"Property\": [
                        { \"Name\": \"Text\", \"Value\": \"$text\" },
                        { \"Name\": \"Geometry\", \"Value\": \"$geomWkt\" }
                    ]
                }
            }
        }";
        return $json;
    }
    private function createUpdatePayload($kind, $filter, $text, $geom) {
        if ($kind === "xml") {
            return $this->createUpdateXml($filter, $text, $geom);
        } else if ($kind === "json") {
            return $this->createUpdateJson($filter, $text, $geom);
        }
        throw new Exception("Unknown kind: $kind");
    }
    private function createHeaderXml($bInsert, $bUpdate, $bDelete, $bUseTransaction) {
        $xml = '<ResourceDocumentHeader xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="ResourceDocumentHeader-1.0.0.xsd">';
        $xml .= '<Security><Inherited>true</Inherited></Security>';
        $xml .= '<Metadata><Simple>';
        if ($bInsert === true) {
            $xml .= "<Property><Name>_MgRestAllowInsert</Name><Value>1</Value></Property>";
        } else {
            $xml .= "<Property><Name>_MgRestAllowInsert</Name><Value>0</Value></Property>";
        }
        if ($bUpdate === true) {
            $xml .= "<Property><Name>_MgRestAllowUpdate</Name><Value>1</Value></Property>";
        } else {
            $xml .= "<Property><Name>_MgRestAllowUpdate</Name><Value>0</Value></Property>";
        }
        if ($bDelete === true) {
            $xml .= "<Property><Name>_MgRestAllowDelete</Name><Value>1</Value></Property>";
        } else {
            $xml .= "<Property><Name>_MgRestAllowDelete</Name><Value>0</Value></Property>";
        }
        if ($bUseTransaction === true) {
            $xml .= "<Property><Name>_MgRestUseTransaction</Name><Value>1</Value></Property>";
        } else {
            $xml .= "<Property><Name>_MgRestUseTransaction</Name><Value>0</Value></Property>";
        }
        $xml .= '</Simple></Metadata></ResourceDocumentHeader>';
        return $xml;
    }
    private function createHeaderJson($bInsert, $bUpdate, $bDelete, $bUseTransaction) {
        $caps = "{
            \"RestCapabilities\": {
                \"AllowInsert\": ".($bInsert ? "true" : "false").",
                \"AllowUpdate\": ".($bUpdate ? "true" : "false").",
                \"AllowDelete\": ".($bDelete ? "true" : "false").",
                \"UseTransaction\": ".($bUseTransaction ? "true" : "false")."
            }
        }";
        return $caps;
    }
    private function createHeaderPayload($kind, $bInsert, $bUpdate, $bDelete, $bUseTransaction) {
        if ($kind === "xml") {
            return $this->createHeaderXml($bInsert, $bUpdate, $bDelete, $bUseTransaction);
        } else if ($kind === "json") {
            return $this->createHeaderJson($bInsert, $bUpdate, $bDelete, $bUseTransaction);
        }
        throw new Exception("Unknown kind: $kind");
    }
    private function getHeaderUrl($kind) {
        if ($kind === "xml") {
            return "/library/RestUnitTests/RedlineLayer.FeatureSource/header.$kind";
        } else if ($kind === "json") {
            return "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json";
        }
        throw new Exception("Unknown kind: $kind");
    }
    private function getHeaderSuccessCode($kind) {
        if ($kind === "xml") {
            return 200;
        } else if ($kind === "json") {
            return 201;
        }
        throw new Exception("Unknown kind: $kind");
    }
    private function __testOperation($kind, $extension, $mimeType) {
        //Disable everything
        $resp = $this->apiTestAnon($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, false, false, false, false));
        $this->assertStatusCodeIs(401, $resp);

        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, false, false, false, false));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "anon credential insert", "POINT (0 0)"));
        $this->assertStatusCodeIs(403, $resp);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "admin credential insert", "POINT (1 1)"));
        $this->assertStatusCodeIs(403, $resp);

        //Enable insert
        $resp = $this->apiTestAnon($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, false, false, false));
        $this->assertStatusCodeIs(401, $resp);

        //Enable insert/transactions
        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, false, false, true));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "anon credential insert", "POINT (0 0)"));
        $this->assertStatusCodeIs(500, $resp);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "admin credential insert", "POINT (1 1)"));
        $this->assertStatusCodeIs(500, $resp);

        //Enable insert, disable transactions
        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, false, false, false));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "anon credential insert", "POINT (0 0)"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "POST", $this->createInsertPayload($kind, "admin credential insert", "POINT (1 1)"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTest("/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "transformto" => "WGS84.PseudoMercator"));
        $this->assertStatusCodeIs(200, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, $resp->dump());

        $this->assertEquals(2, count($json->features), "Expected 2 inserted features");
        foreach ($json->features as $feat) {
            if ($feat->id == 1) {
                $this->assertEquals("anon credential insert", $feat->properties->Text, "expected correct feature text for ID 1");
            } else if ($feat->id == 2) {
                $this->assertEquals("admin credential insert", $feat->properties->Text, "expected correct feature text for ID 2");
            }
        }

        //Try update
        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 1", "anon credential update", "POINT (2 2)"));
        $this->assertStatusCodeIs(403, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 2", "admin credential update", "POINT (3 3)"));
        $this->assertStatusCodeIs(403, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Enable update
        $resp = $this->apiTestAnon($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, false, false));
        $this->assertStatusCodeIs(401, $resp);

        //Enable insert/update/transactions
        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, false, true));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 1", "anon credential update", "POINT (2 2)"));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 2", "admin credential update", "POINT (3 3)"));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Enable insert/update. Disable transactions
        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, false, false));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 1", "anon credential update", "POINT (2 2)"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "PUT", $this->createUpdatePayload($kind, "ID = 2", "admin credential update", "POINT (3 3)"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTest("/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "transformto" => "WGS84.PseudoMercator"));
        $this->assertStatusCodeIs(200, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, $resp->dump());

        $this->assertEquals(2, count($json->features), "Expected 2 features");
        foreach ($json->features as $feat) {
            if ($feat->id == 1) {
                $this->assertEquals("anon credential update", $feat->properties->Text, "expected correct feature text for ID 1");
            } else if ($feat->id == 2) {
                $this->assertEquals("admin credential update", $feat->properties->Text, "expected correct feature text for ID 2");
            }
        }

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 2"));
        $this->assertStatusCodeIs(403, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 1"));
        $this->assertStatusCodeIs(403, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Enable everything
        $resp = $this->apiTestAnon($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, true, false));
        $this->assertStatusCodeIs(401, $resp);

        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, true, true));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 2"));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 1"));
        $this->assertStatusCodeIs(500, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Enable everything but transactions
        $resp = $this->apiTestAdmin($this->getHeaderUrl($kind), "POST", $this->createHeaderPayload($kind, true, true, true, false));
        $this->assertStatusCodeIs($this->getHeaderSuccessCode($kind), $resp);

        $resp = $this->apiTestAdmin("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 2"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Check deletion
        $resp = $this->apiTest("/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "transformto" => "WGS84.PseudoMercator"));
        $this->assertStatusCodeIs(200, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, $resp->dump());
        $this->assertEquals(1, count($json->features), "Expected 1 feature");
        $this->assertEquals(1, $json->features[0]->id, "Expected feature ID 2 to be deleted");

        $resp = $this->apiTestAnon("/library/RestUnitTests/RedlineLayer.FeatureSource/features.$kind/MarkupSchema/Markup", "DELETE", array("filter" => "ID = 1"));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType($mimeType, $resp);
        $this->assertContentKind($resp, $kind);

        //Check deletion
        $resp = $this->apiTest("/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", array("session" => $this->anonymousSessionId, "maxfeatures" => 100, "transformto" => "WGS84.PseudoMercator"));
        $this->assertStatusCodeIs(200, $resp);
        $json = json_decode($resp->getContent());
        $this->assertNotNull($json, $resp->dump());
        $this->assertEquals(0, count($json->features), "Expected no features");
    }
    public function testOperationXml() {
        $this->__testOperation("xml", "xml", Configuration::MIME_XML);
    }
    public function testOperationGeoJson() {
        $this->__testOperation("json", "geojson", Configuration::MIME_JSON);
    }
}