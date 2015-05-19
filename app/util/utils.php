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
//
//  Contains relative to absolute URL conversion code by Aaron Clinger
//  released under the BSD license
//
//  https://github.com/aaronclinger/relative-url-helper
//

require_once "xmlschemainfo.php";

class MgUtils
{
    public static function GetSelfUrlRoot($url) {
        //This is the case if no URL rewriting module was installed
        if (self::StringEndsWith($url, "/index.php")) {
            $url = substr($url, 0, strlen($url) - strlen("/index.php"));
        }
        return $url;
    }

    public static function FormatException($app, $type, $errorMessage, $details, $phpTrace, $status = 500, $mimeType = MgMimeType::Html) {
        $errResponse = "";
        if ($app->config("Error.OutputStackTrace") === false) {
            if ($mimeType === MgMimeType::Xml || $mimeType == MgMimeType::Kml) {
                $errResponse = sprintf(
                    "<?xml version=\"1.0\"?><Error><Type>%s</Type><Message>%s</Message><Details>%s</Details></Error>",
                    MgUtils::EscapeXmlChars($type),
                    MgUtils::EscapeXmlChars($errorMessage),
                    MgUtils::EscapeXmlChars($details));
            } else if ($mimeType === MgMimeType::Json) {
                $errResponse = sprintf(
                    "{ \"Type\": \"%s\", \"Message\": \"%s\", \"Details\": \"%s\" }",
                    MgUtils::EscapeJsonString($type),
                    MgUtils::EscapeJsonString($errorMessage),
                    MgUtils::EscapeJsonString($details));
            } else {
                $errResponse = sprintf(
                    "<html><head><title>%s</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body><h2>%s</h2>%s</body></html>",
                    $type,
                    $errorMessage,
                    $details);
            }
        } else {
            if ($mimeType === MgMimeType::Xml || $mimeType == MgMimeType::Kml) {
                $errResponse = sprintf(
                    "<?xml version=\"1.0\"?><Error><Type>%s</Type><Message>%s</Message><Details>%s</Details><StackTrace>%s</StackTrace></Error>",
                    MgUtils::EscapeXmlChars($type),
                    MgUtils::EscapeXmlChars($errorMessage),
                    MgUtils::EscapeXmlChars($details),
                    MgUtils::EscapeXmlChars($phpTrace));
            } else if ($mimeType === MgMimeType::Json) {
                $errResponse = sprintf(
                    "{ \"Type\": \"%s\", \"Message\": \"%s\", \"Details\": \"%s\", \"StackTrace\": \"%s\" }",
                    MgUtils::EscapeJsonString($type),
                    MgUtils::EscapeJsonString($errorMessage),
                    MgUtils::EscapeJsonString($details),
                    MgUtils::EscapeJsonString($phpTrace));
            } else {
                $errResponse = sprintf(
                    "<html><head><title>%s</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body><h2>%s</h2>%s<h2>%s</h2><pre>%s</pre></body></html>",
                    $type,
                    $errorMessage,
                    $details,
                    $app->localizer->getText("L_STACK_TRACE"),
                    $phpTrace);
            }
        }
        return $errResponse;
    }

    public static function ValidateAcl($userName, $site, $config) {
        // If the user is in the AllowUsers list, or their group is in the AllowGroups list
        // let them through, otherwise 403 them
        //
        if (array_key_exists("AllowUsers", $config)) {
            //print "Checking user ($userName)\n";
            $count = count($config["AllowUsers"]);
            for ($i = 0; $i < $count; $i++) {
                $user = $config["AllowUsers"][$i];
                if ($user == $userName)
                    return true;
            }
        }
        //
        if (array_key_exists("AllowGroups", $config)) {
            //print "Checking group membership of ($userName)\n";
            $groups = array();
            $doc = new DOMDocument();
            $br = $site->EnumerateGroups($userName);
            $xml = $br->ToString();
            $doc->loadXML($xml);
            $groupNodes = $doc->getElementsByTagName("Name");
            for ($i = 0; $i < $groupNodes->length; $i++) {
                $groupName = $groupNodes->item($i)->nodeValue;
                $groups[$groupName] = $groupName;
            }
            
            $count = count($config["AllowGroups"]);
            for ($i = 0; $i < $count; $i++) {
                $group = $config["AllowGroups"][$i];
                if (array_key_exists($group, $groups))
                    return true;
            }
        }
        //
        if (array_key_exists("AllowRoles", $config)) {
            //print "Checking roles of ($userName)";
            $roles = $site->EnumerateRoles($userName);
            $count = count($config["AllowRoles"]);
            for ($i = 0; $i < $count; $i++) {
                $role = $config["AllowRoles"][$i];
                $idx = $roles->IndexOf($role);
                //print "IndexOf($role): $idx\n";
                if ($idx >= 0)
                    return true;
            }
        }
        return false;
    }

