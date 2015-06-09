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
    
?>