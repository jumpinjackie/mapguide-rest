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

// TestStubClasses.php
//
// This implements MapGuide class stubs to allow for such class types to be mocked
// by PHPUnit in PHP environments where the MapGuide API does not exist (eg. TravisCI) 

if (!class_exists("MgByteReader")) {
    class MgByteReader {
        public function GetMimeType() {
            
        }
        
        public function ToString() {
            
        }
    }
}
if (!class_exists("MgSite")) {
    class MgSite {
        public function EnumerateGroups() {
            
        }
        public function EnumerateRoles($user) {
            
        }
    }
}
if (!class_exists("MgPropertyType")) {
    class MgPropertyType {
        const Null = 0 ; 
        const Boolean = 1 ; 
        const Byte = 2 ; 
        const DateTime = 3 ; 
        const Single = 4 ; 
        const Double = 5 ; 
        const Int16 = 6 ; 
        const Int32 = 7 ; 
        const Int64 = 8 ; 
        const String = 9 ; 
        const Blob = 10 ; 
        const Clob = 11 ; 
        const Feature = 12 ; 
        const Geometry = 13 ; 
        const Raster = 14 ; 
        const Decimal = 15 ; 
    }
}
if (!class_exists("MgFeaturePropertyType")) {
    class MgFeaturePropertyType
    {
        const DataProperty = 100 ; 
        const ObjectProperty = 101 ; 
        const GeometricProperty = 102 ; 
        const AssociationProperty = 103 ; 
        const RasterProperty = 104 ; 
    }
}