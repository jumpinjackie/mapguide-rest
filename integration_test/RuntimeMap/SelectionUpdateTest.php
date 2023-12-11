<?php

//
//  Copyright (C) 2023 by Jackie Ng
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

class SelectionUpdateTest extends ServiceTest {
    public function testSelectionUpdate() {
        // Create the map
        $reqFeatures = (1|2|4);
        $mdf = "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition";
        $resp = $this->apiTest("/services/createmap.json", "POST", array(
            "session" => $this->anonymousSessionId,
            "requestedfeatures" => $reqFeatures,
            "mapdefinition" => $mdf
        ));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $content = json_decode($resp->getContent());
        $anonMapName = $content->RuntimeMap->Name;

        //Render an overlay image to establish width/height
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Map/overlayimage.PNG", "GET", array(
            "behavior" => "2",
            "x" => "-87.73025425093128",
            "y" => "43.744459064634064",
            "scale" => "100000",
            "width" => "585",
            "height" => "893",
            "clip" => "true"
        ));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_PNG, $resp);

        //Perform the update
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Selection", "PUT", array(
            "maxfeatures" => "-1",
            "selectionvariant" => "INTERSECTS",
            "layernames" => "Parcels",
            "persist" => "true",
            "requestdata" => "0",
            "featurefilter" => "<SelectionUpdate><Layer><Name>Parcels</Name><SelectionFilter>RNAME LIKE 'SCHMITT%'</SelectionFilter></Layer></SelectionUpdate>",
            "layerattributefilter" => "0",
            "selectionxml" => "false",
            "append" => "true",
            "format" => "json"
        ));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $selResult = json_decode($resp->getContent());

        //Should've selected 45 features
        $this->assertEquals(45, count($selResult->FeatureInformation->FeatureSet->Layer[0]->Class->ID));

        //Test layers route
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Selection/layers.json", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $layersResult = json_decode($resp->getContent());
        $this->assertTrue(isset($layersResult->SelectedLayerCollection));
        $this->assertTrue(isset($layersResult->SelectedLayerCollection->SelectedLayer));
        $this->assertEquals(1, count($layersResult->SelectedLayerCollection->SelectedLayer));
        $this->assertEquals(45, $layersResult->SelectedLayerCollection->SelectedLayer[0]->Count);

        //Test overview route
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/".$anonMapName.".Selection/overview.json", "GET", array(
            "bounds" => "true"
        ));
        $this->assertStatusCodeIs(200, $resp);
        $this->assertMimeType(Configuration::MIME_JSON, $resp);
        $overviewResult = json_decode($resp->getContent());

        $this->assertTrue(isset($overviewResult->SelectionOverview->Bounds));
        $this->assertEquals(1, count($overviewResult->SelectionOverview->Layer));
        
        //The features URL is present
        $this->assertTrue(isset($overviewResult->SelectionOverview->Layer[0]->FeaturesUrl));

        //And the URL checks out
        // Slice off the /mapguide/rest part
        $url = substr($overviewResult->SelectionOverview->Layer[0]->FeaturesUrl, strlen("/mapguide/rest"));
        $resp = $this->apiTest($url, "GET", array());
        $this->assertStatusCodeIs(200, $resp);
    }
}