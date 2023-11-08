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

require_once dirname(__FILE__)."/TestStubClasses.php";

//Mimics a MgStringCollection
class FakeStringCollection
{
    private $values;
    
    public function __construct($vals) {
        $this->values = $vals;
    }
    
    public function IndexOf($val) {
        for ($i = 0; $i < count($this->values); $i++) {
            if ($val === $this->values[$i])
                return $i;
        }
        return -1;
    }
}

class FakeByteReader {
    private $mimeType;
    private $content;
    public function __construct($mimeType, $content) {
        $this->mimeType = $mimeType;
        $this->content = $content;
    }
    public function GetMimeType() { return $this->mimeType; }
    public function ToString() { return $this->content; }
}

class FakeSite {
    private $groupBr;
    private $roleMap;
    public function __construct($groupBr, $roleMap = null) {
        $this->groupBr = $groupBr;
        $this->roleMap = $roleMap;
        if ($this->roleMap == null) {
            $this->roleMap = array(
                "Author" => new FakeStringCollection(array()),
                "Anonymous" => new FakeStringCollection(array()),
                "Administrator" => new FakeStringCollection(array())
            );
        } else {
            $this->roleMap = $roleMap;
        }
    }
    public function EnumerateGroups($userName) { return $this->groupBr; }
    public function EnumerateRoles($arg) {
        if (array_key_exists($arg, $this->roleMap)) {
            return $this->roleMap[$arg];
        }
        return new FakeStringCollection(array());
    }
}
    
class TestUtils
{
    public static function mockSite($testCase, $groupBr, $roleMap) {
        $stub = new FakeSite($groupBr, $roleMap);
        return $stub;
    }

    public static function mockByteReader($testCase, $xml) {
        $stub = new FakeByteReader("text/xml", $xml);
        return $stub;
    }
}