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

require_once dirname(__FILE__)."/utils.php";

class MgWhitelist
{
    private $conf;
    
    public function __construct($conf) {
        $this->conf = $conf;
    }
    
    /*
    private static function MakeDict($arr, $makeLower = false) {
        $dict = array();
        if (!empty($arr)) {
            foreach ($arr as $value) {
                $key = $makeLower ? strtolower($value) : $value;
                $dict[$key] = $value;
            }
        }
        return $dict;
    }
    */
    
    private static function VerifyWhitelistInternal(array $fsConf, /*php_string*/ $mimeType, /*php_callable*/ $forbiddenAction, /*php_string*/ $requiredAction, /*php_string*/ $requiredRepresentation, /*MgSite*/ $site, /*php_string*/ $userName) {
        $supportedActions = null;
        $supportedRepresentations = null;
        if (!empty($fsConf) && array_key_exists("Actions", $fsConf)) {
            $supportedActions = $fsConf["Actions"];
        }
        if (!empty($fsConf) && array_key_exists("Representations", $fsConf)) {
            $supportedRepresentations = $fsConf["Representations"];
        }
        // If a required features array is passed in, verify against the given configuration, throw on any inconsistencies
        if ($requiredAction != null) {
            if ((!empty($supportedActions) &&                               //Supported actions have been defined for this feature source
                !array_key_exists($requiredAction, $supportedActions))) {   //But that same key is not present on the declared supported actions
                //print ("\nThis resource is not whitelisted for this API operation ($userName): $requiredAction");
                if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                    call_user_func_array($forbiddenAction, array("This action is not whitelisted", $mimeType));
                    return;
                }
            }
            if (!empty($supportedActions) && array_key_exists($requiredAction, $supportedActions)) {
                $acl = $supportedActions[$requiredAction];
                if (!MgUtils::ValidateAcl($userName, $site, $acl)) {
                    if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                        call_user_func_array($forbiddenAction, array("This this action for this user is not whitelisted", $mimeType));
                        return;
                    }
                }
            }
        }
        // Same for representations
        if ($requiredRepresentation != null) {
            if ((!empty($supportedRepresentations) &&                                       //Supported representations have been defined for this feature source
                !array_key_exists($requiredRepresentation, $supportedRepresentations))) {   //But that same key is not present on the declared supported representations
                //print ("\nThis resource is not whitelisted for this requested representation ($userName): $requiredRepresentation");
                if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                    call_user_func_array($forbiddenAction, array("This representation is not whitelisted", $mimeType));
                    return;
                }
            }
            if (!empty($supportedRepresentations) && array_key_exists($requiredRepresentation, $supportedRepresentations)) {
                $acl = $supportedRepresentations[$requiredRepresentation];
                if (!MgUtils::ValidateAcl($userName, $site, $acl)) {
                    if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                        call_user_func_array($forbiddenAction, array("This representation for this user is not whitelisted", $mimeType));
                        return;
                    }
                }   
            }
        }
    }
    
    public function VerifyGlobalWhitelist(/*php_string*/ $mimeType, /*php_callable*/ $forbiddenAction, /*php_callable*/ $requiredAction, /*php_string*/ $requiredRepresentation, /*MgSite*/ $site, /*php_string*/ $userName) {
        if ($this->conf && !empty($this->conf)) {
            $fsConf = null;
            if (!array_key_exists("Globals", $this->conf)) {
                if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                    call_user_func_array($forbiddenAction, array("This operation is not whitelisted", $mimeType));
                    return;
                }
            } else {
                $fsConf = $this->conf["Globals"];
            }
            self::VerifyWhitelistInternal($fsConf, $mimeType, $forbiddenAction, $requiredAction, $requiredRepresentation, $site, $userName);
        }
    }
    
    public function VerifyWhitelist(/*php_string*/ $resIdStr, /*php_string*/ $mimeType, /*php_callable*/ $forbiddenAction, /*php_callable*/ $requiredAction, /*php_string*/ $requiredRepresentation, /*MgSite*/ $site, /*php_string*/ $userName) {
        if ($this->conf && !empty($this->conf)) {
            $fsConf = null;
            //If resource configuration does not exist, then fall back to the global one
            if (!array_key_exists($resIdStr, $this->conf)) {
                if (!array_key_exists("Globals", $this->conf)) {
                    if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                        call_user_func_array($forbiddenAction, array("This resource is not whitelisted for this operation", $mimeType));
                        return;
                    }
                }
                $fsConf = $this->conf["Globals"];
            } else {
                $fsConf = $this->conf[$resIdStr];
            }
            self::VerifyWhitelistInternal($fsConf, $mimeType, $forbiddenAction, $requiredAction, $requiredRepresentation, $site, $userName);
        }
    }
}