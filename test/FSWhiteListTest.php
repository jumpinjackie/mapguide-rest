<?php

require_once dirname(__FILE__)."/../app/util/whitelist.php";

class FSWhiteListTest extends PHPUnit_Framework_TestCase
{
	private $actions;
	private $representations;
	private $testIds;
	
	public function __construct() {
		$this->actions = array(
			"GETCONNECTIONPROPERTYVALUES",
            "ENUMERATEDATASTORES",
            "GETPROVIDERCAPABILITIES",
            "GETFEATUREPROVIDERS",
            "GETSCHEMAMAPPING",
            "TESTCONNECTION",
            "GETSPATIALCONTEXTS",
            "GETLONGTRANSACTIONS",
            "GETSCHEMAS",
            "CREATEFEATURESOURCE",
            "DESCRIBESCHEMA",
            "GETCLASSES",
            "GETCLASSDEFINITION",
            "GETEDITCAPABILITIES",
            "SETEDITCAPABILITIES",
            "INSERTFEATURES",
            "UPDATEFEATURES",
            "DELETEFEATURES",
            "SELECTAGGREGATES",
            "SELECTFEATURES"
		);
		$this->representations = array("xml", "json", "geojson", "html", "kml");
		$this->testIds = array(
			"Library://Samples/Sheboygan/Data/BuildingOutlines.FeatureSource",
			"Library://Samples/Sheboygan/Data/HydrographicLines.FeatureSource",
			"Library://Samples/Sheboygan/Data/HydrographicPolygons.FeatureSource",
			"Library://Samples/Sheboygan/Data/Islands.FeatureSource",
			"Library://Samples/Sheboygan/Data/LandUse.FeatureSource",
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
			"Library://Samples/Sheboygan/Data/Rail.FeatureSource",
			"Library://Samples/Sheboygan/Data/RoadCenterLines.FeatureSource",
			"Library://Samples/Sheboygan/Data/Soils.FeatureSource",
			"Library://Samples/Sheboygan/Data/Trees.FeatureSource",
			"Library://Samples/Sheboygan/Data/VotingDistricts.FeatureSource"
		);
	}
	
	public function testInactiveWhitelist() {
		//An empty configuration means open season
		$conf = array();
		$mimeType = "text/xml";
		$wl = new MgFeatureSourceWhitelist($conf);
		foreach ($this->testIds as $resIdStr) {
			foreach ($this->actions as $action) {
				foreach ($this->representations as $repr) {
					$bForbidden = false;
					$wl->VerifyWhitelist($resIdStr, $mimeType, function () use ($bForbidden) {
						$bForbidden = true;
					}, $action, $repr);
					$this->assertFalse($bForbidden);
				}
			}
		}
	}
	
