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

require_once dirname(__FILE__)."/../app/adapters/templateadapter.php";
require_once dirname(__FILE__)."/TestUtils.php";

class MockIDProperty
{
    public function GetName() {
        return "ID";
    }
    
    public function GetDataType() {
        return MgPropertyType::Int32;
    }
}

class MockPropertyDefinitionCollection
{
    public function GetCount() {
        return 1;
    }
    
    public function GetItem($i) {
        if ($i == 0) {
            return new MockIDProperty();
        }
    }
}

class MockClass
{
    public function GetName() {
        return "Test";
    }
    
    public function GetProperties() {
        return new MockPropertyDefinitionCollection();
    }
    
    public function GetIdentityProperties() {
        return new MockPropertyDefinitionCollection();
    }
}

class MockReader
{
    private $i;
    private $data;
    private $closed;
    
    public function __construct() {
        $this->i = -1;
        $this->data = array(0, 1, 2, 3, 4);
        $this->closed = false;
    }
    
    public function ReadNext() {
        $this->i += 1;
        return $this->i < count($this->data);
    }
    
    public function GetPropertyType($indexOrProp) {
        if ($indexOrProp == 0 || $indexOrProp == "ID")
            return MgPropertyType::Int32;
        else
            throw new Exception("Invalid property index or name");
    }
    
    public function GetPropertyCount() {
        return 1;
    }
    
    public function GetPropertyIndex($name) {
        if ($name == "ID") {
            return 0;
        }
        return -1;
    }
    
    public function GetClassDefinition() {
        return new MockClass();
    }
    
    public function GetInt32($indexOrProp) {
        if ($indexOrProp == 0 || $indexOrProp == "ID")
            return $this->data[$this->i];
        else
            throw new Exception("Invalid property index or name");
    }
    
    public function IsNull($indexOrProp) {
        if ($indexOrProp == 0 || $indexOrProp == "ID")
            return false;
        else
            throw new Exception("Invalid property index or name");
    }
    
    public function WasClosed() {
        return $this->closed;
    }
    
    public function Close() {
        $this->closed = true;
    }
}

class MockFormatterSet
{
    public function GetFormatter($formatterName) {
        return null;
    }
}

class FeatureReaderModelTest extends PHPUnit_Framework_TestCase
{
    public function testMockReader() {
        //Test our mock is in good order before feeding it to our 
        //reader model
        $rdr = new MockReader();
        $i = 0;
        $this->assertEquals(1, $rdr->GetPropertyCount());
        $this->assertEquals(MgPropertyType::Int32, $rdr->GetPropertyType(0));
        $this->assertEquals(MgPropertyType::Int32, $rdr->GetPropertyType("ID"));
        $clsDef = $rdr->GetClassDefinition();
        $props = $clsDef->GetProperties();
        $this->assertEquals(1, $props->GetCount());
        $this->assertEquals("ID", $props->GetItem(0)->GetName());
        $this->assertEquals(MgPropertyType::Int32, $props->GetItem(0)->GetDataType());
        while ($rdr->ReadNext()) {
            $this->assertEquals($i, $rdr->GetInt32(0));
            $this->assertEquals($i, $rdr->GetInt32("ID"));
            try {
                $rdr->GetInt32(-1);
                $this->fail("Expected GetInt32 failure");
            } catch (Exception $ex) { }
            try {
                $rdr->GetInt32(1);
                $this->fail("Expected GetInt32 failure");
            } catch (Exception $ex) { }
            try {
                $rdr->GetInt32("IDontExist");
                $this->fail("Expected GetInt32 failure");
            } catch (Exception $ex) { }
            $i++;
        }
        $rdr->Close();
        $this->assertTrue($rdr->WasClosed());
        $this->assertEquals(5, $i);
    }
    
    public function testModelIteration() {
        $rdr = new MockReader();
        $model = new MgFeatureReaderModel(new MockFormatterSet(), $rdr, -1, 0);
        $i = 0;
        while ($model->Next()) {
            $feat = $model->Current();
            $this->assertEquals($i, $feat->ID);
            $i++;
        }
        $model->Done();
        $this->assertTrue($rdr->WasClosed());
        $this->assertEquals(5, $i);
    }
    
    public function testModelPeek() {
        $rdr = new MockReader();
        $model = new MgFeatureReaderModel(new MockFormatterSet(), $rdr, -1, 0);
        $i = 0;
        while ($model->Peek()) {
            $feat = $model->Current();
            $this->assertEquals($i, $feat->ID);
            $i++;
        }
        $model->Done();
        $this->assertTrue($rdr->WasClosed());
        $this->assertEquals(5, $i);
    }
    
    public function testModelPeekIterationMixture() {
        $rdr = new MockReader();
        $model = new MgFeatureReaderModel(new MockFormatterSet(), $rdr, -1, 0);
        
        //Peek 1st record
        $this->assertTrue($model->Peek());
        $this->assertEquals(0, $model->Current()->ID);
        $this->assertTrue($model->Next());
        $this->assertEquals(0, $model->Current()->ID);
        $this->assertTrue($model->Next());
        $this->assertEquals(1, $model->Current()->ID);
        $this->assertTrue($model->Next());
        $this->assertEquals(2, $model->Current()->ID);
        //Peek 4th record
        $this->assertTrue($model->Peek());
        $this->assertEquals(3, $model->Current()->ID);
        $this->assertTrue($model->Next());
        $this->assertEquals(3, $model->Current()->ID);
        $this->assertTrue($model->Next());
        $this->assertEquals(4, $model->Current()->ID);
        //Peek end of reader
        $this->assertFalse($model->Peek());
        $this->assertFalse($model->Next());
    }
}

?>