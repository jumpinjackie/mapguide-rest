<?php

//
//  Copyright (C) 2016 by Jackie Ng
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

class MgSelectionRequestedFeatures {
    const REQUEST_ATTRIBUTES       = 1;
    const REQUEST_INLINE_SELECTION = 2;
    const REQUEST_TOOLTIP          = 4;
    const REQUEST_HYPERLINK        = 8;
}

class MgSelectionRenderer {
    public function __construct() { }
    
    protected function RenderSelection(MgSelectionBase $selection) {
        $selXml = $selection->ToXml();
        if (strlen($selXml) > 0) {
            //Need to strip the XML prolog from this fragment
            $fsdoc = new DOMDocument();
            $fsdoc->loadXML($selXml);
            $selXml = $fsdoc->saveXML($fsdoc->documentElement);
            return $selXml;
        } else {
            return "<FeatureSet />\n";
        }
    }
    
    protected function RenderSelectedFeature(MgReader $reader, /*php_string*/ $geomPropName, array $propMappings, MgAgfReaderWriter $agfRw) {
        $xml = "<Feature>\n";
        $bounds = "";
        if (!$reader->IsNull($geomPropName)) {
            $agf = $reader->GetGeometry($geomPropName);
            $geom = $agfRw->Read($agf);
            $env = $geom->Envelope();
            $ll = $env->GetLowerLeftCoordinate();
            $ur = $env->GetUpperRightCoordinate();
            $bounds = $ll->GetX()." ".$ll->GetY()." ".$ur->GetX()." ".$ur->GetY();
        }
        $xml .= "<Bounds>$bounds</Bounds>\n";
        foreach ($propMappings as $propName => $displayName) {
            $value = MgUtils::EscapeXmlChars(MgUtils::GetBasicValueFromReader($reader, $propName));
            $xml .= "<Property>\n";
            $xml .= "<Name>$displayName</Name>\n";
            if (!$reader->IsNull($propName))
                $xml .= "<Value>$value</Value>\n";
            $xml .= "</Property>\n";
        }
        $xml .= "</Feature>\n";
        return $xml;
    }
    
    public function Render(MgResourceService $resSvc, /*php_int*/ $reqData, MgFeatureInformation $featInfo, MgSelectionBase $selection, /*php_bool*/ $bRequestAttributes, MgByteReader $inlineSelectionImg) {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<FeatureInformation>\n";

        $tooltip = "";
        $hyperlink = "";
        if ($featInfo != null) {
            $tooltip = $featInfo->GetTooltip();
            $hyperlink = $featInfo->GetHyperlink();
        }

        $xml .= $this->RenderSelection($selection);
        
        if ((($reqData & MgSelectionRequestedFeatures::REQUEST_TOOLTIP) == MgSelectionRequestedFeatures::REQUEST_TOOLTIP) && strlen($tooltip) > 0) {
            $xml .= "<Tooltip>".MgUtils::EscapeXmlChars($tooltip)."</Tooltip>\n";
        } else {
            $xml .= "<Tooltip />\n";
        }
        if ((($reqData & MgSelectionRequestedFeatures::REQUEST_HYPERLINK) == MgSelectionRequestedFeatures::REQUEST_HYPERLINK) && strlen($hyperlink) > 0) {
            $xml .= "<Hyperlink>".MgUtils::EscapeXmlChars($hyperlink)."</Hyperlink>\n";   
        } else {
            $xml .= "<Hyperlink />\n";
        }
        if ((($reqData & MgSelectionRequestedFeatures::REQUEST_INLINE_SELECTION) == MgSelectionRequestedFeatures::REQUEST_INLINE_SELECTION) && $inlineSelectionImg != null) {
            $xml .= "<InlineSelectionImage>\n";
            $xml .= "<MimeType>".$inlineSelectionImg->GetMimeType()."</MimeType>\n";
            $b64 = MgUtils::ByteReaderToBase64($inlineSelectionImg);
            $xml .= "<Content>$b64</Content>\n";
            $xml .= "</InlineSelectionImage>\n";
        }
        if ($bRequestAttributes) {
            $agfRw = new MgAgfReaderWriter();
            $layerDoc = new DOMDocument();
            $xml .= "<SelectedFeatures>";

            $selLayers = $selection->GetLayers();
            if ($selLayers != null) {
                $selLayerCount = $selLayers->GetCount();
                for ($i = 0; $i < $selLayerCount; $i++) {
                    $selLayer = $selLayers->GetItem($i);
                    $layerName = $selLayer->GetName();

                    $xml .= "<SelectedLayer id=\"".$selLayer->GetObjectId()."\" name=\"$layerName\">";
                    $xml .= "<LayerMetadata>\n";

                    $ldfId = $selLayer->GetLayerDefinition();
                    $layerContent = $resSvc->GetResourceContent($ldfId);
                    $layerDoc->loadXML($layerContent->ToString());
                    $propMapNodes = $layerDoc->getElementsByTagName("PropertyMapping");
                    $clsDef = $selLayer->GetClassDefinition();
                    $clsProps = $clsDef->GetProperties();

                    $propMappings = array();
                    for ($j = 0; $j < $propMapNodes->length; $j++) {
                        $propMapNode = $propMapNodes->item($j);
                        $propName = $propMapNode->getElementsByTagName("Name")->item(0)->nodeValue;
                        $pidx = $clsProps->IndexOf($propName);
                        if ($pidx >= 0) {
                            $propDispName = MgUtils::EscapeXmlChars($propMapNode->getElementsByTagName("Value")->item(0)->nodeValue);
                            $propDef = $clsProps->GetItem($pidx);
                            $propType = MgPropertyType::Null;
                            if ($propDef->GetPropertyType() == MgFeaturePropertyType::DataProperty) {
                                $propType = $propDef->GetDataType();
                            } else if ($propDef->GetPropertyType() == MgFeaturePropertyType::DataProperty) {
                                $propType = MgPropertyType::Geometry;
                            }
                            $xml .= "<Property>\n";
                            $xml .= "<Name>$propName</Name>\n<Type>$propType</Type>\n<DisplayName>$propDispName</DisplayName>\n";
                            $xml .= "</Property>\n";

                            $propMappings[$propName] = $propDispName;
                        }
                    }

                    $xml .= "</LayerMetadata>\n";

                    $reader = $selection->GetSelectedFeatures($selLayer, $selLayer->GetFeatureClassName(), false);
                    $rdrClass = $reader->GetClassDefinition();
                    $geomPropName = $rdrClass->GetDefaultGeometryPropertyName();
                    while ($reader->ReadNext()) {
                        $xml .= $this->RenderSelectedFeature($reader, $geomPropName, $propMappings, $agfRw);
                    }
                    $reader->Close();

                    $xml .= "</SelectedLayer>";
                }
            }
            $xml .= "</SelectedFeatures>";
        }
        $xml .= "</FeatureInformation>";
        return $xml;
    }
}

