<?php

//
//  Copyright (C) 2023 by Jackie Ng
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

require_once dirname(__FILE__)."/interfaces.php";

class MgReaderAdapter implements IReader {
    private $inner;
    public function __construct(MgReader $inner) {
        $this->inner = $inner;
    }

    public function ReadNext() {
        return $this->inner->ReadNext();
    }
    
    public function GetPropertyType(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetPropertyType($indexOrProp);
    }
    
    public function GetPropertyCount() {
        return $this->inner->GetPropertyCount();
    }
    
    public function GetPropertyName(/*php_int*/ $index) {
        return $this->inner->GetPropertyName($index);
    }
    
    public function GetPropertyIndex(/*php_string*/ $name) {
        return $this->inner->GetPropertyIndex($name);
    }
    
    //public function GetClassDefinition();

    public function GetBoolean(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetBoolean($indexOrProp);
    }

    public function GetByte(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetByte($indexOrProp);
    }

    public function GetDateTime(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetDateTime($indexOrProp);
    }

    public function GetDouble(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetDouble($indexOrProp);
    }

    public function GetGeometry(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetGeometry($indexOrProp);
    }

    public function GetInt16(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetInt16($indexOrProp);
    }

    public function GetInt32(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetInt32($indexOrProp);
    }

    public function GetInt64(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetInt64($indexOrProp);
    }

    public function GetSingle(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetSingle($indexOrProp);
    }

    public function GetString(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->GetString($indexOrProp);
    }

    public function IsNull(/*php_int|php_string*/ $indexOrProp) {
        return $this->inner->IsNull($indexOrProp);
    }
    
    public function Close() {
        $this->inner->Close();
    }
}