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

require_once dirname(__FILE__)."/../app/util/localizer.php";

class LocalizerTest extends PHPUnit_Framework_TestCase
{
    public function testGetText() {
        $strings = array(
            "FOO_BAR" => "Foo Bar"
        );
        $loc = new Localizer($strings);
        $this->assertEquals("Foo Bar", $loc->getText("FOO_BAR"));
    }
    
    public function testGetTextWithParams() {
        $strings = array(
            "FOO_BAR" => "Foo Bar: %s",
            "ABC" => "A %s %d %s"
        );
        $loc = new Localizer($strings);
        $this->assertEquals("Foo Bar: A-OK", $loc->getText("FOO_BAR", "A-OK"));
        $this->assertEquals("A b 1 c", $loc->getText("ABC", "b", 1, "c"));
    }
    
    public function testGetTextNonExistentKey() {
        $strings = array();
        $loc = new Localizer($strings);
        $this->assertEquals("FOO_BAR", $loc->getText("FOO_BAR"));
    }
    
    public function testGetTextNonExistentKeyWithParams() {
        $strings = array();
        $loc = new Localizer($strings);
        $this->assertEquals("FOO_BAR [a,1,b,2]", $loc->getText("FOO_BAR", "a", 1, "b", 2));
    }
}