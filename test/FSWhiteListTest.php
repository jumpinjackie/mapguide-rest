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
	
	public function testBasicWhitelist() {
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
					$this->assertEquals($expect[$resIdStr], $bForbidden, "Expected $resIdStr to be forbidden: ".$expect[$resIdStr]);
				}
			}
		}
	}
}

?>