<?php

//
//  Copyright (C) 2015 by Jackie Ng
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

require_once dirname(__FILE__)."/../app/util/whitelist.php";
require_once dirname(__FILE__)."/TestUtils.php";

use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;

class RSWhiteListTest extends TestCase
{
    use AssertIsType;
    private $actions;
    private $representations;
    private $testIds;
    
    public function __construct() {
        $this->actions = array(
            "ENUMERATERESOURCES",
            "ENUMERATERESOURCEDATA",
            "ENUMERATERESOURCEREFERENCES",
            "ENUMERATEUNMANAGEDDATA",
            "GETRESOURCE",
            "GETRESOURCEDATA",
            "GETRESOURCEHEADER",
            "SETRESOUCE",
            "SETRESOURCEDATA",
            "SETRESOURCEHEADER",
            "APPLYRESOURCEPACKAGE",
            "DELETERESOURCE",
            "DELETERESOURCEDATA",
            "COPYRESOURCE",
            "MOVERESOURCE",
            "GETRESOURCEINFO" // specific to mapguide-rest
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
        $this->testFolders = array(
            "Library://Samples/Sheboygan/",
            "Library://Samples/Sheboygan/Data/",
            "Library://Samples/Sheboygan/Layers/"
        );
    }
    
    public function testInactiveWhitelist() {
        $site = $this->getMockBuilder("MgSite")->getMock();
        //An empty configuration means open season
        $conf = array();
        $mimeType = "text/xml";
        $wl = new MgWhitelist($conf);
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
        $wl = new MgWhitelist($conf);
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
        $wl = new MgWhitelist($conf);
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
                    "GETRESOURCE" => array(),
                    "GETRESOURCEDATA" => array()
                ),
                "Representations" => array()
            )
        );
        $mimeType = "text/xml";
        $wl = new MgWhitelist($conf);
        
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
                $bExpect = !in_array($action, array("GETRESOURCE", "GETRESOURCEDATA"));
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
                    "GETRESOURCE" => array(),
                    "GETRESOURCEDATA" => array()
                ),
                "Representations" => array(
                    "xml" => array(),
                    "json" => array()
                )
            )
        );
        $mimeType = "text/xml";
        $wl = new MgWhitelist($conf);
        
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
                if (in_array($action, array("GETRESOURCE", "GETRESOURCEDATA"))) {
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
                    "GETRESOURCE" => array(),
                    "GETRESOURCEDATA" => array()
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
        $wl = new MgWhitelist($conf);
        
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
                    if (in_array($action, array("GETRESOURCE", "GETRESOURCEDATA"))) {
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
                    "GETRESOURCE" => array(
                        "AllowUsers" => array("Author"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Users")
                    ),
                    "GETRESOURCEDATA" => array(
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
        $wl = new MgWhitelist($conf);
        
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
                    if (in_array($action, array("GETRESOURCE", "GETRESOURCEDATA"))) {
                        if (in_array($repr, array("xml", "json"))) {
                            switch ($userName) {
                                case "Author":
                                    $bExpect = false;
                                    break;
                                case "Anonymous":
                                    $bExpect = 
                                        ($action == "GETRESOURCEDATA") ||
                                        ($action == "GETRESOURCE" && $repr == "xml");
                                    break;
                                case "Administrator":
                                    $bExpect = !($action == "GETRESOURCEDATA" && $repr == "xml");
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
        return $op === "APPLYRESOURCEPACKAGE" ||
               $op === "ENUMERATEUNMANAGEDDATA";
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
                    "APPLYRESOURCEPACKAGE" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    ),
                    "ENUMERATEUNMANAGEDDATA" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    )
                )
            )
        );
        $mimeType = "text/xml";
        $wl = new MgWhitelist($conf);
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
    
    public function testWhitelistAclGlobalInheritance() {
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
        
        //Everything not parcels is subject to the global rules
        //Any parcel action in the list with any representation is allowed if the calling user is part of any of the users/groups/roles specified
        $conf = array(
            "Globals" => array(
                "Actions" => array(
                    "GETRESOURCE" => array(
                        "AllowRoles" => array("Author", "Administrator")
                    )
                )
            ),
            "Library://Samples/Sheboygan/Data/Parcels.FeatureSource" => array(
                "Actions" => array(
                    "GETRESOURCE" => array(
                        "AllowUsers" => array("Author"),
                        "AllowGroups" => array("Foo"),
                        "AllowRoles" => array("Users")
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
        $resp = "json";
        $wl = new MgWhitelist($conf);
        
        //Anonymous can't use GETRESOURCE globally
        $action = "GETRESOURCE";
        $userName = "Anonymous";
        $bExpect = true;
        $bForbidden = false;
        $wl->VerifyGlobalWhitelist($mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Author can use GETRESOURCE globally
        $action = "GETRESOURCE";
        $userName = "Author";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyGlobalWhitelist($mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Administrator can use GETRESOURCE globally
        $action = "GETRESOURCE";
        $userName = "Administrator";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyGlobalWhitelist($mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Test on trees. As the configuration has no entry for this, it should default to global configuration
        
        $resIdStr = "Library://Samples/Sheboygan/Data/Trees.FeatureSource";
        //Anonymous can't use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Anonymous";
        $bExpect = true;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Author can use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Author";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Administrator can use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Administrator";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Test on parcels
        
        $resIdStr = "Library://Samples/Sheboygan/Data/Parcels.FeatureSource";
        //Anonymous can use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Anonymous";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Author can use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Author";
        $bExpect = false;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
        
        //Administrator can't use GETRESOURCE on trees
        $action = "GETRESOURCE";
        $userName = "Administrator";
        $bExpect = true;
        $bForbidden = false;
        $wl->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) use (&$bForbidden) {
            $bForbidden = true;
        }, $action, $resp, $site, $userName);
        $this->assertEquals($bExpect, $bForbidden, "Expected (".($bExpect?"true":"false").") on ($action, $resp) for $userName. Got: ".($bForbidden?"true":"false"));
    }
}