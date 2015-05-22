<?php

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