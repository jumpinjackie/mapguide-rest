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

require_once "controller.php";
require_once dirname(__FILE__)."/../util/readerchunkedresult.php";

class MgMapController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    const REQUEST_ATTRIBUTES       = 1;
    const REQUEST_INLINE_SELECTION = 2;
    const REQUEST_TOOLTIP          = 4;
    const REQUEST_HYPERLINK        = 8;

    const RenderSelection   = 1;
    const RenderLayers      = 2;
    const KeepSelection     = 4;
    const RenderBaseLayers  = 8;

    public function QueryMapFeatures($sessionId, $mapName) {
        $layerNames = $this->app->request->params("layernames");
        $geometry = $this->app->request->params("geometry");
        $maxFeatures = $this->app->request->params("maxfeatures");
        $selVariant = $this->app->request->params("selectionvariant");
        $selColor = $this->app->request->params("selectioncolor");
        $selFormat = $this->app->request->params("selectionformat");
        $persist = $this->app->request->params("persist");
        $reqData = $this->app->request->params("requestdata");
        $featFilter = $this->app->request->params("featurefilter");
        $layerAttFilter = $this->app->request->params("layerattributefilter");
        $format = $this->app->request->params("format");

        //Convert or coerce to defaults
        if ($format == null)
            $format = "xml";
        else
            $format = strtolower($format);

        if ($maxFeatures == null)
            $maxFeatures = -1;
        else
            $maxFeatures = intval($maxFeatures);

        if ($selFormat == null)
            $selFormat = "PNG";
        else
            $selFormat = strtoupper($selFormat);

        if ($layerAttFilter == null)
            $layerAttFilter = 3; //visible and selectable
        else
            $layerAttFilter = intval($layerAttFilter);

        if ($persist == null)
            $persist = true;
        else
            $persist = ($persist == "1");

        if ($reqData == null)
            $reqData = 0;
        else
            $reqData = intval($reqData);

        if ($selColor == null)
            $selColor = "0x0000FFFF";

        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);

        $layersToQuery = null;
        if ($layerNames != null) {
            $layersToQuery = new MgStringCollection();
            $names = explode(",", $layerNames);
            foreach ($names as $name) {
                $layersToQuery->Add($name);
            }
        }

        $wktRw = new MgWktReaderWriter();
        $selectGeom = $wktRw->Read($geometry);

        $variant = 0;
        if ($selVariant === "TOUCHES")
            $variant = MgFeatureSpatialOperations::Touches;
        else if ($selVariant === "INTERSECTS")
            $variant = MgFeatureSpatialOperations::Intersects;
        else if ($selVariant === "WITHIN")
            $variant = MgFeatureSpatialOperations::Within;
        else if ($selVariant === "ENVELOPEINTERSECTS")
            $variant = MgFeatureSpatialOperations::EnvelopeIntersects;

        $featInfo = $renderSvc->QueryFeatures($map, $layersToQuery, $selectGeom, $variant, $featFilter, $maxFeatures, $layerAttFilter);
        $bHasNewSelection = false;
        if ($persist) {
            $sel = $featInfo->GetSelection();
            if ($sel != null) {
                $selection->FromXml($sel->ToXml());
                $bHasNewSelection = true;
            }
            $selection->Save($resSvc, $mapName);
        }

        // Render an image of this selection if requested
        $inlineSelectionImg = null;
        if ((($reqData & self::REQUEST_INLINE_SELECTION) == self::REQUEST_INLINE_SELECTION) && $bHasNewSelection) {
            $color = new MgColor($selColor);
            $renderOpts = new MgRenderingOptions($selFormat, self::RenderSelection | self::KeepSelection, $color);
            $inlineSelectionImg = $renderSvc->RenderDynamicOverlay($map, $selection, $renderOpts);
        }

        // Collect any attributes of selected features
        $bRequestAttributes = (($reqData & self::REQUEST_ATTRIBUTES) == self::REQUEST_ATTRIBUTES);

        $xml = $this->CollectQueryMapFeaturesResult($resSvc, $reqData, $featInfo, $selection, $bRequestAttributes, $inlineSelectionImg);

        $bs = new MgByteSource($xml, strlen($xml));
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->app->response->header("Content-Type", MgMimeType::Xml);
            $this->OutputByteReader($br);
        }
    }

    private function CollectQueryMapFeaturesResult($resSvc, $reqData, $featInfo, $selection, $bRequestAttributes, $inlineSelectionImg) {
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<FeatureInformation>\n";

        $tooltip = "";
        $hyperlink = "";
        if ($featInfo != null) {
            $tooltip = $featInfo->GetTooltip();
            $hyperlink = $featInfo->GetHyperlink();
        }

        $selXml = $selection->ToXml();
        if (strlen($selXml) > 0) {
            //Need to strip the XML prolog from this fragment
            $fsdoc = new DOMDocument();
            $fsdoc->loadXML($selXml);
            $selXml = $fsdoc->saveXML($fsdoc->documentElement);
            $xml .= $selXml;
        } else {
            $xml .= "<FeatureSet />\n";
        }
        if ((($reqData & self::REQUEST_TOOLTIP) == self::REQUEST_TOOLTIP) && strlen($tooltip) > 0) {
            $xml .= "<Tooltip>".MgUtils::EscapeXmlChars($tooltip)."</Tooltip>\n";
        } else {
            $xml .= "<Tooltip />\n";
        }
        if ((($reqData & self::REQUEST_HYPERLINK) == self::REQUEST_HYPERLINK) && strlen($hyperlink) > 0) {
            $xml .= "<Hyperlink>".MgUtils::EscapeXmlChars($hyperlink)."</Hyperlink>\n";   
        } else {
            $xml .= "<Hyperlink />\n";
        }
        if ((($reqData & self::REQUEST_INLINE_SELECTION) == self::REQUEST_INLINE_SELECTION) && $inlineSelectionImg != null) {
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
                        $xml .= "<Feature>\n";
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

    public function CreateMap($resId) {
        $mdfIdStr = $this->app->request->params("mapdefinition");
        if ($mdfIdStr == null) {
            $this->app->halt(400, "Missing required parameter: mapdefinition"); //TODO: Localize
        } else {
            $mdfId = new MgResourceIdentifier($mdfIdStr);
            if ($mdfId->GetResourceType() != MgResourceType::MapDefinition) {
                $this->app->halt(400, "Parameter 'mapdefinition' is not a Map Definition resource id"); //TODO: Localize
            } else {
                //$this->EnsureAuthenticationForSite();
                $userInfo = new MgUserInformation($resId->GetRepositoryName());
                $siteConn = new MgSiteConnection();
                $siteConn->Open($userInfo);
                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

                $map = new MgMap();
                $map->Create($resSvc, $mdfId, $resId->GetName());
                $sel = new MgSelection($map);
                $sel->Save($resSvc, $resId->GetName());
                $map->Save($resSvc, $resId);

                $this->app->response->setStatus(201);
                $this->app->response->setBody($this->app->urlFor("session_resource_id", array("sessionId" => $resId->GetRepositoryName(), "resName" => $resId->GetName().".".$resId->GetResourceType())));
            }
        }
    }

    public function EnumerateMapLayers($sessionId, $mapName) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $layerNames = new MgStringCollection();
        $layers = $map->GetLayers();
        $layerCount = $layers->GetCount();

        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            $layerNames->Add($layer->GetName());
        }

        $this->OutputMgStringCollection($layerNames);
    }

    public function EnumerateMapLayerGroups($sessionId, $mapName) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $groupNames = new MgStringCollection();
        $groups = $map->GetLayerGroups();
        $groupCount = $groups->GetCount();

        for ($i = 0; $i < $groupCount; $i++) {
            $group = $groups->GetItem($i);
            $groupNames->Add($group->GetName());
        }

        $this->OutputMgStringCollection($groupNames);
    }

    public function GetSelectionXml($sessionId, $mapName) {
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $this->app->response->header("Content-Type", MgMimeType::Xml);
        $this->app->response->write($selection->ToXml());
    }

    public function GetSelectionLayerNames($sessionId, $mapName, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $layers = $selection->GetLayers();

        $output  = "<SelectedLayerCollection>";
        if ($layers != null) {
            $layerCount = $layers->GetCount();
            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $name = $layer->GetName();
                $count = $selection->GetSelectedFeaturesCount($layer, $layer->GetFeatureClassName());
                $objId = $layer->GetObjectId();
                $output .= "<SelectedLayer><Name>$name</Name><ObjectId>$objId</ObjectId><Count>$count</Count></SelectedLayer>";
            }
        }
        $output .= "</SelectedLayerCollection>";
        $bs = new MgByteSource($output, strlen($output));
        $br = $bs->GetReader();
        if ($fmt === "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->app->response->header("Content-Type", MgMimeType::Xml);
            $this->OutputByteReader($br);
        }
    }

    public function GetSelectedFeatures($sessionId, $mapName, $layerName, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "geojson"));
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $layers = $selection->GetLayers();
        $lidx = -1;
        $layerCount = $layers->GetCount();
        for ($i = 0; $i < $layerCount; $i++) {
            $currentlayer = $layers->GetItem($i);
            if ($currentlayer->GetName() == $layerName) {
                $lidx = $i;
                break;
            }
        }
        if ($lidx < 0) {
            $this->app->halt(404, "Layer ($layerName) not found in selection"); //TODO: Localize
        } else {
            $layer = $layers->GetItem($lidx);
            $bMapped = ($this->GetRequestParameter("mappedonly", 0) == 1);
            $transformto = $this->GetRequestParameter("transformto", "");
            $transform = null;
            if ($transformto !== "") {
                $resId = new MgResourceIdentifier($layer->GetFeatureSourceId());
                $tokens = explode(":", $layer->GetFeatureClassName());
                $transform = MgUtils::GetTransform($featSvc, $resId, $tokens[0], $tokens[1], $transformto);
            }
            $reader = $selection->GetSelectedFeatures($layer, $layer->GetFeatureClassName(), $bMapped);
            $result = new MgReaderChunkedResult($this->app, $featSvc, $reader, -1);
            if ($transform != null)
                $result->SetTransform($transform);
            $result->Output($format);
        }
    }

    public function UpdateSelectionFromXml($sessionId, $mapName) {
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $xml = trim($this->app->request->getBody());
        $selection = new MgSelection($map);
        $selection->FromXml($xml);

        $selection->Save($resSvc, $mapName);
    }
}

?>