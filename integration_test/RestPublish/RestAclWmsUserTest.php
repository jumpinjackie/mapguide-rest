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

class RestAclWmsUserTest extends RestPublishingTest {
    public function testXml() {
        $this->__testACL(array(72, 73, 1237), "xml", "wmsuser", Configuration::MIME_XML);
    }
    public function testJson() {
        $this->__testACL(array(87, 88, 2385), "json", "wmsuser", Configuration::MIME_JSON);
    }
}

?>