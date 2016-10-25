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

class XmlRuntimeMapTest extends ServiceTest {
    private function createLayerXml($fsId, $className, $geom) {
        $xml = '<LayerDefinition xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" version="1.0.0" xsi:noNamespaceSchemaLocation="LayerDefinition-1.0.0.xsd">';
        $xml .= "<VectorLayerDefinition>";
            $xml .= "<ResourceId>" . $fsId . "</ResourceId>";
            $xml .= "<FeatureName>" . $className . "</FeatureName>";
            $xml .= "<FeatureNameType>FeatureClass</FeatureNameType>";
            $xml .= "<Geometry>" . $geom . "</Geometry>";
            $xml .= "<VectorScaleRange>";
            $xml .= "<PointTypeStyle>";
                $xml .= "<DisplayAsText>false</DisplayAsText>";
                $xml .= "<AllowOverpost>false</AllowOverpost>";
                $xml .= "<PointRule>";
                $xml .= "<LegendLabel />";
                $xml .= "<PointSymbolization2D>";
                    $xml .= "<Mark>";
                    $xml .= "<Unit>Points</Unit>";
                    $xml .= "<SizeContext>DeviceUnits</SizeContext>";
                    $xml .= "<SizeX>10</SizeX>";
                    $xml .= "<SizeY>10</SizeY>";
                    $xml .= "<Rotation>0</Rotation>";
                    $xml .= "<Shape>Square</Shape>";
                    $xml .= "<Fill>";
                        $xml .= "<FillPattern>Solid</FillPattern>";
                        $xml .= "<ForegroundColor>ffffffff</ForegroundColor>";
                        $xml .= "<BackgroundColor>ffffffff</BackgroundColor>";
                    $xml .= "</Fill>";
                    $xml .= "<Edge>";
                        $xml .= "<LineStyle>Solid</LineStyle>";
                        $xml .= "<Thickness>1</Thickness>";
                        $xml .= "<Color>ff000000</Color>";
                        $xml .= "<Unit>Points</Unit>";
                    $xml .= "</Edge>";
                    $xml .= "</Mark>";
                $xml .= "</PointSymbolization2D>";
                $xml .= "</PointRule>";
            $xml .= "</PointTypeStyle>";
            $xml .= "<LineTypeStyle>";
                $xml .= "<LineRule>";
                $xml .= "<LegendLabel />";
                $xml .= "<LineSymbolization2D>";
                    $xml .= "<LineStyle>Solid</LineStyle>";
                    $xml .= "<Thickness>1</Thickness>";
                    $xml .= "<Color>ff000000</Color>";
                    $xml .= "<Unit>Points</Unit>";
                $xml .= "</LineSymbolization2D>";
                $xml .= "</LineRule>";
            $xml .= "</LineTypeStyle>";
            $xml .= "<AreaTypeStyle>";
                $xml .= "<AreaRule>";
                $xml .= "<LegendLabel />";
                $xml .= "<AreaSymbolization2D>";
                    $xml .= "<Fill>";
                    $xml .= "<FillPattern>Solid</FillPattern>";
                    $xml .= "<ForegroundColor>ffffffff</ForegroundColor>";
                    $xml .= "<BackgroundColor>ffffffff</BackgroundColor>";
                    $xml .= "</Fill>";
                    $xml .= "<Stroke>";
                    $xml .= "<LineStyle>Solid</LineStyle>";
                    $xml .= "<Thickness>1</Thickness>";
                    $xml .= "<Color>ff000000</Color>";
                    $xml .= "<Unit>Points</Unit>";
                    $xml .= "</Stroke>";
                $xml .= "</AreaSymbolization2D>";
                $xml .= "</AreaRule>";
            $xml .= "</AreaTypeStyle>";
            $xml .= "</VectorScaleRange>";
        $xml .= "</VectorLayerDefinition>";
        $xml .= "</LayerDefinition>";
        return $xml;
    }
    
    private function createModificationXml() {
        $xml = "<UpdateMap>";
        $xml .= "<Operation>";
        $xml .= "<Type>RemoveLayer</Type>";
        $xml .= "<Name>Trees</Name>";
        $xml .= "</Operation>";
        $xml .= "<Operation>";
        $xml .= "<Type>RemoveGroup</Type>";
        $xml .= "<Name>Base Map</Name>";
        $xml .= "</Operation>";
        $xml .= "</UpdateMap>";
        return $xml;
    }
    
    private function createInsertLayerXml($name, $ldfId, $label, $bVisible, $bSelectable, $bShowInLegend) {
        $xml = "<UpdateMap>";
        $xml .= "<Operation>";
        $xml .= "<Type>AddGroup</Type>";
        $xml .= "<Name>Session-based Layers</Name>";
        $xml .= "<SetExpandInLegend>true</SetExpandInLegend>";
        $xml .= "<SetDisplayInLegend>true</SetDisplayInLegend>";
        $xml .= "<SetVisible>true</SetVisible>";
        $xml .= "<SetLegendLabel>Session Layers</SetLegendLabel>";
        $xml .= "</Operation>";
        $xml .= "<Operation>";
        $xml .= "<Type>AddLayer</Type>";
        $xml .= "<Name>" . $name . "</Name>";
        $xml .= "<ResourceId>" . $ldfId . "</ResourceId>";
        $xml .= "<SetLegendLabel>" . $label . "</SetLegendLabel>";
        $xml .= "<SetSelectable>" . ($bSelectable ? "true" : "false") . "</SetSelectable>";
        $xml .= "<SetVisible>" . ($bVisible ? "true" : "false") . "</SetVisible>";
        $xml .= "<SetDisplayInLegend>" . ($bShowInLegend ? "true" : "false") . "</SetDisplayInLegend>";
        $xml .= "<SetGroup>Session-based Layers</SetGroup>";
        $xml .= "</Operation>";
        $xml .= "</UpdateMap>";
        return $xml;
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

        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/layersandgroups.xml", "PUT", $this->createModificationXml());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/layersandgroups.xml", "PUT", $this->createModificationXml());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

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
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/Trees.LayerDefinition/content.xml", "POST", $this->createLayerXml($fsId, $cls, $geom));
        $this->assertStatusCodeIs(201, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/Trees.LayerDefinition/content.xml", "POST", $this->createLayerXml($fsId, $cls, $geom));
        $this->assertStatusCodeIs(201, $resp);

        $anonTreesXml = $this->createInsertLayerXml("Trees", "Session:".$this->anonymousSessionId."//Trees.LayerDefinition", "Trees (Session-based)", true, false, true);
        $adminTreesXml = $this->createInsertLayerXml("Trees", "Session:".$this->adminSessionId."//Trees.LayerDefinition", "Trees (Session-based)", false, true, false);

        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/layersandgroups.xml", "PUT", $anonTreesXml);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

        $resp = $this->apiTest("/session/".$this->adminSessionId."/".$adminMapName.".Map/layersandgroups.xml", "PUT", $adminTreesXml);
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_XML, $resp);

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

?>