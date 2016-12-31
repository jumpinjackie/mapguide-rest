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
require_once dirname(__FILE__)."/RestPublishingTest.php";
require_once dirname(__FILE__)."/../Config.php";

class RestAclGroupTest extends RestPublishingTest {
    protected function getFolderName($username) {
        return "test_group";
    }
    public function testXml() {
        $this->__testACL(array(123, 215, 1327), "xml", "user1", Configuration::MIME_XML);
        $this->__testACL(array(124, 216, 1328), "xml", "user2", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testACL(array(45, 345, 2315), "json", "user1", Configuration::MIME_JSON);
        $this->__testACL(array(46, 346, 2316), "json", "user2", Configuration::MIME_JSON);
    }
}