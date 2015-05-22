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
        $site = $this->getMockBuilder("MgSite")->getMock();
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
                    }, $action, $repr, $site, "Anonymous");
                    $this->assertFalse($bForbidden);
                }
            }
        }
    }
    
    public function testWhitelistOnlyFeatureSource() {
        $site = $this->getMockBuilder("MgSite")->getMock();
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
                    }, $action, $repr, $site, "Anonymous");
                    //print "\nForbidden result: ".($bForbidden ? 1 : 0);
                    $this->assertEquals($expect[$resIdStr], $bForbidden, "Expected $resIdStr to be forbidden: ".($expect[$resIdStr]?"true":"false"));
                }
            }
        }
    }
    
    public function testWhitelistAllActionsXmlOnly() {
        $site = $this->getMockBuilder("MgSite")->getMock();
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
                    }, $action, $repr, $site, "Anonymous");
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
                }, $action, $repr, $site, "Anonymous");
                $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
            }
        }
    }
    
    public function testWhitelistAllRepresentationsWithSpecificActions() {
        $site = $this->getMockBuilder("MgSite")->getMock();
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
                    }, $action, $repr, $site, "Anonymous");
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
                }, $action, $repr, $site, "Anonymous");
                $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
            }
        }
    }
    
    public function testWhitelistSpecificRepresentationsWithSpecificActions() {
        $site = $this->getMockBuilder("MgSite")->getMock();
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
                    }, $action, $repr, $site, "Anonymous");
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
                }, $action, $repr, $site, "Anonymous");
                $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
            }
        }
    }
    
    public function testWhitelistSpecificRepresentationsWithAcls()
    {
        $everyoneGroupXml = '<?xml version="1.0" encoding="UTF-8"?>
<GroupList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="GroupList-1.0.0.xsd">
    <Group>
        <Name>Everyone</Name>
        <Description>Built-in group to include all users</Description>
    </Group>
</GroupList>';
        $everyoneGroupBr = TestUtils::mockByteReader($this, $everyoneGroupXml);
        
        $this->assertEquals("text/xml", $everyoneGroupBr->GetMimeType());
        $this->assertEquals($everyoneGroupXml, $everyoneGroupBr->ToString());
        $site = $this->getMockBuilder("MgSite")->getMock();
        
        $site->method("EnumerateGroups")
            ->will($this->returnValue($everyoneGroupBr));
        
        $roleMethodMap = array(
            array("Author", new FakeStringCollection(array("Authors"))),
            array("Anonymous", new FakeStringCollection(array("Users"))),
            array("Administrator", new FakeStringCollection(array("Administrator")))
        );
        $site->method("EnumerateRoles")
            ->will($this->returnValueMap($roleMethodMap));
        
        //Everything not parcels is forbidden
        //Any parcel action in the list with any representation is allowed if the calling user is part of any of the users/groups/roles specified
        $conf = array(
            "Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
                "Actions" => array(
                    "TESTCONNECTION" => array(),
                    "SELECTFEATURES" => array()
                ),
                "Representations" => array(
                    "xml" => array(
                        "AllowUsers" => array("Administrator"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Authors")
                    ),
                    "json" => array(
                        "AllowUsers" => array("Author"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Users")
                    )
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
                    }, $action, $repr, $site, "Anonymous");
                    $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
                }
            }
        }
        //Now focus on parcels
        $resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
        $users = array("Author", "Anonymous", "Administrator");
        foreach ($users as $userName) {
            foreach ($this->actions as $action) {
                foreach ($this->representations as $repr) {
                    $bExpect = true; 
                    if (in_array($action, array("TESTCONNECTION", "SELECTFEATURES"))) {
                        if (in_array($repr, array("xml", "json"))) {
                            switch ($userName) {
                                case "Author":
                                    $bExpect = false;
                                    break;
                                case "Anonymous":
                                    $bExpect = ($repr == "xml");
                                    break;
                                default:
                                    $bExpect = ($repr == "json");
                                    break;	
                            }
                        }
                    }
                    $bForbidden = false;
                    $wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
                        $bForbidden = true;
                    }, $action, $repr, $site, $userName);
                    $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden for ($userName): ".($bExpect?"true":"false"));
                }
            }	
        }
    }
    
    public function testWhitelistSpecificRepresentationsAndActionsWithAcls()
    {
        $everyoneGroupXml = '<?xml version="1.0" encoding="UTF-8"?>
<GroupList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="GroupList-1.0.0.xsd">
    <Group>
        <Name>Everyone</Name>
        <Description>Built-in group to include all users</Description>
    </Group>
</GroupList>';
        $everyoneGroupBr = TestUtils::mockByteReader($this, $everyoneGroupXml);
        
        $this->assertEquals("text/xml", $everyoneGroupBr->GetMimeType());
        $this->assertEquals($everyoneGroupXml, $everyoneGroupBr->ToString());
        $site = $this->getMockBuilder("MgSite")->getMock();
        
        $site->method("EnumerateGroups")
            ->will($this->returnValue($everyoneGroupBr));
        
        $roleMethodMap = array(
            array("Author", new FakeStringCollection(array("Authors"))),
            array("Anonymous", new FakeStringCollection(array("Users"))),
            array("Administrator", new FakeStringCollection(array("Administrator")))
        );
        $site->method("EnumerateRoles")
            ->will($this->returnValueMap($roleMethodMap));
        
        //Everything not parcels is forbidden
        //Any parcel action in the list with any representation is allowed if the calling user is part of any of the users/groups/roles specified
        $conf = array(
            "Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
                "Actions" => array(
                    "TESTCONNECTION" => array(
                        "AllowUsers" => array("Author"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Users")
                    ),
                    "SELECTFEATURES" => array(
                        "AllowUsers" => array("Administrator"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Authors")
                    )
                ),
                "Representations" => array(
                    "xml" => array(
                        "AllowUsers" => array("Administrator"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Authors")
                    ),
                    "json" => array(
                        "AllowUsers" => array("Author"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Users")
                    )
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
                    }, $action, $repr, $site, "Anonymous");
                    $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden: ".($bExpect?"true":"false"));
                }
            }
        }
        //Now focus on parcels
        $resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
        $users = array("Author", "Anonymous", "Administrator");
        foreach ($users as $userName) {
            foreach ($this->actions as $action) {
                foreach ($this->representations as $repr) {
                    $bExpect = true; 
                    if (in_array($action, array("TESTCONNECTION", "SELECTFEATURES"))) {
                        if (in_array($repr, array("xml", "json"))) {
                            switch ($userName) {
                                case "Author":
                                    $bExpect = false;
                                    break;
                                case "Anonymous":
                                    $bExpect = 
                                        ($action == "SELECTFEATURES") ||
                                        ($action == "TESTCONNECTION" && $repr == "xml");
                                    break;
                                case "Administrator":
                                    $bExpect = !($action == "SELECTFEATURES" && $repr == "xml");
                                    break;	
                            }
                        }
                    }
                    $bForbidden = false;
                    $wl->VerifyWhitelist($resIdStr, $mimeType, function () use (&$bForbidden) {
                        $bForbidden = true;
                    }, $action, $repr, $site, $userName);
                    $this->assertEquals($bExpect, $bForbidden, "Expected $resIdStr ($action, $repr) to be forbidden for ($userName): ".($bExpect?"true":"false"));
                }
            }	
        }
    }
    
    private static function isGlobalOperation($op) {
        return $op === "GETCONNECTIONPROPERTYVALUES" ||
               $op === "ENUMERATEDATASTORES" ||
               $op === "GETPROVIDERCAPABILITIES" ||
               $op === "GETFEATUREPROVIDERS" ||
               $op === "GETSCHEMAMAPPING";
    }
    
    public function testWhitelistGlobalsWithAcls()
    {
        $everyoneGroupXml = '<?xml version="1.0" encoding="UTF-8"?>
<GroupList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="GroupList-1.0.0.xsd">
    <Group>
        <Name>Everyone</Name>
        <Description>Built-in group to include all users</Description>
    </Group>
</GroupList>';
        $everyoneGroupBr = TestUtils::mockByteReader($this, $everyoneGroupXml);
        
        $this->assertEquals("text/xml", $everyoneGroupBr->GetMimeType());
        $this->assertEquals($everyoneGroupXml, $everyoneGroupBr->ToString());
        $site = $this->getMockBuilder("MgSite")->getMock();
        
        $site->method("EnumerateGroups")
            ->will($this->returnValue($everyoneGroupBr));
        
        $roleMethodMap = array(
            array("Author", new FakeStringCollection(array("Author"))),
            array("Anonymous", new FakeStringCollection(array("Users"))),
            array("Administrator", new FakeStringCollection(array("Administrator")))
        );
        $site->method("EnumerateRoles")
            ->will($this->returnValueMap($roleMethodMap));
        
        //Everything not parcels is forbidden
        //Any parcel action in the list with any representation is allowed if the calling user is part of any of the users/groups/roles specified
        $conf = array(
            "Globals" => array(
                "Actions" => array(
                    "GETCONNECTIONPROPERTYVALUES" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    ),
                    "ENUMERATEDATASTORES" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    ),
                    "GETPROVIDERCAPABILITIES" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    ),
                    "GETFEATUREPROVIDERS" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    ),
                    "GETSCHEMAMAPPING" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    )
                )
            )
        );
        $mimeType = "text/xml";
        $wl = new MgFeatureSourceWhitelist($conf);
        $users = array("Administrator", "Author", "Anonymous");
        foreach ($users as $userName) {
            foreach ($this->actions as $action) {
                $bExpect = ($userName == "Anonymous" || !self::isGlobalOperation($action));
                $resp = "json";
                $bForbidden = false;
                $wl->VerifyGlobalWhitelist($mimeType, function($msg, $mt) use (&$bForbidden) {
                    $bForbidden = true;
                }, $action, $resp, $site, $userName);
                $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
            }
        }
    }
}

?>