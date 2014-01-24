<?php

class MgUtils
{
    public static function ParseLibraryResourceID($parts, $stopAt = null) {
        $appendSlash = false;
        $count = count($parts);
        if ($stopAt != null) {
            $newParts = array();
            for ($i = 0; $i < $count; $i++) {
                if ($parts[$i] === $stopAt) {
                    break;
                } else {
                    array_push($newParts, $parts[$i]);
                }
            }
            $parts = $newParts;
            $count = count($parts);
        }
        if ($count > 0) {
            $lastPart = $parts[$count - 1];
            //If the last part is not a known resource extension, append a slash to indicate a folder
            if (!MgUtils::StringEndsWith($lastPart, ".".MgResourceType::FeatureSource) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::LayerDefinition) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::MapDefinition) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::WebLayout) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::ApplicationDefinition) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::SymbolDefinition) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::DrawingSource) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::PrintLayout) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::SymbolLibrary) &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::LoadProcedure)) {
                $appendSlash = true;
            }
        }

        $resIdStr = "Library://".implode("/", $parts);
        if ($appendSlash === true)
            $resIdStr .= "/";
        return new MgResourceIdentifier($resIdStr);
    }

    public static function StringEndsWith($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) return false;
        return substr_compare($string, $test, -$testlen) === 0;
    }

    public static function EscapeJsonString($str) {
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $str);
        return $result;
    }

    public static function EscapeXmlChars($str) {
        $str = str_replace("&", "&amp;", $str);
        $str = str_replace("'", "&apos;", $str);
        $str = str_replace(">", "&gt;", $str);
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace("\"", "&quot;", $str);
        return $str;
    }

    private static function DomElementToJson($domElement) {
        $result = '';
        if ($domElement->nodeType == XML_COMMENT_NODE) {
            return '';
        }
        if ($domElement->nodeType == XML_TEXT_NODE) {
            /* text node, just return content */
            $text = trim($domElement->textContent);
            $text = addslashes($text);
            if ($text != '') {
                $result = '"'.$text.'"';
            } else {
                $text = '""';
            }
        } else {
            /* some other kind of node, needs to be processed */
            
            $aChildren = array();
            $aValues = array();
            
            /* attributes are considered child nodes with a special key name
               starting with @ */
            if ($domElement->hasAttributes()) {
                foreach($domElement->attributes as $key => $attr) {
                    $len = array_push($aValues, array('"'.$attr->value.'"'));
                    $aChildren['@'.$key] = $len-1;
                }
            }
            
            if ($domElement->hasChildNodes()) {
                //has children
                foreach($domElement->childNodes as $child) {
                    if ($child->nodeType == XML_COMMENT_NODE) {
                        continue;
                    }
                    if ($child->nodeType == XML_TEXT_NODE) {
                        $text = trim($child->textContent);
                        $text = addslashes($text);
                        if ($text == '') {
                            continue;
                        }
                        array_push($aValues, array('"'.$text.'"'));
                    } else {
                        $childTag = $child->tagName;
                        $json = MgUtils::DomElementToJson($child);
                        if ($json == '') {
                            continue;
                        }
                        if (array_key_exists($childTag, $aChildren)) {
                            array_push($aValues[$aChildren[$childTag]], $json);
                        } else {
                            $len = array_push($aValues, array($json));
                            $aChildren[$childTag] = $len - 1;
                        }
                    }
                }
            }
            
            $nChildren = count($aChildren);
            $nValues = count($aValues);
            
            if ($nChildren == 0 && $nValues == 0) {
                return '';
            }
            
            if ($nValues == 1 && $nChildren == 0) {
                $result .= $aValues[0][0];
            } else {
                $bIsObject = true;
                if ($nChildren != $nValues) {
                    $bIsObject = false;
                }
                $result .= $bIsObject ? '{' : '[';
            
                $sep = '';
                $aChildren = array_flip($aChildren);
                for ($i=0; $i<$nValues; $i++) {
                    $aValue = $aValues[$i];
                    $result .= $sep;
                
                    if (isset($aChildren[$i])) {
                        if (!$bIsObject) {
                            $result .= '{';
                        }
                        $result .= '"'.$aChildren[$i].'":';
                    }
                    //if (count($aValue) > 1) {
                        $result .= '[';
                        $result .= implode(',', $aValue);
                        $result .= ']';
                    //} else {
                    //    $result .= $aValue[0];
                    //}
                    if (isset($aChildren[$i]) && !$bIsObject) {
                        $result .= '}';
                    }
                    $sep = ',';
                }
                $result .= $bIsObject ? '}' : ']';
            }
            
        }
        return $result;
    }

    public static function Xml2Json($xml) {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $root = $doc->documentElement;
        echo '{"' . $root->tagName . '":' . MgUtils::DomElementToJson($root) . '}'; 
    }
}

?>