    public static function GetApiVersionNamespace($app, $prefix) {
        $pi = $app->request->getPathInfo();
        if (strpos($pi, $prefix) > 0) {
            $tokens = explode("/", $pi);
            //This runs with a major assumption that we have the version in the url (ie: /rest/v1/*, and this method will return "v1")
            if (count($tokens) > 0) {
                for ($i = 0; $i < count($tokens); $i++) {
                    if (strlen($tokens[$i]) > 0) {
                        return $tokens[$i];
                    }
                }
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    public static function TranscodeResourceUrl($baseUrl, $resId) {
        $fullUrl = $baseUrl;
        if ($resId->GetRepositoryType() == MgRepositoryType::Library) {
            $fullUrl .= "/library/";
        } else { //Session
            $fullUrl .= "/session/" . $resId->GetRepositoryName() . "/";
        }
        $fullUrl .= $resId->GetPath() . "/" . $resId->GetName() . "." . $resId->GetResourceType();
        return $fullUrl;
    }

    public static function GetScale($extents, $csObj, $width, $height, $dpi)
    {
        $mapWidth = $csObj->ConvertCoordinateSystemUnitsToMeters($extents->GetWidth());
        $mapHeight = $csObj->ConvertCoordinateSystemUnitsToMeters($extents->GetHeight());
        $screenWidth = $width / $dpi * 0.0254; //METERS_PER_INCH
        $screenHeight = $height / $dpi * 0.0254; //METERS_PER_INCH
        $xScale = $mapWidth / $screenWidth;
        $yScale = $mapHeight / $screenHeight;
        return min($xScale, $yScale);
    }

    public static function GetFileNameFromMimeType($fileNameWithoutExtension, $mimeType = NULL) {
        if ($mimeType == NULL) {
            return $fileNameWithoutExtension;
        } else {
            switch ($mimeType) {
                case MgMimeType::Dwf:
                    return $fileNameWithoutExtension.".dwf";
                case MgMimeType::Gif:
                    return $fileNameWithoutExtension.".gif";
                case MgMimeType::Html:
                    return $fileNameWithoutExtension.".html";
                case MgMimeType::Jpeg:
                    return $fileNameWithoutExtension.".jpg";
                case MgMimeType::Json:
                    return $fileNameWithoutExtension.".json";
                case MgMimeType::Kml:
                    return $fileNameWithoutExtension.".kml";
                case MgMimeType::Kmz:
                    return $fileNameWithoutExtension.".kmz";
                case MgMimeType::Png:
                    return $fileNameWithoutExtension.".png";
                case MgMimeType::Text:
                    return $fileNameWithoutExtension.".text";
                case MgMimeType::Tiff:
                    return $fileNameWithoutExtension.".tif";
                case MgMimeType::Xml:
                    return $fileNameWithoutExtension.".xml";
                default:
                    return $fileNameWithoutExtension;
            }
        }
    }

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
                !MgUtils::StringEndsWith($lastPart, ".WatermarkDefinition") &&
                !MgUtils::StringEndsWith($lastPart, ".TileSetDefinition") &&
                !MgUtils::StringEndsWith($lastPart, ".".MgResourceType::LoadProcedure)) {
                $appendSlash = true;
            }
        }

        $resIdStr = "Library://".implode("/", $parts);
        if ($appendSlash === true)
            $resIdStr .= "/";
        return new MgResourceIdentifier($resIdStr);
    }

    public static function GetPaperSize($app, $paperType) {
        $sizes = $app->config("PDF.PaperSizes");
        if (!array_key_exists($paperType, $sizes))
            throw new Exception($app->localizer->getText("E_UNKNOWN_PAPER_SIZE", $paperType));
        return $sizes[$paperType];
    }

    public static function ParseLocaleDouble($stringValue) {
        $lc = localeconv();
        $result = str_replace(".", $lc["decimal_point"], $stringValue);
        return doubleval($result);
    }

    public static function InToMM($in) {
        return $in * 25.4;
    }

    public static function MMToIn($mm) {
        return $mm / 25.4;
    }

    public static function InToPx($in, $dpi) {
        return ($in * $dpi) / 25.4;
    }
    
    public static function PxToIn($px, $dpi) {
        return ($px * 25.4) / $dpi;
    }

