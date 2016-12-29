<?php

require_once dirname(__FILE__)."/TestUtils.php";
require_once dirname(__FILE__)."/../app/util/utils.php";

class AclTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyAcl() {
        $site = $this->getMockBuilder("MgSite")->getMock();
        $this->assertTrue(MgUtils::ValidateAcl("Anonymous", $site, array()));
    }
    
    public function testUserInAcl() {
        $site = $this->getMockBuilder("MgSite")->getMock();
        $this->assertTrue(MgUtils::ValidateAcl("Anonymous", $site, array(
            "AllowUsers" => array("Anonymous")
        )));
        $this->assertFalse(MgUtils::ValidateAcl("Anonymous", $site, array(
            "AllowUsers" => array("Administrator")
        )));
    }
    
    public function testGroupInAcl() {
        $groupXml = '<?xml version="1.0" encoding="UTF-8"?>
<GroupList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="GroupList-1.0.0.xsd">
    <Group>
        <Name>Everyone</Name>
        <Description>Built-in group to include all users</Description>
    </Group>
</GroupList>';
        $br = TestUtils::mockByteReader($this, $groupXml);
        $this->assertEquals("text/xml", $br->GetMimeType());
        $this->assertEquals($groupXml, $br->ToString());
        $site = $this->getMockBuilder("MgSite")->getMock();
        $site->method("EnumerateGroups")
            ->will($this->returnValue($br));
        $conf1 = array(
            "AllowUsers" => array("Anonymous"),
            "AllowGroups" => array("Everyone")
        );
        $this->assertTrue(MgUtils::ValidateAcl("Anonymous", $site, $conf1));
        $conf2 = array(
            "AllowUsers" => array("Administrator"),
            "AllowGroups" => array("Foo")
        );
        $this->assertFalse(MgUtils::ValidateAcl("Anonymous", $site, $conf2));
    }
    
    public function testRoleInAcl() {
        $groupXml = '<?xml version="1.0" encoding="UTF-8"?>
<GroupList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="GroupList-1.0.0.xsd">
    <Group>
        <Name>Everyone</Name>
        <Description>Built-in group to include all users</Description>
    </Group>
</GroupList>';
        $br = TestUtils::mockByteReader($this, $groupXml);
        $this->assertEquals("text/xml", $br->GetMimeType());
        $this->assertEquals($groupXml, $br->ToString());
        $site = $this->getMockBuilder("MgSite")->getMock();
        $site->method("EnumerateGroups")
            ->will($this->returnValue($br));
        
        $roleMethodMap = array(
            array("Author", new FakeStringCollection(array("Authors"))),
            array("Anonymous", new FakeStringCollection(array("Users")))	
        );
        $site->method("EnumerateRoles")
            ->will($this->returnValueMap($roleMethodMap));
        
        $conf1 = array(
            "AllowUsers" => array("Administrator"),
            "AllowGroups" => array("Foo"),
            "AllowRoles" => array("Users")
        );
        $this->assertFalse(MgUtils::ValidateAcl("Author", $site, $conf1));
        $conf2 = array(
            "AllowUsers" => array("Administrator"),
            "AllowGroups" => array("Foo"),
            "AllowRoles" => array("Users")
        );
        $this->assertTrue(MgUtils::ValidateAcl("Anonymous", $site, $conf2));
    }
}