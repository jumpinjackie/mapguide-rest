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
//  relased under the BSD license
//
//  https://github.com/aaronclinger/relative-url-helper
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
        echo '{"'.$root->tagName.'":'.MgUtils::DomElementToJson($root).'}'; 
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
                                    $boolVal->SetNull(true);
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
                                    $byteVal->SetNull(true);
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
                                $props->Add($i16prop);
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
                                $props->Add($i32prop);   
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
                                $props->Add($i64prop);
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

    public static function GetFeatureClassMBR($featureSrvc, $featuresId, $schemaName, $className, $geomName = null)
    {
        $extentGeometryAgg = null;
        $extentGeometrySc = null;
        $extentByteReader = null;
        
        $mbr = new stdClass();

        $clsDef = $featureSrvc->GetClassDefinition($featuresId, $schemaName, $className);
        $props = $clsDef->GetProperties();
        if ($geomName == null) {
            $geomName = $clsDef->GetDefaultGeometryPropertyName();
        }
        $geomProp = $props->GetItem($geomName);
        if ($geomProp->GetPropertyType() != MgFeaturePropertyType::GeometricProperty)
            throw new Exception("Not a geometry property: ".$geomName); //TODO: Localize

        $spatialContext = $geomProp->GetSpatialContextAssociation();

        // Finds the coordinate system
        $agfReaderWriter = new MgAgfReaderWriter();
        $spatialcontextReader = $featureSrvc->GetSpatialContexts($featuresId, false);
        while ($spatialcontextReader->ReadNext())
        {
            if ($spatialcontextReader->GetName() == $spatialContext)
            {
                $mbr->coordinateSystem = $spatialcontextReader->GetCoordinateSystemWkt();
                $csFactory = new MgCoordinateSystemFactory();
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

    static function GetBasicValueFromReader($reader, $propName) {
        $val = "";
        if ($reader->IsNull($propName))
            return "";
        $propType = $reader->GetPropertyType($propName);
        switch($propType) {
            case MgPropertyType::Boolean:
                $val = $reader->GetBoolean($propName)."";
                break;
            case MgPropertyType::Byte:
                $val = $reader->GetByte($propName)."";
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
}

?>