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

class JsonRuntimeMapTest extends ServiceTest {
    private function createLayerJson($fsId, $className, $geom) {
        return "{
            \"LayerDefinition\": {
                \"@xmlns:xsi\": \"http://www.w3.org/2001/XMLSchema-instance\",
                \"@version\": \"1.0.0\",
                \"@xsi:noNamespaceSchemaLocation\": \"LayerDefinition-1.0.0.xsd\",
                \"VectorLayerDefinition\": {
                    \"ResourceId\": \"$fsId\",
                    \"FeatureName\": \"$className\",
                    \"FeatureNameType\": \"FeatureClass\",
                    \"Geometry\": \"$geom\",
                    \"VectorScaleRange\": [
                        {
                            \"PointTypeStyle\": {
                                \"DisplayAsText\": false,
                                \"AllowOverpost\": false,
                                \"PointRule\": [
                                    {
                                        \"LegendLabel\": null,
                                        \"PointSymbolization2D\": {
                                            \"Mark\": {
                                                \"Unit\": \"Points\",
                                                \"SizeContext\": \"DeviceUnits\",
                                                \"SizeX\": \"10\",
                                                \"SizeY\": \"10\",
                                                \"Rotation\": \"0\",
                                                \"Shape\": \"Square\",
                                                \"Fill\": {
                                                    \"FillPattern\": \"Solid\",
                                                    \"ForegroundColor\": \"ffffffff\",
                                                    \"BackgroundColor\": \"ffffffff\"
                                                },
                                                \"Edge\": {
                                                    \"LineStyle\": \"Solid\",
                                                    \"Thickness\": \"1\",
                                                    \"Color\": \"ff000000\",
                                                    \"Unit\": \"Points\"
                                                }
                                            }
                                        }
                                    }
                                ]
                            },
                            \"LineTypeStyle\": {
                                \"LineRule\": [
                                    {
                                        \"LegendLabel\": null,
                                        \"LineSymbolization2D\": [
                                            {
                                                \"LineStyle\": \"Solid\",
                                                \"Thickness\": \"1\",
                                                \"Color\": \"ff000000\",
                                                \"Unit\": \"Points\"
                                            }
                                        ]
                                    }
                                ]
                            },
                            \"AreaTypeStyle\": {
                                \"AreaRule\": [
                                    {
                                        \"LegendLabel\": null,
                                        \"AreaSymbolization2D\": {
                                            \"Fill\": {
                                                \"FillPattern\": \"Solid\",
                                                \"ForegroundColor\": \"ffffffff\",
                                                \"BackgroundColor\": \"ffffffff\"
                                            },
                                            \"Stroke\": {
                                                \"LineStyle\": \"Solid\",
                                                \"Thickness\": \"1\",
                                                \"Color\": \"ff000000\",
                                                \"Unit\": \"Points\"
                                            }
                                        }
                                    }
                                ]
                            }
                        }
                    ]
                }
            }
        }";
    }
    private function createModificationJson() {
        return "{
            \"UpdateMap\": {
                \"Operation\": [
                    {
                        \"Type\": \"RemoveLayer\",
                        \"Name\": \"Trees\"
                    },
                    {
                        \"Type\": \"RemoveGroup\",
                        \"Name\": \"Base Map\"
                    }
                ]
            }
        }";
    }
    private function createInsertLayerJson($name, $ldfId, $label, $bVisible, $bSelectable, $bShowInLegend) {
        return "{
            \"UpdateMap\": {
                \"Operation\": [
                    {
                        \"Type\": \"AddGroup\",
                        \"Name\": \"Session-based Layers\",
                        \"SetExpandInLegend\": true,
                        \"SetDisplayInLegend\": true,
                        \"SetVisible\": true,
                        \"SetLegendLabel\": \"Session Layers\"
                    },
                    {
                        \"Type\": \"AddLayer\",
                        \"Name\": \"$name\",
                        \"ResourceId\": \"$ldfId\",
                        \"SetLegendLabel\": \"$label\",
                        \"SetSelectable\": ".($bSelectable ? "true" : "false").",
                        \"SetVisible\": ".($bVisible ? "true" : "false").",
                        \"SetDisplayInLegend\": ".($bShowInLegend ? "true" : "false").",
                        \"SetGroup\": \"Session-based Layers\"
                    }
                ]
            }
        }";
    }

    public function testOperation() {
        $reqFeatures = (1|2|4);
        $mdf = "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition";
        $resp = $this->apiTest("/services/createmap.json", "POST", array("session" => $this->anonymousSessionId, "requestedfeatures" => $reqFeatures, "mapdefinition" => $mdf));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $content = json_decode($resp->getContent());
        $anonMapName = $content->RuntimeMap->Name;

        $resp = $this->apiTest("/services/createmap.json", "POST", array("session" => $this->adminSessionId, "requestedfeatures" => $reqFeatures, "mapdefinition" => $mdf));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $content = json_decode($resp->getContent());
        $adminMapName = $content->RuntimeMap->Name;

        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/layersandgroups.json", "PUT", $this->createModificationJson());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/layersandgroups.json", "PUT", $this->createModificationJson());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);

        //Verify by re-querying layer structure
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/description.json", "GET", array("requestedfeatures" => $reqFeatures));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $rtMap = json_decode($resp->getContent())->RuntimeMap;
        $bGroupRemoved = true;
        $bLayerRemoved = true;
        foreach ($rtMap->Group as $grp) {
            if ($grp->Name == "Base Map") {
                $bGroupRemoved = false;
            }
        }
        foreach ($rtMap->Layer as $lyr) {
            if ($lyr->Name == "Trees") {
                $bLayerRemoved = false;
            }
        }
        $this->assertTrue($bGroupRemoved, "Expected 'Base Map' group to be removed");
        $this->assertTrue($bLayerRemoved, "Expected 'Trees' layer to be removed");

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/description.json", "GET", array("requestedfeatures" => $reqFeatures));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $rtMap = json_decode($resp->getContent())->RuntimeMap;
        $bGroupRemoved = true;
        $bLayerRemoved = true;
        foreach ($rtMap->Group as $grp) {
            if ($grp->Name == "Base Map") {
                $bGroupRemoved = false;
            }
        }
        foreach ($rtMap->Layer as $lyr) {
            if ($lyr->Name == "Trees") {
                $bLayerRemoved = false;
            }
        }
        $this->assertTrue($bGroupRemoved, "Expected 'Base Map' group to be removed");
        $this->assertTrue($bLayerRemoved, "Expected 'Trees' layer to be removed");

        $fsId = "Library://Samples/Sheboygan/Data/Trees.FeatureSource";
        $cls = "SHP_Schema:Trees";
        $geom = "SHPGEOM";

        //Insert a session-based layer
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/Trees.LayerDefinition/content.json", "POST", $this->createLayerJson($fsId, $cls, $geom));
        $this->assertStatusCodeIs(201, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/Trees.LayerDefinition/content.json", "POST", $this->createLayerJson($fsId, $cls, $geom));
        $this->assertStatusCodeIs(201, $resp);

        $anonTreesJson = $this->createInsertLayerJson("Trees", "Session:".$this->anonymousSessionId."//Trees.LayerDefinition", "Trees (Session-based)", true, false, true);
        $adminTreesJson = $this->createInsertLayerJson("Trees", "Session:".$this->adminSessionId."//Trees.LayerDefinition", "Trees (Session-based)", false, true, false);

        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/layersandgroups.json", "PUT", $anonTreesJson);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/layersandgroups.json", "PUT", $adminTreesJson);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);

        //Verify by re-querying layer structure
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/description.json", "GET", array("requestedfeatures" => $reqFeatures));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $rtMap = json_decode($resp->getContent())->RuntimeMap;

        $bFoundTrees = false;
        $bFoundGroup = false;
        foreach ($rtMap->Group as $grp) {
            if ($grp->Name == "Session-based Layers") {
                $bFoundGroup = true;
                $this->assertTrue($grp->Visible, "Expected group Visible = true");
                $this->assertTrue($grp->ExpandInLegend, "Expected group ExpandInLegend = true");
                $this->assertTrue($grp->DisplayInLegend, "Expected group DisplayInLegend = true");
                $this->assertEquals($grp->LegendLabel, "Session Layers", "Expected group label: Session Layers");
            }
        }
        foreach ($rtMap->Layer as $lyr) {
            if ($lyr->Name == "Trees") {
                $bFoundTrees = true;
                $this->assertTrue($lyr->Visible, "Expected layer Visible = true");
                $this->assertFalse($lyr->Selectable, "Expected layer Selectable = false");
                $this->assertTrue($lyr->DisplayInLegend, "Expected layer DisplayInLegend = true");
                $this->assertEquals($lyr->LegendLabel, "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
            }
        }

        $this->assertTrue($bFoundGroup, "Expected 'Session-based Layers' group to be added");
        $this->assertTrue($bFoundTrees, "Expected 'Trees' layer to be re-added");

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/description.json", "GET", array("requestedfeatures" => $reqFeatures));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $rtMap = json_decode($resp->getContent())->RuntimeMap;

        $bFoundTrees = false;
        $bFoundGroup = false;
        foreach ($rtMap->Group as $grp) {
            if ($grp->Name == "Session-based Layers") {
                $bFoundGroup = true;
                $this->assertTrue($grp->Visible, "Expected group Visible = true");
                $this->assertTrue($grp->ExpandInLegend, "Expected group ExpandInLegend = true");
                $this->assertTrue($grp->DisplayInLegend, "Expected group DisplayInLegend = true");
                $this->assertEquals($grp->LegendLabel, "Session Layers", "Expected group label: Session Layers");
            }
        }
        foreach ($rtMap->Layer as $lyr) {
            if ($lyr->Name == "Trees") {
                $bFoundTrees = true;
                $this->assertFalse($lyr->Visible, "Expected layer Visible = false");
                $this->assertTrue($lyr->Selectable, "Expected layer Selectable = true");
                $this->assertFalse($lyr->DisplayInLegend, "Expected layer DisplayInLegend = false");
                $this->assertEquals($lyr->LegendLabel, "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
            }
        }

        $this->assertTrue($bFoundGroup, "Expected 'Session-based Layers' group to be added");
        $this->assertTrue($bFoundTrees, "Expected 'Trees' layer to be re-added");
    }
}