	public function testWhitelistOnlyFeatureSource() {
		//Every action/representation on anything not parcels is forbidden
		$conf = array(
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array()
		);
		$mimeType = "text/xml";
		$wl = new MgFeatureSourceWhitelist($conf);
		//Expected forbidden states
		$expect = array(
			"Library://Samples/Sheboygan/Data/BuildingOutlines.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/HydrographicLines.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/HydrographicPolygons.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/Islands.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/LandUse.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => false,
			"Library://Samples/Sheboygan/Data/Rail.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/RoadCenterLines.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/Soils.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/Trees.FeatureSource" => true,
			"Library://Samples/Sheboygan/Data/VotingDistricts.FeatureSource" => true
		);
		foreach ($this->testIds as $resIdStr) {
			foreach ($this->actions as $action) {
				foreach ($this->representations as $repr) {
					$bForbidden = false;
					//print "\nChecking $resIdStr ($action, $repr) is ".($expect[$resIdStr] ? 1 : 0);
					$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
						$bForbidden = true;
					}, $action, $repr);
					//print "\nForbidden result: ".($bForbidden ? 1 : 0);
					$this->assertEquals($expect[$resIdStr], $bForbidden, "Expected $resIdStr to be forbidden: ".($expect[$resIdStr]?"true":"false"));
				}
			}
		}
	}
	
	public function testWhitelistAllActionsXmlOnly() {
		//Everything not parcels is forbidden
		//Any parcel action is allowed
		//Any parcel action with a non-xml representation is forbidden
		$conf = array(
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
				"Actions" => array(),
				"Representations" => array(
					"xml" => array()
				)
			)
		);
		$mimeType = "text/xml";
		$wl = new MgFeatureSourceWhitelist($conf);
		//Check non-parcel FS ids are forbidden
		foreach ($this->testIds as $resIdStr) {
			if ($resIdStr === "Library://Samples/Sheboygan/Data/Parcels.FeatureSource")
				continue;
			foreach ($this->actions as $action) {
				foreach ($this->representations as $repr) {
					$bExpect = true;
					$bForbidden = false;
					$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
						$bForbidden = true;
					}, $action, $repr);
					$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
				}
			}
		}
		//Now focus on parcels
		$resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
		foreach ($this->actions as $action) {
			foreach ($this->representations as $repr) {
				$bExpect = ($repr !== "xml");
				$bForbidden = false;
				$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
					$bForbidden = true;
				}, $action, $repr);
				$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
			}
		}
	}
	
	public function testWhitelistAllRepresentationsWithSpecificActions() {
		//Everything not parcels is forbidden
		//Any parcel action in the list with any representation is allowed
		$conf = array(
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
				"Actions" => array(
					"TESTCONNECTION" => array(),
					"SELECTFEATURES" => array()
				),
				"Representations" => array()
			)
		);
		$mimeType = "text/xml";
		$wl = new MgFeatureSourceWhitelist($conf);
		
		//Check non-parcel FS ids are forbidden
		foreach ($this->testIds as $resIdStr) {
			if ($resIdStr === "Library://Samples/Sheboygan/Data/Parcels.FeatureSource")
				continue;
			foreach ($this->actions as $action) {
				foreach ($this->representations as $repr) {
					$bExpect = true;
					$bForbidden = false;
					$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
						$bForbidden = true;
					}, $action, $repr);
					$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
				}
			}
		}
		//Now focus on parcels
		$resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
		foreach ($this->actions as $action) {
			foreach ($this->representations as $repr) {
				$bExpect = !in_array($action, array("TESTCONNECTION", "SELECTFEATURES"));
				$bForbidden = false;
				$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
					$bForbidden = true;
				}, $action, $repr);
				$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
			}
		}
	}
	
	public function testWhitelistSpecificRepresentationsWithSpecificActions() {
		//Everything not parcels is forbidden
		//Any parcel action in the list with any representation is allowed
		$conf = array(
			"Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
				"Actions" => array(
					"TESTCONNECTION" => array(),
					"SELECTFEATURES" => array()
				),
				"Representations" => array(
					"xml" => array(),
					"json" => array()
				)
			)
		);
		$mimeType = "text/xml";
		$wl = new MgFeatureSourceWhitelist($conf);
		
		//Check non-parcel FS ids are forbidden
		foreach ($this->testIds as $resIdStr) {
			if ($resIdStr === "Library://Samples/Sheboygan/Data/Parcels.FeatureSource")
				continue;
			foreach ($this->actions as $action) {
				foreach ($this->representations as $repr) {
					$bExpect = true;
					$bForbidden = false;
					$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
						$bForbidden = true;
					}, $action, $repr);
					$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
				}
			}
		}
		//Now focus on parcels
		$resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
		foreach ($this->actions as $action) {
			foreach ($this->representations as $repr) {
				$bExpect = true; 
				if (in_array($action, array("TESTCONNECTION", "SELECTFEATURES"))) {
					if (in_array($repr, array("xml", "json"))) {
						$bExpect = false;
					}
				}
				$bForbidden = false;
				$wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
					$bForbidden = true;
				}, $action, $repr);
				$this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
			}
		}
	}
}

?>