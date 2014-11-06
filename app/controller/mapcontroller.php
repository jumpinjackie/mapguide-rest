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
require_once "mappingservicecontroller.php";
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
            $persist = ($persist == "1" || $persist == "true");

        if ($reqData == null)
            $reqData = 0;
        else
            $reqData = intval($reqData);

        if ($selColor == null)
            $selColor = "0x0000FFFF";

        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $admin = new MgServerAdmin();
        $admin->Open($this->userInfo);
        $version = explode(".", $admin->GetSiteVersion());
        $bCanUseNative = false;
        if (intval($version[0]) > 2) { //3.0 or greater
            $bCanUseNative = true;
        } else if (intval($version[0]) == 2 && intval($version[1]) >= 6) { //2.6 or greater
            $bCanUseNative = true;
        }
        if ($bCanUseNative) {
            $req = new MgHttpRequest("");
            $param = $req->GetRequestParam();

            $param->AddParameter("OPERATION", "QUERYMAPFEATURES");
            $param->AddParameter("VERSION", "2.6.0");
            $param->AddParameter("SESSION", $sessionId);
            $param->AddParameter("MAPNAME", $mapName);

            $param->AddParameter("GEOMETRY", $geometry);
            $param->AddParameter("SELECTIONVARIANT", $selVariant);
            $param->AddParameter("MAXFEATURES", $maxFeatures);
            $param->AddParameter("LAYERNAMES", $layerNames);
            $param->AddParameter("PERSIST", $persist ? "1" : "0");
            $param->AddParameter("LAYERATTRIBUTEFILTER", $layerAttFilter);
            if ($featFilter == null)
                $param->AddParameter("FEATUREFILTER", $featFilter);

            $param->AddParameter("REQUESTDATA", $reqData);
            $param->AddParameter("SELECTIONCOLOR", $selColor);
            $param->AddParameter("SELECTIONFORMAT", $selFormat);

            if ($format === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $this->ExecuteHttpRequest($req);
        } else { //Shim the response
            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);

            $layersToQuery = null;
            if ($layerNames != null) {
                $layersToQuery = new MgStringCollection();
                $names = explode(",", $layerNames);
                foreach ($names as $name) {
                    $layersToQuery->Add($name);
                }
            }

            $variant = 0;
            if ($selVariant === "TOUCHES")
                $variant = MgFeatureSpatialOperations::Touches;
            else if ($selVariant === "INTERSECTS")
                $variant = MgFeatureSpatialOperations::Intersects;
            else if ($selVariant === "WITHIN")
                $variant = MgFeatureSpatialOperations::Within;
            else if ($selVariant === "ENVELOPEINTERSECTS")
                $variant = MgFeatureSpatialOperations::EnvelopeIntersects;

            $map = new MgMap($siteConn);
            $map->Open($mapName);
            $selection = new MgSelection($map);

            $wktRw = new MgWktReaderWriter();
            $selectGeom = $wktRw->Read($geometry);

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
            $bs->SetMimeType(MgMimeType::Xml);
            $br = $bs->GetReader();
            if ($format == "json") {
                $this->OutputXmlByteReaderAsJson($br);
            } else {
                $this->OutputByteReader($br);
            }
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
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "mapdefinition"));
        } else {
            $mdfId = new MgResourceIdentifier($mdfIdStr);
            if ($mdfId->GetResourceType() != MgResourceType::MapDefinition) {
                $this->app->halt(400, $this->app->localizer->getText("E_INVALID_MAP_DEFINITION_PARAMETER", "mapdefinition"));
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

    public function EnumerateMapLayers($sessionId, $mapName, $format) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $reqFeatures = $this->app->request->params("requestedfeatures");
        $iconFormat = $this->app->request->params("iconformat");
        $iconWidth = $this->app->request->params("iconwidth");
        $iconHeight = $this->app->request->params("iconheight");
        $iconsPerScaleRange = $this->app->request->params("iconsperscalerange");
        $groupName = $this->app->request->params("group");

        //Assign default values or coerce existing ones to their expected types
        if ($reqFeatures != null) {
            $reqFeatures = intval($reqFeatures);
        }

        if ($iconFormat == null) {
            $iconFormat = "PNG";
        }

        if ($iconWidth == null) {
            $iconWidth = 16;
        } else {
            $iconWidth = intval($iconWidth);
        }

        if ($iconHeight == null) {
            $iconHeight = 16;
        } else {
            $iconHeight = intval($iconHeight);
        }

        if ($iconsPerScaleRange == null) {
            $iconsPerScaleRange = 25;
        } else {
            $iconsPerScaleRange = intval($iconsPerScaleRange);
        }

        if ($format == null) {
            $format = "xml";
        } else {
            $format = strtolower($format);
        }

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $layers = $map->GetLayers();
        $layerCount = $layers->GetCount();

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

        $output = "<LayerCollection>";
        $layerDefinitionMap = array();
        //Build our LayerDefinition map for code below that requires it
        if (($reqFeatures & MgMappingServiceController::REQUEST_LAYER_ICONS) == MgMappingServiceController::REQUEST_LAYER_ICONS) {
            $layerIds = new MgStringCollection();
            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $parent = $layer->GetGroup();
                if ($groupName != null && $parent != null && $parent->GetName() != $groupName)
                    continue;
                $ldfId = $layer->GetLayerDefinition();
                $layerIds->Add($ldfId->ToString());
            }

            $layerContents = $resSvc->GetResourceContents($layerIds, null);
            $layerIdCount = $layerIds->GetCount();
            for ($i = 0; $i < $layerIdCount; $i++) {
                $ldfId = $layerIds->GetItem($i);
                $content = $layerContents->GetItem($i);
                $layerDefinitionMap[$ldfId] = $content;
            }
        }
        // ----------- Some pre-processing before we do groups/layers ------------- //
        $doc = new DOMDocument();
        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            $parent = $layer->GetGroup();
            if ($groupName != null && $parent != null && $parent->GetName() != $groupName)
                continue;
            $ldf = $layer->GetLayerDefinition();
            $layerId = $ldf->ToString();

            $layerDoc = null;
            if (array_key_exists($layerId, $layerDefinitionMap)) {
                $doc->loadXML($layerDefinitionMap[$layerId]);
                $layerDoc = $doc;
            }

            $output .= MgMappingServiceController::CreateLayerItem($reqFeatures, $iconsPerScaleRange, $iconFormat, $iconWidth, $iconHeight, $layer, $parent, $layerDoc, $mappingSvc);
        }
        $output .= "</LayerCollection>";

        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function EnumerateMapLayerGroups($sessionId, $mapName, $format) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $groups = $map->GetLayerGroups();
        $groupCount = $groups->GetCount();

        $output = "<GroupCollection>";
        for ($i = 0; $i < $groupCount; $i++) {
            $group = $groups->GetItem($i);
            $parent = $group->GetGroup();
            $output .= MgMappingServiceController::CreateGroupItem($group, $parent);
        }
        $output .= "</GroupCollection>";

        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
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
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($fmt === "json") {
            if ($layers == null) {
                //HACK: Bug (?) in how xml2json processes empty tags
                $this->app->response->header("Content-Type", MgMimeType::Json);
                $this->app->response->write('{ "SelectedLayerCollection": [] }');
            } else {
                $this->OutputXmlByteReaderAsJson($br);
            }
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function GetSelectedFeatures($sessionId, $mapName, $layerName, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "geojson", "html"));

        $pageSize = $this->GetRequestParameter("pagesize", -1);
        $pageNo = $this->GetRequestParameter("page", -1);
        $orientation = $this->GetRequestParameter("orientation", "h");

        //Internal debugging flag
        $chunk = $this->GetBooleanRequestParameter("chunk", true);

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
            $this->app->halt(404, $this->app->localizer->getText("E_LAYER_NOT_IN_SELECTION", $layerName));
        } else {
            $layer = $layers->GetItem($lidx);
            $bMapped = ($this->GetBooleanRequestParameter("mappedonly", "0") == "1");
            $transformto = $this->GetRequestParameter("transformto", "");
            $transform = null;
            if ($transformto !== "") {
                $resId = new MgResourceIdentifier($layer->GetFeatureSourceId());
                $tokens = explode(":", $layer->GetFeatureClassName());
                $transform = MgUtils::GetTransform($featSvc, $resId, $tokens[0], $tokens[1], $transformto);
            }

            $owriter = null;
            if ($chunk === "0")
                $owriter = new MgSlimChunkWriter($this->app);
            else
                $owriter = new MgHttpChunkWriter();

            //NOTE: This does not do a query to ascertain a total, this is already a pre-computed property of the selection set.
            $total = $selection->GetSelectedFeaturesCount($layer, $layer->GetFeatureClassName());
            $reader = $selection->GetSelectedFeatures($layer, $layer->GetFeatureClassName(), $bMapped);
            if ($pageSize > 0) {
                $pageReader = new MgPaginatedFeatureReader($reader, $pageSize, $pageNo, $total);
                $result = new MgReaderChunkedResult($featSvc, $pageReader, -1, $owriter);
            } else {
                $result = new MgReaderChunkedResult($featSvc, $reader, -1, $owriter);
            }
            $result->CheckAndSetDownloadHeaders($this->app, $format);
            if ($transform != null)
                $result->SetTransform($transform);
            if ($fmt === "html") {
                $result->SetAttributeDisplayOrientation($orientation);
                $result->SetBaseUrl($this->app->config("SelfUrl"));
                $result->SetThisUrl($this->app->config("SelfUrl").$this->app->request->getPathInfo(), $this->app->request->params());
            }
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