class MgStatelessSelectionRenderer extends MgSelectionRenderer {
    public function __construct() {
        parent::__construct();
    }
    
    protected function RenderSelection(MgSelectionBase $selection) { //Override
        //Selection XML is useless in stateless mode, so do nothing 
        return "";
    }
}

abstract class MgSelectionUpdaterBase {
    public abstract function Update(MgSelectionBase $selection, MgFeatureInformation $featInfo, /*php_bool*/ $bAppend);
}

class MgNullSelectionUpdater extends MgSelectionUpdaterBase {
    public function Update(MgSelectionBase $selection, MgFeatureInformation $featInfo, /*php_bool*/ $bAppend) {
        //no-op
    }
}

class MgSelectionUpdater extends MgSelectionUpdaterBase {
    private $bHasNewSelection;
    private $map;
    private $resSvc; 
    private $mapName;
    private $persist;
    
    public function __construct(MgMapBase $map, MgResourceService $resSvc, /*php_string*/ $mapName, /*php_bool*/ $persist) {
        $this->bHasNewSelection = false;
        $this->map = $map;
        $this->resSvc = $resSvc; 
        $this->mapName = $mapName;
        $this->persist = $persist;
    }
    
    public function HasNewSelection() { return $this->bHasNewSelection; }
    
    public function Update(MgSelectionBase $selection, MgFeatureInformation $featInfo, /*php_bool*/ $bAppend) {
        $map = $this->map;
        $resSvc = $this->resSvc; 
        $mapName = $this->mapName;
        if ($this->persist) {
            $sel = $featInfo->GetSelection();
            if ($sel != null) {
                $selXml = $sel->ToXml();
                //$this->app->log->debug("Query selection:\n$selXml");
                if ($bAppend) {
                    $selOrig = new MgSelection($map);
                    $selOrig->Open($resSvc, $mapName);
                    $selAppend = new MgSelection($map, $selXml);
                    MgUtils::MergeSelections($selOrig, $selAppend);
                    $selNewXml = $selOrig->ToXml();
                    //$this->app->log->debug("Appended selection:\n$selNewXml");
                    $selection->FromXml($selNewXml);
                } else {
                    $selection->FromXml($selXml);
                }
                $this->bHasNewSelection = true;
            }
            $selection->Save($resSvc, $mapName);
        }
    }
}