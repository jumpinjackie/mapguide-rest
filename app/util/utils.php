<?php

//
//  Copyright (C) 2014 by Jackie Ng
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

    public static function StringStartsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function StringEndsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
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

    public static function XslTransformByteReader($byteReader, $xslStylesheet, $xslParams) {
        $xslPath = dirname(__FILE__)."/../res/xsl/$xslStylesheet";
        
        $xsl = new DOMDocument();
        $xsl->load($xslPath);

        $doc = new DOMDocument();
        $doc->loadXML($byteReader->ToString());

        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);

        foreach ($xslParams as $key => $value) {
            $xslt->setParameter('', $key, $value);
        }

        return $xslt->transformToXml($doc);
    }

    private static function ParseFeatureNode($propNodes, $agfRw, $wktRw, $classProps) {
        $props = new MgPropertyCollection();
        for ($j = 0; $j < $propNodes->length; $j++) {
            $propNode = $propNodes->item($j);

            $name = $propNode->getElementsByTagName("Name")->item(0)->nodeValue;
            $valueNodes = $propNode->getElementsByTagName("Value");
            $value = "";
            $bNull = true;
            if ($valueNodes->length == 1) {
                $value = $valueNodes->item(0)->nodeValue;
                $bNull = false;
            } else {
                $bNull = true;
            }

            $pidx = $classProps->IndexOf($name);
            if ($pidx >= 0) {
                $propDef = $classProps->GetItem($pidx);
                if ($propDef->GetPropertyType() == MgFeaturePropertyType::GeometricProperty) {
                    $geom = $wktRw->Read($value);
                    $agf = $agfRw->Write($geom);

                    $geomVal = new MgGeometryProperty($name, $agf);
                    $props->Add($geomVal);
                } else if ($propDef->GetPropertyType() == MgFeaturePropertyType::DataProperty) {
                    $dataType = $propDef->GetDataType();
                    switch ($dataType) {
                        case MgPropertyType::Boolean:
                            {
                                if ($bNull) {
                                    $boolVal = new MgBooleanProperty($name, false);
                                    $boolVal->SetNull();
                                } else {
                                    $boolVal = new MgBooleanProperty($name, boolval($value));
                                }
                                $props->Add($boolVal);
                            }
                            break;
                        case MgPropertyType::Byte:
                            {
                                if ($bNull) {
                                    $byteVal = new MgByteProperty($name, 0);
                                    $byteVal->SetNull();
                                } else {
                                    $byteVal = new MgByteProperty($name, intval($value));
                                }
                                $props->Add($byteVal);
                            }
                            break;
                        case MgPropertyType::DateTime:
                            {
                                throw new Exception("Case not supported yet: DateTime"); //TODO: Localize
                            }
                            break;
                        case MgPropertyType::Decimal:
                        case MgPropertyType::Double:
                            {
                                if ($bNull) {
                                    $doubleVal = new MgDoubleProperty($name, 0.0);
                                    $doubleVal->SetNull();
                                } else {
                                    $doubleVal = new MgDoubleProperty($name, floatval($value));
                                }
                                $props->Add($doubleVal);
                            }
                            break;
                        case MgPropertyType::Int16:
                            {
                                if ($bNull) {
                                    $i16val = new MgInt16Property($name, 0);
                                    $i16val->SetNull();
                                } else {
                                    $i16val = new MgInt16Property($name, intval($value));
                                }
                                $props->Add($i16prop);
                            }
                            break;
                        case MgPropertyType::Int32:
                            {
                                if ($bNull) {
                                    $i32val = new MgInt32Property($name, 0);
                                    $i32val->SetNull();
                                } else {
                                    $i32val = new MgInt32Property($name, intval($value));
                                }
                                $props->Add($i32prop);   
                            }
                            break;
                        case MgPropertyType::Int64:
                            {
                                if ($bNull) {
                                    $i64val = new MgInt64Property($name, 0);
                                    $i64val->SetNull();
                                } else {
                                    $i64val = new MgInt64Property($name, intval($value));
                                }
                                $props->Add($i64prop);
                            }
                            break;
                        case MgPropertyType::Single:
                            {
                                if ($bNull) {
                                    $sinProp = new MgSingleProperty($name, 0.0);
                                    $sinProp->SetNull();
                                } else {
                                    $sinProp = new MgSingleProperty($name, floatval($value));
                                }
                                $props->Add($sinProp);
                            }
                            break;
                        case MgPropertyType::String:
                            {
                                if ($bNull) {
                                    $strProp = new MgStringProperty($name, "");
                                    $strProp->SetNull();
                                } else {
                                    $strProp = new MgStringProperty($name, $value);
                                }
                                $props->Add($strProp);
                            }
                            break;
                    }
                }
            }
        }
        return $props;
    }

    public static function ParseMultiFeatureXml($classDef, $xml, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        return MgUtils::ParseMultiFeatureDocument($classDef, $doc, $featureNodeName, $propertyNodeName);
    }

    public static function ParseMultiFeatureDocument($classDef, $doc, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $batchProps = new MgBatchPropertyCollection();
        $featureNodes = $doc->getElementsByTagName($featureNodeName);

        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();
        $classProps = $classDef->GetProperties();

        for ($i = 0; $i < $featureNodes->length; $i++) {
            $propNodes = $featureNodes->item($i)->getElementsByTagName($propertyNodeName);
            $props = MgUtils::ParseFeatureNode($propNodes, $agfRw, $wktRw, $classProps);
            $batchProps->Add($props);
        }

        return $batchProps;
    }

    public static function ParseSingleFeatureDocument($classDef, $doc, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();
        $classProps = $classDef->GetProperties();

        $props = new MgPropertyCollection();
        $featureNodes = $doc->GetElementsByTagName($featureNodeName);
        $propNodes = $featureNodes->item(0)->getElementsByTagName($propertyNodeName);

        $props = MgUtils::ParseFeatureNode($propNodes, $agfRw, $wktRw, $classProps);
        return $props;
    }

    public static function ParseSingleFeatureXml($classDef, $xml, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $doc = new DOMDocument($xml);
        $doc->loadXML($xml);

        return MgUtils::ParseSingleFeatureDocument($classDef, $doc, $featureNodeName, $propertyNodeName);
    }
}

?>