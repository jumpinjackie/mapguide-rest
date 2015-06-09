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
    
class TestUtils
{
    public static function mockByteReader($testCase, $xml) {
        $stub = $testCase->getMockBuilder("MgByteReader")->getMock();
        $stub->method("GetMimeType")
            ->will($testCase->returnValue("text/xml"));
        $stub->method("ToString")
            ->will($testCase->returnValue($xml));
        return $stub;
    }
}
?>