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

class MgFeatureSourceWhitelist
{
	private $conf;
	
	public function __construct($conf) {
		$this->conf = $conf;
	}
	
    private static function MakeDict($arr, $makeLower = false) {
        $dict = array();
        foreach ($arr as $value) {
            $dict[$makeLower ? strtolower($value) : $value] = $value;
        }
        return $dict;
    }
    
	public function VerifyWhitelist($resIdStr, $mimeType, $forbiddenAction, $requiredAction = null, $requiredRepresentation = null) {
        if ($this->conf && !empty($this->conf)) {
            $fsConf = null;
            if (!array_key_exists($resIdStr, $this->conf)) {
                if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                    call_user_func_array($forbiddenAction, array("This resource is not whitelisted for this operation", $mimeType));
                    return;
                }
            } else {
                $fsConf = $this->conf[$resIdStr];
            }
            $supportedActions = null;
            $supportedRepresentations = null;
            if (!empty($fsConf) && array_key_exists("Actions", $fsConf)) {
                $supportedActions = self::MakeDict($fsConf["Actions"]);
            }
            if (!empty($fsConf) && array_key_exists("Representations", $fsConf)) {
                $supportedRepresentations = self::MakeDict($fsConf["Representations"], true);
            }
            // If a required features array is passed in, verify against the given configuration, throw on any inconsistencies
            if ($requiredAction != null) {
                if ((!empty($supportedActions) &&                                    //Supported actions have been defined for this feature source
                            !array_key_exists($requiredAction, $supportedActions))) {   //But that same key is not present on the declared supported actions
                    if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                        call_user_func_array($forbiddenAction, array("This resource is not whitelisted for this action", $mimeType));
                        return;
                    }
                    //$this->Forbidden("This resource is not whitelisted for this API operation: " . $requiredAction . ". Supported actions: " . print_r($supportedActions, true), $mimeType);
                }
            }
            // Same for representations
            if ($requiredRepresentation != null) {
                if ((!empty($supportedRepresentations) &&                                           //Supported representations have been defined for this feature source
                            !array_key_exists($requiredRepresentation, $supportedRepresentations))) {   //But that same key is not present on the declared supported representations
                    if ($forbiddenAction != null && is_callable($forbiddenAction)) {
                        call_user_func_array($forbiddenAction, array("This resource is not whitelisted for this representation", $mimeType));
                        return;
                    }
                    //$this->Forbidden("This resource is not whitelisted for this requested representation: " . $requiredRepresentation . ". Supported representations: " . print_r($supportedRepresentations, true), $mimeType);
                }
            }
        }
	}
}

?>