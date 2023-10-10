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

require_once dirname(__FILE__)."/../app/util/boxedvalue.php";

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class BoxedValueTest extends TestCase
{
    public function testBoxBoolean() {
        $bvXmlTrue = MgBoxedValue::Boolean(true, "xml");
        $bvXmlFalse = MgBoxedValue::Boolean(false, "xml");
        $bvJsonTrue = MgBoxedValue::Boolean(true, "json");
        $bvJsonFalse = MgBoxedValue::Boolean(false, "json");
        
        $doc = new DOMDocument();
        $doc->loadXML($bvXmlTrue);
        
        $this->assertTrue($doc->getElementsByTagName("Type")->length == 1);
        $this->assertEquals("Boolean", $doc->getElementsByTagName("Type")->item(0)->nodeValue);
        $this->assertTrue($doc->getElementsByTagName("Value")->length == 1);
        $this->assertEquals("true", $doc->getElementsByTagName("Value")->item(0)->nodeValue);
        
        $doc->loadXML($bvXmlFalse);
        
        $this->assertTrue($doc->getElementsByTagName("Type")->length == 1);
        $this->assertEquals("Boolean", $doc->getElementsByTagName("Type")->item(0)->nodeValue);
        $this->assertTrue($doc->getElementsByTagName("Value")->length == 1);
        $this->assertEquals("false", $doc->getElementsByTagName("Value")->item(0)->nodeValue);
        
        $val = json_decode($bvJsonTrue);
        $this->assertEquals("Boolean", $val->PrimitiveValue->Type);
        $this->assertTrue($val->PrimitiveValue->Value);
        
        $val = json_decode($bvJsonFalse);
        $this->assertEquals("Boolean", $val->PrimitiveValue->Type);
        $this->assertFalse($val->PrimitiveValue->Value);
    }
    
    public function testBoxInt32() {
        $bvXmlInt = MgBoxedValue::Int32(1234, "xml");
        $bvJsonInt = MgBoxedValue::Int32(1234, "json");
        
        $doc = new DOMDocument();
        $doc->loadXML($bvXmlInt);
        
        $this->assertTrue($doc->getElementsByTagName("Type")->length == 1);
        $this->assertEquals("Int32", $doc->getElementsByTagName("Type")->item(0)->nodeValue);
        $this->assertTrue($doc->getElementsByTagName("Value")->length == 1);
        $this->assertEquals("1234", $doc->getElementsByTagName("Value")->item(0)->nodeValue);
        
        $val = json_decode($bvJsonInt);
        $this->assertEquals("Int32", $val->PrimitiveValue->Type);
        $this->assertEquals(1234, $val->PrimitiveValue->Value);
    }
    
    public function testBoxInt64() {
        $bvXmlInt = MgBoxedValue::Int64(1234, "xml");
        $bvJsonInt = MgBoxedValue::Int64(1234, "json");
        
        $doc = new DOMDocument();
        $doc->loadXML($bvXmlInt);
        
        $this->assertTrue($doc->getElementsByTagName("Type")->length == 1);
        $this->assertEquals("Int64", $doc->getElementsByTagName("Type")->item(0)->nodeValue);
        $this->assertTrue($doc->getElementsByTagName("Value")->length == 1);
        $this->assertEquals("1234", $doc->getElementsByTagName("Value")->item(0)->nodeValue);
        
        $val = json_decode($bvJsonInt);
        $this->assertEquals("Int64", $val->PrimitiveValue->Type);
        $this->assertEquals(1234, $val->PrimitiveValue->Value);
    }
    
    public function testBoxString() {
        $bvXmlStr = MgBoxedValue::String("foo", "xml");
        $bvJsonStr = MgBoxedValue::String("bar", "json");
        
        $doc = new DOMDocument();
        $doc->loadXML($bvXmlStr);
        
        $this->assertTrue($doc->getElementsByTagName("Type")->length == 1);
        $this->assertEquals("String", $doc->getElementsByTagName("Type")->item(0)->nodeValue);
        $this->assertTrue($doc->getElementsByTagName("Value")->length == 1);
        $this->assertEquals("foo", $doc->getElementsByTagName("Value")->item(0)->nodeValue);
        
        $val = json_decode($bvJsonStr);
        $this->assertEquals("String", $val->PrimitiveValue->Type);
        $this->assertEquals("bar", $val->PrimitiveValue->Value);
    }
}