    public static function HtmlToRgba($argbColor) {
        if ($argbColor[0] == '#')
            $argbColor = substr($argbColor, 1);

        $a = 0;
        if (strlen($argbColor) == 6) {
            list($r, $g, $b) = array($argbColor[0].$argbColor[1],
                                     $argbColor[2].$argbColor[3],
                                     $argbColor[4].$argbColor[5]);
        } else if (strlen($argbColor) == 8) {
            list($a, $r, $g, $b) = array($argbColor[0].$argbColor[1],
                                         $argbColor[2].$argbColor[3],
                                         $argbColor[4].$argbColor[5],
                                         $argbColor[6].$argbColor[7]);
            $a = hexdec($a);
        } else {
            return false;
        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array($r, $g, $b, $a);
    }

    //This function try to get a more elegant number for the scale bar display.
    //For example, convert 5.3 to 5, 5.5 to 6, 13 to 10, 230 to 200 and 1234 to 1200.
    //Basically no number will execced 9999 in scale bar display, however we support that situation
    //the minimum number for the return value is 0
    public static function GetRoundNumber($number) {
        $number = abs($number);
        $temp = $number = round($number);
        $len = 0;
        
        while($temp > 0)
        {
            $len++;
            $temp /= 10;
            $temp = floor($temp);
        }      
        
        //10,20,30,40,50,60,70,80,90
        if( 2 === $len )
        {
            $number = $number / 10;
            $number = round($number);
            $number = $number * 10;
        }
        
        //100,200,300,400,500,600,700,800,900
        if( $len >= 3 )
        {
            $number = $number / 100;
            $number = round($number);
            $number = $number * 100;
        }
        
        //else, just 1,2,3,4,5,6,7,8,9
        return $number; 
    }

    public static function MakeWktPolygon($x1, $y1, $x2, $y2) {
        return "POLYGON(($x1 $y1, $x2 $y1, $x2 $y2, $x1 $y2, $x1 $y1))";
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
        $newStr = "";
        $len = strlen($str);

        for($i = 0; $i < $len; $i++)
        {
            switch($str[$i])
            {
                case '&' :
                {
                    $newStr .= "&amp;";
                    break;
                }
                case '\'' :
                {
                    $newStr .= "&apos;";
                    break;
                }
                case '>' :
                {
                    $newStr .= "&gt;";
                    break;
                }
                case '<' :
                {
                    $newStr .= "&lt;";
                    break;
                }
                case '"' :
                {
                    $newStr .= "&quot;";
                    break;
                }
                default :
                    $newStr .= $str[$i];
            }
        }
        return $newStr;
    }

    static function DEBUG_NEWLINE() {
        //NOTE: DO NOT RETURN \n WHEN RUNNING THE TEST SUITE
        return ""; //"\n";
    }

    public static function Json2Xml($value, $nodeName = null) {
        //NOTE: This is not a generic JSON to XML conversion function. It is specialized for MapGuide's
        //case and makes several assumptions that do not apply to general conversion
        
        $xml = "";
        if ($nodeName == null) {
            $xml .= '<?xml version="1.0" encoding="utf-8"?>';
            $props = get_object_vars($value);
            if (count($props) != 1) {
                throw new Exception("No parent node was passed, assuming this method was invoked with a JSON object with one top-level property. However, the following top-level properties were found: ".implode(",", array_keys($props)));
            }
            foreach ($props as $name => $val) {
                $nodeName = $name;
                $xml .= self::Json2Xml($val, $name);
            }
        } else {
            if (is_object($value)) {
                $xml .= "<$nodeName" . self::DEBUG_NEWLINE();
                $props = get_object_vars($value);
                //Process attributes first
                foreach ($props as $name => $val) {
                    if (self::StringStartsWith($name, "@")) { //Is attribute
                        $attName = substr($name, 1);
                        $xml .= ' '.$attName.'="'.$val.'"' . self::DEBUG_NEWLINE();
                    }
                }
                $xml .= ">" . self::DEBUG_NEWLINE();
                //$xml .= "<!--- START OBJECT -->" . self::DEBUG_NEWLINE();
                //Now process other values
                foreach ($props as $name => $val) {
                    if (!self::StringStartsWith($name, "@")) {
                        $xml .= self::Json2Xml($val, $name);
                    }
                }
                //$xml .= "<!--- END OBJECT -->" . self::DEBUG_NEWLINE();
                $xml .= "</$nodeName>" . self::DEBUG_NEWLINE();
            } else if (is_array($value)) {
                //$xml .= "<$nodeName>\n";
                //$xml .= "<!--- START ARRAY -->" . self::DEBUG_NEWLINE();
                foreach ($value as $val) {
                    $xml .= self::Json2Xml($val, $nodeName);
                }
                //$xml .= "<!--- END ARRAY -->" . self::DEBUG_NEWLINE();
                //$xml .= "</$nodeName>\n";
            } else {
                //$xml .= "<!--- START VALUE -->" . self::DEBUG_NEWLINE();
                $xml .= "<$nodeName>" . self::DEBUG_NEWLINE();
                $xml .= self::EscapeXmlChars(strval($value));
                //$xml .= "<!--- END VALUE -->" . self::DEBUG_NEWLINE();
                $xml .= "</$nodeName>" . self::DEBUG_NEWLINE();
            }
        }
        return $xml;
    }

    private static function DomElementToJson($domElement, $rootNode = true) {
        $result = '';
        if ($domElement->nodeType == XML_COMMENT_NODE) {
            return '';
        }
        if ($domElement->nodeType == XML_TEXT_NODE) {
            $result = MgXmlSchemaInfo::GetValue($domElement);
        } else {
            /* some other kind of node, needs to be processed */
            
            $aChildren = array();
            $aValues = array();

            //HACK: Write these attributes ourselves, because DOMNode's brain-dead API won't let
            //us iterate namespaced attributes!
            if ($rootNode) {
                $len = array_push($aValues, array('"'.MgXmlSchemaInfo::NS_XSI.'"'));
                $aChildren['@xmlns:xsi'] = $len-1;
            }

            /* attributes are considered child nodes with a special key name
               starting with @ */
            if ($domElement->hasAttributes()) {
                foreach($domElement->attributes as $key => $attr) {
                    if ($key == "noNamespaceSchemaLocation") {
                        $key = "xsi:$key";
                    }
                    $len = array_push($aValues, array(MgXmlSchemaInfo::GetAttributeValue($attr)));
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
                        array_push($aValues, array(MgXmlSchemaInfo::GetValue($child)));
                    } else {
                        $childTag = $child->tagName;
                        $json = MgUtils::DomElementToJson($child, false);
                        if ($json == '') {
                            $json = 'null';
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
            //ar_dump($aChildren);
            //var_dump($aValues);
            //die;
            
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
                
                    $childNodeName = MgXmlSchemaInfo::DeEscape($aChildren[$i]);
                    $childNodeCanBeMulti = MgXmlSchemaInfo::IsMultiple($domElement, "/$childNodeName");
                    if (isset($aChildren[$i])) {
                        if (!$bIsObject) {
                            $result .= '{';
                        }
                        $result .= '"'.$childNodeName.'":';
                    }
                    if (count($aValue) > 1) {
                        //$result .= '[{"_comment":"value ('.count($aValue).')"},';
                        $result .= '[';
                        //Need to de-escape \' because an escaped ' is an illegal character under double-quoted strings in JSON
                        $result .= MgXmlSchemaInfo::DeEscape(implode(',', $aValue));
                        $result .= ']';
                    } else {
                        if ($childNodeCanBeMulti) {
                            //$result .= '[{"_comment": "multi-node ('.$childNodeName.')"},' . $aValue[0] . ']';
                            $result .= '[' . $aValue[0] . ']';
                        } else {
                            $result .= $aValue[0];
                        }
                    }
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
        return '{"'.$root->tagName.'":'.MgUtils::DomElementToJson($root).'}'; 
    }

    public static function XslTransformByteReader($app, $byteReader, $xslStylesheet, $xslParams) {
        $locale = $app->config("Locale");
        $xslPath = dirname(__FILE__)."/../res/xsl/$locale/$xslStylesheet";

        $doc = new DOMDocument();
        $doc->loadXML($byteReader->ToString());

        //HACK: We have to re-activate entity loading for XSLT transformation to work
        //Thanks to Captain Hindsight, XSLT was a bad choice (was it ever good?). It looked good on paper, you know ... we 
        //had a bunch of existing XSL files already in the schema report we could re-use to easily add HTML representation
        //support to mapguide-rest for certain XML responses. What could possibly go wrong?
        //
        //We'll fix this in v2.0, by using something actually sane
        libxml_disable_entity_loader(false);
        $xsl = new DOMDocument();
        $xsl->load($xslPath);

        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);
        //Back to normal business
        libxml_disable_entity_loader(true);

        foreach ($xslParams as $key => $value) {
            $xslt->setParameter('', $key, $value);
        }

        $result = $xslt->transformToXml($doc);
        return $result;
    }

    public static function StringToBool($str) {
        //boolval was only introduced in PHP 5.5, so this is the next best thing for older releases
        return filter_var($str, FILTER_VALIDATE_BOOLEAN);
    }

    private static function ParseFeatureNode($app, $propNodes, $agfRw, $wktRw, $classProps) {
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
                                    $boolVal->SetNull(true);
                                } else {
                                    $boolVal = new MgBooleanProperty($name, self::StringToBool($value));
                                }
                                $props->Add($boolVal);
                            }
                            break;
                        case MgPropertyType::Byte:
                            {
                                if ($bNull) {
                                    $byteVal = new MgByteProperty($name, 0);
                                    $byteVal->SetNull(true);
                                } else {
                                    $byteVal = new MgByteProperty($name, intval($value));
                                }
                                $props->Add($byteVal);
                            }
                            break;
                        case MgPropertyType::DateTime:
                            {
                                if ($bNull) {
                                    $dtVal = new MgDateTimeProperty($name, null);
                                    $dtVal>SetNull(true);
                                } else {
                                    //We're expecting this: YYYY-MM-DD HH:mm:ss
                                    $dtMajorParts = explode(" ", $value);
                                    if (count($dtMajorParts) != 2) {
                                        throw new Exception($app->localizer->getText("E_INVALID_DATE_STRING", $value));
                                    }
                                    $dateComponents = explode("-", $dtMajorParts[0]);
                                    $timeComponents = explode(":", $dtMajorParts[1]);
                                    if (count($dateComponents) != 3) {
                                        throw new Exception($app->localizer->getText("E_CANNOT_PARSE_DATE_STRING_INVALID_COMPONENT", $value, $dtMajorParts[0]));
                                    }
                                    if (count($timeComponents) != 3) {
                                        throw new Exception($app->localizer->getText("E_CANNOT_PARSE_DATE_STRING_INVALID_COMPONENT", $value, $dtMajorParts[1]));
                                    }
                                    
                                    $dt = new MgDateTime();
                                    
                                    $y = intval(ltrim($dateComponents[0], "0"));
                                    $m = intval(ltrim($dateComponents[1], "0"));
                                    $d = intval(ltrim($dateComponents[2], "0"));
                                    $h = intval(ltrim($timeComponents[0], "0"));
                                    $min = intval(ltrim($timeComponents[1], "0"));
                                    $s = intval(ltrim($timeComponents[2], "0"));
                                    
                                    $dt->SetYear($y);
                                    $dt->SetMonth($m);
                                    $dt->SetDay($d);
                                    $dt->SetHour($h);
                                    $dt->SetMinute($min);
                                    $dt->SetSecond($s);
                                    $dtVal = new MgDateTimeProperty($name, $dt);
                                }
                                $props->Add($dtVal);
                            }
                            break;
                        case MgPropertyType::Decimal:
                        case MgPropertyType::Double:
                            {
                                if ($bNull) {
                                    $doubleVal = new MgDoubleProperty($name, 0.0);
                                    $doubleVal->SetNull(true);
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
                                    $i16val->SetNull(true);
                                } else {
                                    $i16val = new MgInt16Property($name, intval($value));
                                }
                                $props->Add($i16val);
                            }
                            break;
                        case MgPropertyType::Int32:
                            {
                                if ($bNull) {
                                    $i32val = new MgInt32Property($name, 0);
                                    $i32val->SetNull(true);
                                } else {
                                    $i32val = new MgInt32Property($name, intval($value));
                                }
                                $props->Add($i32val);   
                            }
                            break;
                        case MgPropertyType::Int64:
                            {
                                if ($bNull) {
                                    $i64val = new MgInt64Property($name, 0);
                                    $i64val->SetNull(true);
                                } else {
                                    $i64val = new MgInt64Property($name, intval($value));
                                }
                                $props->Add($i64val);
                            }
                            break;
                        case MgPropertyType::Single:
                            {
                                if ($bNull) {
                                    $sinProp = new MgSingleProperty($name, 0.0);
                                    $sinProp->SetNull(true);
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
                                    $strProp->SetNull(true);
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

    public static function ParseMultiFeatureXml($app, $classDef, $xml, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        return MgUtils::ParseMultiFeatureDocument($app, $classDef, $doc, $featureNodeName, $propertyNodeName);
    }

    public static function ParseMultiFeatureDocument($app, $classDef, $doc, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $batchProps = new MgBatchPropertyCollection();
        $featureNodes = $doc->getElementsByTagName($featureNodeName);

        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();
        $classProps = $classDef->GetProperties();

        for ($i = 0; $i < $featureNodes->length; $i++) {
            $propNodes = $featureNodes->item($i)->getElementsByTagName($propertyNodeName);
            $props = MgUtils::ParseFeatureNode($app, $propNodes, $agfRw, $wktRw, $classProps);
            $batchProps->Add($props);
        }

        return $batchProps;
    }

    public static function ParseSingleFeatureDocument($app, $classDef, $doc, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();
        $classProps = $classDef->GetProperties();

        $props = new MgPropertyCollection();
        $featureNodes = $doc->GetElementsByTagName($featureNodeName);
        $propNodes = $featureNodes->item(0)->getElementsByTagName($propertyNodeName);

        $props = MgUtils::ParseFeatureNode($app, $propNodes, $agfRw, $wktRw, $classProps);
        return $props;
    }

    public static function ParseSingleFeatureXml($app, $classDef, $xml, $featureNodeName = "Feature", $propertyNodeName = "Property") {
        $doc = new DOMDocument($xml);
        $doc->loadXML($xml);

        return MgUtils::ParseSingleFeatureDocument($app, $classDef, $doc, $featureNodeName, $propertyNodeName);
    }

    public static function GetTransform($featSvc, $resId, $schemaName, $className, $transformto) {
        $transform = null;
        $factory = new MgCoordinateSystemFactory();
        $targetWkt = $factory->ConvertCoordinateSystemCodeToWkt($transformto);
        $clsDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
        //Has a designated geometry property, use it's spatial context
        if ($clsDef->GetDefaultGeometryPropertyName() !== "") {
            $props = $clsDef->GetProperties();
            $idx = $props->IndexOf($clsDef->GetDefaultGeometryPropertyName());
            if ($idx >= 0) {
                $geomProp = $props->GetItem($idx);
                $scName = $geomProp->GetSpatialContextAssociation();
                $scReader = $featSvc->GetSpatialContexts($resId, false);
                while ($scReader->ReadNext()) {
                    if ($scReader->GetName() === $scName) {
                        if ($scReader->GetCoordinateSystemWkt() !== $targetWkt) {
                            $targetCs = $factory->CreateFromCode($transformto);
                            $sourceCs = $factory->Create($scReader->GetCoordinateSystemWkt());
                            $transform = $factory->GetTransform($sourceCs, $targetCs);
                            break;
                        }
                    }
                }
                $scReader->Close();
            }
        }
        return $transform;
    }

    public static function GetProviderCapabilties($featSvc, $resSvc, $fsId) {
        $content = $resSvc->GetResourceContent($fsId);
        $doc = new DOMDocument();
        $doc->loadXML($content->ToString());

        $prvNode = $doc->getElementsByTagName("Provider");
        if ($prvNode->length == 1) {
            $capsBr = $featSvc->GetCapabilities($prvNode->item(0)->nodeValue);
            return $capsBr->ToString();
        }
        return null;
    }

    public static function GetFeatureClassMBR($app, $featureSrvc, $featuresId, $schemaName, $className, $geomName = null, $transformToCsCode = null)
    {
        $extentGeometryAgg = null;
        $extentGeometrySc = null;
        $extentByteReader = null;
        
        $mbr = new stdClass();
        $csFactory = new MgCoordinateSystemFactory();

        $clsDef = $featureSrvc->GetClassDefinition($featuresId, $schemaName, $className);
        $props = $clsDef->GetProperties();
        if ($geomName == null) {
            $geomName = $clsDef->GetDefaultGeometryPropertyName();
        }
        $geomProp = $props->GetItem($geomName);
        if ($geomProp->GetPropertyType() != MgFeaturePropertyType::GeometricProperty)
            throw new Exception($app->localizer->getText("E_NOT_GEOMETRY_PROPERTY", $geomName));

        $spatialContext = $geomProp->GetSpatialContextAssociation();

        // Finds the coordinate system
        $agfReaderWriter = new MgAgfReaderWriter();
        $spatialcontextReader = $featureSrvc->GetSpatialContexts($featuresId, false);
        while ($spatialcontextReader->ReadNext())
        {
            if ($spatialcontextReader->GetName() == $spatialContext)
            {
                $mbr->coordinateSystem = $spatialcontextReader->GetCoordinateSystemWkt();
                $mbr->csCode = $csFactory->ConvertWktToCoordinateSystemCode($mbr->coordinateSystem);
                $mbr->epsg = $csFactory->ConvertWktToEpsgCode($mbr->coordinateSystem);
                // Finds the extent
                $extentByteReader = $spatialcontextReader->GetExtent();
                break;
            }
        }
        $spatialcontextReader->Close();
        if ($extentByteReader != null)
        {
            // Get the extent geometry from the spatial context
            $extentGeometrySc = $agfReaderWriter->Read($extentByteReader);
        }

        // Try to get the extents using the selectaggregate as sometimes the spatial context
        // information is not set
        $aggregateOptions = new MgFeatureAggregateOptions();
        $featureProp = 'SPATIALEXTENTS("' . $geomName . '")';
        $aggregateOptions->AddComputedProperty('EXTENTS', $featureProp);

        try
        {
            $dataReader = $featureSrvc->SelectAggregate($featuresId, $className, $aggregateOptions);
            if($dataReader->ReadNext())
            {
                // Get the extents information
                $byteReader = $dataReader->GetGeometry('EXTENTS');
                $extentGeometryAgg = $agfReaderWriter->Read($byteReader);
            }
            $dataReader->Close();
        }
        catch (MgException $e)
        {
            if ($extentGeometryAgg == null) 
            {
                //We do have one last hope. EXTENT() is an internal MapGuide custom function that's universally supported
                //as it operates against an underlying select query result. This raw-spins the reader server-side so there
                //is no server -> web tier transmission overhead involved.
                try
                {
                    $aggregateOptions = new MgFeatureAggregateOptions();
                    $aggregateOptions->AddComputedProperty("COMP_EXTENT", "EXTENT(".$geomName.")");
                    
                    $dataReader = $featureSrvc->SelectAggregate($featuresId, $className, $aggregateOptions);
                    if($dataReader->ReadNext())
                    {
                        // Get the extents information
                        $byteReader = $dataReader->GetGeometry('COMP_EXTENT');
                        $extentGeometryAgg = $agfReaderWriter->Read($byteReader);
                    }
                    $dataReader->Close();
                }
                catch (MgException $e2) 
                {
                    
                }
            }
        }
        
        $mbr->extentGeometry = null;
        // Prefer SpatialExtents() of EXTENT() result over spatial context extent
        if ($extentGeometryAgg != null)
            $mbr->extentGeometry = $extentGeometryAgg;
        if ($mbr->extentGeometry == null) { //Stil null? Now try spatial context
            if ($extentGeometrySc != null)
                $mbr->extentGeometry = $extentGeometrySc;
        }

        if ($transformToCsCode != null) {
            $sourceCs = $csFactory->CreateFromCode($mbr->csCode);
            $targetCs = $csFactory->CreateFromCode($transformToCsCode);
            $xform = $csFactory->GetTransform($sourceCs, $targetCs);

            $mbr->extentGeometry = $mbr->extentGeometry->Transform($xform);
            $mbr->csCode = $targetCs->GetCsCode();
            $mbr->epsg = $targetCs->GetEpsgCode();
        }

        return $mbr;
    }

    public static function GetFeatureCount($featSvc, $featuresId, $schemaName, $className, $tryAggregate = true) {
        //Try the SelectAggregate shortcut. This is faster than raw spinning a feature reader
        //
        //NOTE: If MapGuide supported scrollable readers like FDO, we'd have also tried 
        //that as well.
        $totalEntries = -1;
        $featureName = $schemaName . ":" . $className;
        $canCount = false;
        $gotCount = false;
        
        if ($tryAggregate) {
            $clsDef = $featSvc->GetClassDefinition($featuresId, $schemaName, $className);
            $idProps = $clsDef->GetIdentityProperties();
            if ($idProps->GetCount() > 0)
            {
                $pd = $idProps->GetItem(0);
                $expr = "COUNT(" .$pd->GetName(). ")";
                $query = new MgFeatureAggregateOptions();
                $query->AddComputedProperty("TotalCount", $expr);
                try 
                {
                    $dataReader = $featSvc->SelectAggregate($featuresId, $featureName, $query);
                    if ($dataReader->ReadNext())
                    {
                        // When there is no data, the property will be null.
                        if($dataReader->IsNull("TotalCount"))
                        {
                            $totalEntries = 0;
                            $gotCount = true;
                        }
                        else
                        {
                            $ptype = $dataReader->GetPropertyType("TotalCount");
                            switch ($ptype)
                            {
                                case MgPropertyType::Int32:
                                    $totalEntries = $dataReader->GetInt32("TotalCount");
                                    $gotCount = true;
                                    break;
                                case MgPropertyType::Int64:
                                    $totalEntries = $dataReader->GetInt64("TotalCount");
                                    $gotCount = true;
                                    break;
                            }
                            $dataReader->Close();
                        }
                    }
                }
                catch (MgException $ex) //Some providers like OGR can lie
                {
                    $gotCount = false;
                }
            }
        }
        
        if ($gotCount == false)
        {
            $featureReader = null;
            try 
            {
                $featureReader = $featSvc->SelectFeatures($featuresId, $featureName, null);
            }
            catch (MgException $ex)
            {
                $totalEntries = -1; //Can't Count() or raw spin? Oh dear!
            }
            
            if ($featureReader != null)
            {
                while($featureReader->ReadNext())
                    $totalEntries++;
                $featureReader->Close();
            }
        }
        
        return $totalEntries;
    }

    public static function DateTimeToString($dt) {
        $val = "";
        if ($dt->IsDate()) {
            $val .= sprintf("%s-%s-%s",
                ($dt->GetYear().""),
                str_pad($dt->GetMonth()."", 2, '0', STR_PAD_LEFT),
                str_pad($dt->GetDay()."", 2, '0', STR_PAD_LEFT));
        }
        if ($dt->IsTime()) {
            if (strlen($val) > 0)
                $val .= " ";
            $val .= sprintf("%s:%s:%s",
                str_pad($dt->GetHour()."", 2, '0', STR_PAD_LEFT),
                str_pad($dt->GetMinute()."", 2, '0', STR_PAD_LEFT),
                str_pad($dt->GetSecond()."", 2, '0', STR_PAD_LEFT)
            );
        }
        return $val;
    }

    public static function GetBasicValueFromReader($reader, $propName) {
        $val = "";
        if ($reader->IsNull($propName))
            return "";
        $propType = $reader->GetPropertyType($propName);
        switch($propType) {
            case MgPropertyType::Boolean:
                $val = $reader->GetBoolean($propName) ? "true" : "false";
                break;
            case MgPropertyType::Byte:
                $val = $reader->GetByte($propName)."";
                break;
            case MgPropertyType::DateTime:
                $dt = $reader->GetDateTime($propName);
                $val = self::DateTimeToString($dt);
                break;
            case MgPropertyType::Decimal:
            case MgPropertyType::Double:
                $val = $reader->GetDouble($propName)."";
                break;
            case MgPropertyType::Int16:
                $val = $reader->GetInt16($propName)."";
                break;
            case MgPropertyType::Int32:
                $val = $reader->GetInt32($propName)."";
                break;
            case MgPropertyType::Int64:
                $val = $reader->GetInt64($propName)."";
                break;
            case MgPropertyType::Single:
                $val = $reader->GetSingle($propName)."";
                break;
            case MgPropertyType::String:
                $val = $reader->GetString($propName);
                break;
        }
        return $val;
    }

    public static function GetDistinctValues($featSvc, $fsId, $schemaName, $className, $distinctPropName) {
        $values = array();

        $query = new MgFeatureAggregateOptions();
        $query->AddComputedProperty("RESULT", "UNIQUE($distinctPropName)");
        $rdr = $featSvc->SelectAggregate($fsId, "$schemaName:$className", $query);
        while ($rdr->ReadNext()) {
            array_push($values, self::GetBasicValueFromReader($rdr, "RESULT"));
        }
        $rdr->Close();

        return $values;
    }

    public static function RelativeToAbsoluteUrl($host, $path)
    {
        $host_parts    = parse_url($host);
        $path_parts    = parse_url($path);
        $absolute_path = '';
        
        if (isset($path_parts['path']) && isset($host_parts['scheme']) && substr($path_parts['path'], 0, 2) === '//' && ! isset($path_parts['scheme']))
        {
            $path       = $host_parts['scheme'] . ':' . $path;
            $path_parts = parse_url($path);
        }
        
        if (isset($path_parts['host']))
        {
            return $path;
        }
        
        if (isset($host_parts['scheme']))
        {
            $absolute_path .= $host_parts['scheme'] . '://';
        }
        
        if (isset($host_parts['user']))
        {
            if (isset($host_parts['pass']))
            {
                $absolute_path .= $host_parts['user'] . ':' . $host_parts['pass'] . '@';
            }
            else
            {
                $absolute_path .= $host_parts['user'] . '@';
            }
        }
        
        if (isset($host_parts['host']))
        {
            $absolute_path .= $host_parts['host'];
        }
        
        if (isset($host_parts['port']))
        {
            $absolute_path .= ':' . $host_parts['port'];
        }
        
        if (isset($path_parts['path']))
        {
            $path_segments = explode('/', $path_parts['path']);
            
            if (isset($host_parts['path']))
            {
                $host_segments = explode('/', $host_parts['path']);
            }
            else
            {
                $host_segments = array('', '');
            }
            
            $i = -1;
            while (++$i < count($path_segments))
            {
                $path_seg  = $path_segments[$i];
                $last_item = end($host_segments);
                
                switch ($path_seg)
                {
                    case '.' :
                        if ($i === 0 || empty($last_item))
                        {
                            array_splice($host_segments, -1);
                        }
                        break;
                    case '..' :
                        if ($i === 0 && ! empty($last_item))
                        {
                            array_splice($host_segments, -2);
                        }
                        else
                        {
                            array_splice($host_segments, empty($last_item) ? -2 : -1);
                        }
                        break;
                    case '' :
                        if ($i === 0)
                        {
                            $host_segments = array();
                        }
                        else
                        {
                            $host_segments[] = $path_seg;
                        }
                        break;
                    default :
                        if ($i === 0 && ! empty($last_item))
                        {
                            array_splice($host_segments, -1);
                        }
                        
                        $host_segments[] = $path_seg;
                        break;
                }
            }
            
            $absolute_path .= '/' . ltrim(implode('/', $host_segments), '/');
        }
        
        if (isset($path_parts['query']))
        {
            $absolute_path .= '?' . $path_parts['query'];
        }
        
        if (isset($path_parts['fragment']))
        {
            $absolute_path .= '#' . $path_parts['fragment'];
        }
        
        return $absolute_path;
    }

    public static function ByteReaderToBase64($icon, $includeDataUriPrefix = false) {
        $str = "";
        $buffer = '';
        $length = $icon->GetLength();
        if ($icon->Read($buffer, $length) != 0)
        {
            $str .= base64_encode($buffer);
        }

        if ($includeDataUriPrefix === true)        
            $str = "data:".$icon->GetMimeType().";base64,". $str;
        return $str;
    }

    // GetLegendImageInline
    //
    // Returns a data URI containing the base64 encoded content of the specified legend icon
    //
    // Due to the fixed size (16x16 px), the generated data URI will easily fall under the data URI limit of most (if not all) web browsers that support it.
    //
    public static function GetLegendImageInline($mappingService, $layerDefinitionId, $scale, $geomType, $themeCategory, $iconWidth = 16, $iconHeight = 16, $iconFormat = MgImageFormats::Png, $includeDataUriPrefix = false)
    {
        $icon = $mappingService->GenerateLegendImage($layerDefinitionId, $scale, $iconWidth, $iconHeight, $iconFormat, $geomType, $themeCategory);
        if ($icon != null)
        {
            $str = "";
            $buffer = '';
            $length = $icon->GetLength();
            if ($icon->Read($buffer, $length) != 0)
            {
                $str .= base64_encode($buffer);
            }
    
            if ($includeDataUriPrefix === true)        
                $str = "data:".$icon->GetMimeType().";base64,". $str;
            return $str;
        }
        //$styleObj->imageData = "http://localhost/mapguide/mapagent/mapagent.fcgi?OPERATION=GETLEGENDIMAGE&VERSION=1.0.0&SESSION=$sessionID&SCALE=$scaleVal&LAYERDEFINITION=".$resID->ToString()."&TYPE=".$styleObj->geometryType."&THEMECATEGORY=".$styleObj->categoryIndex;
        return null;
    }

    private static function PropertyDefinitionToJson($propDef, $isIdentity = false) {
        $ptype = $propDef->GetPropertyType();

        $output = "{" . self::DEBUG_NEWLINE();
        $output .= '"Name": "'.self::EscapeJsonString($propDef->GetName()).'",' . self::DEBUG_NEWLINE();
        $output .= '"Description": "'.self::EscapeJsonString($propDef->GetDescription()).'",' . self::DEBUG_NEWLINE();
        $output .= '"PropertyType": '.$ptype."," . self::DEBUG_NEWLINE();
        $output .= '"IsIdentity": '.($isIdentity ? "true" : "false")."," . self::DEBUG_NEWLINE();
        switch ($ptype)
        {
            case MgFeaturePropertyType::DataProperty:
                $output .= '"DataType": '. $propDef->GetDataType()."," . self::DEBUG_NEWLINE();
                $output .= '"DefaultValue": "'.self::EscapeJsonString($propDef->GetDefaultValue()).'",' . self::DEBUG_NEWLINE();
                $output .= '"Length": '. $propDef->GetLength()."," . self::DEBUG_NEWLINE();
                $output .= '"Nullable": '.($propDef->GetNullable() ? "true":"false")."," . self::DEBUG_NEWLINE();
                $output .= '"ReadOnly": '.($propDef->GetReadOnly() ? "true":"false")."," . self::DEBUG_NEWLINE();
                $output .= '"IsAutoGenerated": '.($propDef->IsAutoGenerated() ? "true" : "false")."," . self::DEBUG_NEWLINE();
                $output .= '"Precision": '. $propDef->GetPrecision()."," . self::DEBUG_NEWLINE();
                $output .= '"Scale": '. $propDef->GetScale().self::DEBUG_NEWLINE();
                break;
            case MgFeaturePropertyType::GeometricProperty:
                $output .= '"GeometryTypes": '. $propDef->GetGeometryTypes()."," . self::DEBUG_NEWLINE();
                $geomTypes = $propDef->GetSpecificGeometryTypes();
                $output .= '"SpecificGeometryTypes": ['. self::DEBUG_NEWLINE();
                for ($i = 0; $i < $geomTypes->GetCount(); $i++) {
                    $output .= $geomTypes->GetType($i);
                    if ($i < $geomTypes->GetCount() - 1) {
                        $output .= ",";
                    }
                }
                $output .= "]," . self::DEBUG_NEWLINE();
                $output .= '"HasElevation": '.($propDef->GetHasElevation() ? "true" : "false")."," . self::DEBUG_NEWLINE();
                $output .= '"HasMeasure": '.($propDef->GetHasMeasure() ? "true" : "false")."," . self::DEBUG_NEWLINE();
                $output .= '"ReadOnly": '.($propDef->GetReadOnly() ? "true" : "false")."," . self::DEBUG_NEWLINE();
                $output .= '"SpatialContextAssociation": "'. self::EscapeJsonString($propDef->GetSpatialContextAssociation()).'"'.self::DEBUG_NEWLINE();
                break;
            case MgFeaturePropertyType::RasterProperty:
                $output .= '"DefaultImageXSize": '. $propDef->GetDefaultImageXSize()."," . self::DEBUG_NEWLINE();
                $output .= '"DefaultImageYSize": '. $propDef->GetDefaultImageYSize()."," . self::DEBUG_NEWLINE();
                $output .= '"Nullable": '. $propDef->GetNullable()."," . self::DEBUG_NEWLINE();
                $output .= '"ReadOnly": '.($propDef->GetReadOnly() ? "true" : "false")."," . self::DEBUG_NEWLINE();
                $output .= '"SpatialContextAssociation": "'. self::EscapeJsonString($propDef->GetSpatialContextAssociation()).'"'.self::DEBUG_NEWLINE();
                break;
        }
        $output .= "}" . self::DEBUG_NEWLINE();
        return $output;
    }

    public static function ClassDefinitionToJson($clsDef) {
        $idProps = $clsDef->GetIdentityProperties();
        $props = $clsDef->GetProperties();
        $propCount = $props->GetCount();

        $output = "{" . self::DEBUG_NEWLINE();
        $output .= '"Name": "'.self::EscapeJsonString($clsDef->GetName()).'",' . self::DEBUG_NEWLINE();
        $output .= '"Description": "'.self::EscapeJsonString($clsDef->GetDescription()).'",' . self::DEBUG_NEWLINE();
        $output .= '"IsAbstract": '.($clsDef->IsAbstract() ? "true" : "false")."," . self::DEBUG_NEWLINE();
        $output .= '"IsComputed": '.($clsDef->IsComputed() ? "true" : "false")."," . self::DEBUG_NEWLINE();
        $output .= '"DefaultGeometryPropertyName": "'.self::EscapeJsonString($clsDef->GetDefaultGeometryPropertyName()).'",' . self::DEBUG_NEWLINE();
        $output .= '"Properties": [' . self::DEBUG_NEWLINE();        
        for ($i = 0; $i < $propCount; $i++) {
            $propDef = $props->GetItem($i);
            $isIdentity = ($idProps->IndexOf($propDef->GetName()) >= 0);
            $output .= self::PropertyDefinitionToJson($propDef, $isIdentity);
            if ($i < $propCount - 1) {
                $output .= "," . self::DEBUG_NEWLINE();
            }
        }
        $output .= "]" . self::DEBUG_NEWLINE();
        $output .= "}" . self::DEBUG_NEWLINE();
        return $output;
    }

    public static function SchemaToJson($schema) {
        $output = "{" . self::DEBUG_NEWLINE();
        $output .= '"Name": "'.self::EscapeJsonString($schema->GetName()).'",' . self::DEBUG_NEWLINE();
        $output .= '"Description": "'.self::EscapeJsonString($schema->GetDescription()).'",' . self::DEBUG_NEWLINE();
        $output .= '"Classes": [' . self::DEBUG_NEWLINE();
        $classes = $schema->GetClasses();
        $clsCount = $classes->GetCount();
        for ($i = 0; $i < $clsCount; $i++) {
            $clsDef = $classes->GetItem($i);
            $output .= self::ClassDefinitionToJson($clsDef);
            if ($i < $clsCount - 1) {
                $output .= "," . self::DEBUG_NEWLINE();
            }
        }
        $output .= "]" . self::DEBUG_NEWLINE();
        $output .= "}" . self::DEBUG_NEWLINE();
        return $output;
    }

    public static function SchemasToJson($schemas) {
        $output = "{" . self::DEBUG_NEWLINE();
        $output .= '"Schemas": [';
        $schemaCount = $schemas->GetCount();
        for ($i = 0; $i < $schemaCount; $i++) {
            $schema = $schemas->GetItem($i);
            $output .= self::SchemaToJson($schema);
            if ($i < $schemaCount - 1) {
                $output .= "," . self::DEBUG_NEWLINE();
            }
        }
        $output .= "]" . self::DEBUG_NEWLINE();
        $output .= "}" . self::DEBUG_NEWLINE();
        return $output;
    }

    //This method should not have to exist, but we've discovered certain cases where partially
    //describing a schema does not give us a partial result (this must be a defect in the FDO provider). 
    //This method helps us workaround the problem, by whittling down the class list to only what's specified
    public static function EnsurePartialSchema($schemas, $schemaName, $classNames) {
        
        $sidx = -1;
        for ($i = 0; $i < $schemas->GetCount(); $i++) {
            $schema = $schemas->GetItem($i);
            if ($schema->GetName() == $schemaName) {
                $sidx = $i;
                break;
            }
        }
        if ($sidx >= 0) {
            $schema = $schemas->GetItem($sidx);
            $classes = $schema->GetClasses();
            $clsCount = $classes->GetCount();
            if ($clsCount > $classNames->GetCount()) {
                //Defect has been exposed, now work around it
                $toRemove = array();
                for ($i = 0; $i < $clsCount; $i++) {
                    $clsDef = $classes->GetItem($i);
                    //Not in the list of class names specified, remove it
                    if ($classNames->IndexOf($clsDef->GetName()) < 0) {
                        array_push($toRemove, $clsDef);
                    }
                }
                foreach ($toRemove as $remove) {
                    $classes->Remove($remove);
                }
            }
        }
    }

    /*
    private static function OutputPropertyDefinition($propDef, $isIdentity = false) {
        $output = "<PropertyDefinition>";
        $output .= "<Name>".self::EscapeXmlChars($propDef->GetName())."</Name>";
        $output .= "<Description>".self::EscapeXmlChars($propDef->GetDescription())."</Description>";
        $output .= "</PropertyDefinition>";
        return $output;
    }

    public static function OutputClassDefinition($clsDef, $fmt = "xml", $includeXmlProlog = true) {
        $output = "";
        if ($fmt == "xml" && $includeXmlProlog) {
            $output .= '<?xml version="1.0" encoding="utf-8"?>';
        }
        $output .= "<ClassDefinition>";
        $output .= "<Name>".self::EscapeXmlChars($clsDef->GetName())."</Name>";
        $output .= "<Description>".self::EscapeXmlChars($clsDef->GetDescription())."</Description>";
        $output .= "<IsAbstract>".($clsDef->IsAbstract() ? "true" : "false")."</IsAbstract>";
        $output .= "<IsComputed>".($clsDef->IsComputed() ? "true" : "false")."</IsComputed>";
        $output .= "<DefaultGeometryPropertyName>".self::EscapeXmlChars($clsDef->GetDefaultGeometryPropertyName())."</DefaultGeometryPropertyName>";
        $idProps = $clsDef->GetIdentityProperties();
        $props = $clsDef->GetProperties();
        for ($i = 0; $i < $props->GetCount(); $i++) {
            $propDef = $props->GetItem($i);
            $isIdentity = ($idProps->IndexOf($propDef->GetName()) >= 0);
            $output .= self::OutputPropertyDefinition($propDef, $isIdentity);
        }
        $output .= "</ClassDefinition>";

        if ($fmt == "json") {
            return self::Xml2Json($output);
        }

        return $output;
    }

    private static function OutputSchema($schema, $fmt = "xml") {
        $output = "<FeatureSchema>";
        $output .= "<Name>".self::EscapeXmlChars($schema->GetName())."</Name>";
        $output .= "<Description>".self::EscapeXmlChars($schema->GetDescription())."</Description>";
        $classes = $schema->GetClasses();
        for ($i = 0; $i < $classes->GetCount(); $i++) {
            $clsDef = $classes->GetItem($i);
            $output .= self::OutputClassDefinition($clsDef, $fmt, false);
        }
        $output .= "</FeatureSchema>";
        return $output;
    }

    public static function OutputSchemas($schemas, $fmt = "xml") {
        $output = '<?xml version="1.0" encoding="utf-8"?>';
        $output .= "<FeatureSchemaCollection>";
        $output .= "<Schemas>";
        $schemaCount = $schemas->GetCount();
        for ($i = 0; $i < $schemaCount; $i++) {
            $output .= self::OutputSchema($schemas->GetItem($i), $fmt);
        }
        $output .= "</Schemas>";
        $output .= "</FeatureSchemaCollection>";

        if ($fmt == "json") {
            return self::Xml2Json($output);
        } else {
            return $output;
        }
    }
    */
}

?>