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
require_once dirname(__FILE__)."/../util/pdfplotter.php";

class MgMappingServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format) {
        $resIdStr = $resId->ToString();
        $width = $this->app->request->params("width");
        $height = $this->app->request->params("height");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $width, $height, $scale, $geomtype, $themecat, $format) {
            $param->AddParameter("OPERATION", "GETLEGENDIMAGE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("LAYERDEFINITION", $resIdStr);
            $param->AddParameter("SCALE", $scale);
            $param->AddParameter("TYPE", $geomtype);
            $param->AddParameter("FORMAT", strtoupper($format));
            $param->AddParameter("THEMECATEGORY", $themecat);
            if ($width != null)
                $param->AddParameter("WIDTH", $width);
            if ($height != null)
                $param->AddParameter("HEIGHT", $height);
            $that->ExecuteHttpRequest($req);
        });
    }

    const REQUEST_LAYER_STRUCTURE = 1;
    const REQUEST_LAYER_ICONS = 2;
    const REQUEST_LAYER_FEATURE_SOURCE = 4;

    public static function CreateGroupItem($group, $parent) {
        $xml  = "<Group>\n";
        $xml .= "<Name>".$group->GetName()."</Name>\n";
        $xml .= "<Type>".$group->GetLayerGroupType()."</Type>\n";
        $xml .= "<LegendLabel>".MgUtils::EscapeXmlChars($group->GetLegendLabel())."</LegendLabel>\n";
        $xml .= "<ObjectId>".$group->GetObjectId()."</ObjectId>\n";
        if ($parent != null) {
            $xml .= "<ParentId>".$parent->GetObjectId()."</ParentId>\n";
        }
        $xml .= "<DisplayInLegend>".($group->GetDisplayInLegend() ? "true" : "false")."</DisplayInLegend>\n";
        $xml .= "<ExpandInLegend>".($group->GetExpandInLegend() ? "true" : "false")."</ExpandInLegend>\n";
        $xml .= "<Visible>".($group->GetVisible() ? "true" : "false")."</Visible>\n";
        $xml .= "<ActuallyVisible>".($group->IsVisible() ? "true" : "false")."</ActuallyVisible>\n";
        $xml .= "</Group>";
        return $xml;
    }

    public static function CreateLayerItem($reqFeatures, $iconsPerScaleRange, $iconFormat, $iconWidth, $iconHeight, $layer, $parent, $xmldoc, $mappingService) {
        $xml  = "<Layer>\n";
        $xml .= "<Name>".$layer->GetName()."</Name>\n";
        $xml .= "<Type>".$layer->GetLayerType()."</Type>\n";
        $xml .= "<LegendLabel>".MgUtils::EscapeXmlChars($layer->GetLegendLabel())."</LegendLabel>\n";
        $xml .= "<ObjectId>".$layer->GetObjectId()."</ObjectId>\n";
        if ($parent != null) {
            $xml .= "<ParentId>".$parent->GetObjectId()."</ParentId>\n";
        }
        $xml .= "<Selectable>".($layer->GetSelectable() ? "true" : "false")."</Selectable>";
        $xml .= "<DisplayInLegend>".($layer->GetDisplayInLegend() ? "true" : "false")."</DisplayInLegend>\n";
        $xml .= "<ExpandInLegend>".($layer->GetExpandInLegend() ? "true" : "false")."</ExpandInLegend>\n";
        $xml .= "<Visible>".($layer->GetVisible() ? "true" : "false")."</Visible>\n";
        $xml .= "<ActuallyVisible>".($layer->IsVisible() ? "true" : "false")."</ActuallyVisible>\n";
        $ldfId = $layer->GetLayerDefinition();
        $xml .= "<LayerDefinition>".$ldfId->ToString()."</LayerDefinition>\n";
        // ----------------------- Optional things if requested ------------------------- //
        if (($reqFeatures & self::REQUEST_LAYER_FEATURE_SOURCE) == self::REQUEST_LAYER_FEATURE_SOURCE) {
            $xml .= "<FeatureSource>\n";
            $xml .= "<ResourceId>".$layer->GetFeatureSourceId()."</ResourceId>\n";
            $xml .= "<ClassName>".$layer->GetFeatureClassName()."</ClassName>\n";
            $xml .= "<Geometry>".$layer->GetFeatureGeometryName()."</Geometry>\n";
            $xml .= "</FeatureSource>\n";
        }
        //Following code ripped from Fusion's LoadMap.php and LoadScaleRanges.php
        if ($xmldoc != null) {
            $type = 0;
            $scaleRanges = $xmldoc->getElementsByTagName('VectorScaleRange');
            if($scaleRanges->length == 0) {
                $scaleRanges = $xmldoc->getElementsByTagName('GridScaleRange');
                if($scaleRanges->length == 0) {
                    $scaleRanges = $xmldoc->getElementsByTagName('DrawingLayerDefinition');
                    if($scaleRanges->length == 1) {
                        $type = 2;
                    }
                } else {
                    $type = 1;
                }
            }
            $typeStyles = array("PointTypeStyle", "LineTypeStyle", "AreaTypeStyle", "CompositeTypeStyle");
            $ruleNames = array("PointRule", "LineRule", "AreaRule", "CompositeRule");
            for($sc = 0; $sc < $scaleRanges->length; $sc++)
            {
                $scaleRange = $scaleRanges->item($sc);
                $minElt = $scaleRange->getElementsByTagName('MinScale');
                $maxElt = $scaleRange->getElementsByTagName('MaxScale');
                $minScale = "0";
                $maxScale = 'infinity';  // as MDF's VectorScaleRange::MAX_MAP_SCALE
                if($minElt->length > 0)
                    $minScale = $minElt->item(0)->nodeValue;
                if($maxElt->length > 0)
                    $maxScale = $maxElt->item(0)->nodeValue;

                if ($type != 0) {
                    break;
                }

                $scaleVal = 42;
                if (strcmp($maxScale, "infinity") == 0)
                    $scaleVal = intval($minScale);
                else
                    $scaleVal = (intval($minScale) + intval($maxScale)) / 2.0;

                $xml .= "<ScaleRange>\n<MinScale>$minScale</MinScale>\n<MaxScale>$maxScale</MaxScale>\n";

                // 2 passes: First to compile icon count (to determine compression), second to write the actual XML
                $iconCount = 0;
                for ($ts=0, $count = count($typeStyles); $ts < $count; $ts++) {
                    $typeStyle = $scaleRange->getElementsByTagName($typeStyles[$ts]);
                    for ($st = 0; $st < $typeStyle->length; $st++) {
                        // We will check if this typestyle is going to be shown in the legend
                        $showInLegend = $typeStyle->item($st)->getElementsByTagName("ShowInLegend");
                        if($showInLegend->length > 0)
                            if($showInLegend->item(0)->nodeValue == "false")
                                continue;   // This typestyle does not need to be shown in the legend

                        $rules = $typeStyle->item($st)->getElementsByTagName($ruleNames[$ts]);
                        $iconCount += $rules->length;                            
                    }
                }
                $bCompress = ($iconCount > $iconsPerScaleRange);

                for ($ts=0, $count = count($typeStyles); $ts < $count; $ts++) {
                    $typeStyle = $scaleRange->getElementsByTagName($typeStyles[$ts]);
                    $catIndex = 0;

                    if ($typeStyle->length == 0)
                        continue;

                    $xml .= "<FeatureStyle>\n";
                    $xml .= "<Type>".($ts+1)."</Type>\n";

                    for($st = 0; $st < $typeStyle->length; $st++) {

                        // We will check if this typestyle is going to be shown in the legend
                        $showInLegend = $typeStyle->item($st)->getElementsByTagName("ShowInLegend");
                        if($showInLegend->length > 0)
                            if($showInLegend->item(0)->nodeValue == "false")
                                continue;   // This typestyle does not need to be shown in the legend

                        $rules = $typeStyle->item($st)->getElementsByTagName($ruleNames[$ts]);
                        for($r = 0; $r < $rules->length; $r++) {

                            $bRequestIcon = false;
                            if (!$bCompress) {
                                $bRequestIcon = true;
                            } else { //This is a compressed theme
                                $bRequestIcon = ($r == 0 || $r == ($rules->length - 1)); //Only first and last rule
                            }

                            $rule = $rules->item($r);
                            $label = $rule->getElementsByTagName("LegendLabel");
                            $filter = $rule->getElementsByTagName("Filter");

                            $labelText = MgUtils::EscapeXmlChars($label->length==1? $label->item(0)->nodeValue: "");
                            $filterText = MgUtils::EscapeXmlChars($filter->length==1? $filter->item(0)->nodeValue: "");
                            $geomType = ($ts+1);
                            $themeCategory = $catIndex++;

                            $xml .= "<Rule>\n<LegendLabel>$labelText</LegendLabel>\n<Filter>$filterText</Filter>\n";
                            if ($bRequestIcon) {
                                $xml .= "<Icon>\n";
                                $xml .= MgUtils::GetLegendImageInline($mappingService, $ldfId, $scaleVal, $geomType, $themeCategory, $iconWidth, $iconHeight, $iconFormat);
                                $xml .= "</Icon>\n";
                            }
                            $xml .= "</Rule>";
                        }
                    }
                    $xml .= "</FeatureStyle>";
                }

                $xml .= "</ScaleRange>";
            }
        } else {
            $xml .= "<ScaleRange />";
        }

        $xml .= "</Layer>";
        return $xml;
    }

    private function DescribeRuntimeMapXml($mapDefinition, $map, $sessionId, $mapName, $iconFormat, $iconWidth, $iconHeight, $reqFeatures, $iconsPerScaleRange, $resSvc, $mappingSvc) {
        //TODO: Caching opportunity here

        $admin = new MgServerAdmin();
        $admin->Open($this->userInfo);
        $siteVersion = $admin->GetSiteVersion();

        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<RuntimeMap xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"RuntimeMap-2.6.0.xsd\">\n";
        // ---------------------- Site Version  --------------------------- //
        $xml .= "<SiteVersion>$siteVersion</SiteVersion>\n";
        // ---------------------- Session ID --------------------------- //
        $xml .= "<SessionId>$sessionId</SessionId>\n";
        // ---------------------- Map Name --------------------------- //
        $xml .= "<Name>$mapName</Name>\n";
        // ---------------------- Map Definition --------------------------- //
        $xml .= "<MapDefinition>$mapDefinition</MapDefinition>\n";
        // ---------------------- Background Color --------------------------- //
        $bgColor = $map->GetBackgroundColor();
        $xml .= "<BackgroundColor>$bgColor</BackgroundColor>\n";
        // ---------------------- Display DPI --------------------------- //
        $dpi = $map->GetDisplayDpi();
        $xml .= "<DisplayDpi>$dpi</DisplayDpi>";
        // ---------------------- Icon MIME Type --------------------------- //
        if (($reqFeatures & self::REQUEST_LAYER_ICONS) == self::REQUEST_LAYER_ICONS) {
            switch ($iconFormat) {
                case "JPG":
                    $xml .= "<IconMimeType>".MgMimeType::Jpeg."</IconMimeType>\n";
                    break;
                case "GIF":
                    $xml .= "<IconMimeType>".MgMimeType::Gif."</IconMimeType>\n";
                    break;
                case "PNG8":
                    $xml .= "<IconMimeType>".MgMimeType::Png."</IconMimeType>\n";
                    break;
                default:
                    $xml .= "<IconMimeType>".MgMimeType::Png."</IconMimeType>\n";
                    break;
            }
        }
        // ---------------------- Coordinate System --------------------------- //
        $csFactory = new MgCoordinateSystemFactory();
        $metersPerUnit = 1.0;
        $wkt = $map->GetMapSRS();
        $csCode = "";
        $epsg = "";
        try {
            $cs = $csFactory->Create($wkt);
            $metersPerUnit = $cs->ConvertCoordinateSystemUnitsToMeters(1.0);
            $epsg = $cs->GetEpsgCode();
            $csCode = $cs->GetCsCode();
        } catch (MgException $ex) {

        }
        $xml .= "<CoordinateSystem>\n<Wkt>$wkt</Wkt>\n<MentorCode>$csCode</MentorCode>\n<EpsgCode>$epsg</EpsgCode>\n<MetersPerUnit>$metersPerUnit</MetersPerUnit>\n</CoordinateSystem>";
        // ---------------------- Map Extents--------------------------- //
        $extents = $map->GetMapExtent();
        $ll = $extents->GetLowerLeftCoordinate();
        $ur = $extents->GetUpperRightCoordinate();
        $minX = $ll->GetX();
        $minY = $ll->GetY();
        $maxX = $ur->GetX();
        $maxY = $ur->GetY();
        $xml .= "<Extents>\n<LowerLeftCoordinate><X>$minX</X><Y>$minY</Y></LowerLeftCoordinate>\n<UpperRightCoordinate><X>$maxX</X><Y>$maxY</Y></UpperRightCoordinate></Extents>\n";
        
        $layerDefinitionMap = array();

        // ---------------------- Optional things if requested --------------------------- //
        if (($reqFeatures & self::REQUEST_LAYER_STRUCTURE) == self::REQUEST_LAYER_STRUCTURE) {
            $layers = $map->GetLayers();
            $layerCount = $layers->GetCount();

            //Build our LayerDefinition map for code below that requires it
            if (($reqFeatures & self::REQUEST_LAYER_ICONS) == self::REQUEST_LAYER_ICONS) {
                $layerIds = new MgStringCollection();
                for ($i = 0; $i < $layerCount; $i++) {
                    $layer = $layers->GetItem($i);
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
            $groups = $map->GetLayerGroups();
            $groupCount = $groups->GetCount();
            for ($i = 0; $i < $groupCount; $i++) {
                $group = $groups->GetItem($i);
                $parent = $group->GetGroup();
                $xml .= self::CreateGroupItem($group, $parent);
            }

            $doc = new DOMDocument();
            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $parent = $layer->GetGroup();
                $ldf = $layer->GetLayerDefinition();
                $layerId = $ldf->ToString();

                $layerDoc = null;
                if (array_key_exists($layerId, $layerDefinitionMap)) {
                    $doc->loadXML($layerDefinitionMap[$layerId]);
                    $layerDoc = $doc;
                }

                $xml .= self::CreateLayerItem($reqFeatures, $iconsPerScaleRange, $iconFormat, $iconWidth, $iconHeight, $layer, $parent, $layerDoc, $mappingSvc);
            }
        } else {
            //Base Layer Groups need to be outputted regardless, otherwise a client application doesn't have enough information to build GETTILEIMAGE requests
            $groups = $map->GetLayerGroups();
            $groupCount = $groups->GetCount();
            for ($i = 0; $i < $groupCount; $i++) {
                $group = $groups->GetItem($i);
                if ($group->GetLayerGroupType() != MgLayerGroupType::BaseMap) {
                    continue;
                }

                $parent = $group->GetGroup();
                $xml .= self::CreateGroupItem($group, $parent);
            }
        }

        // ------------------------ Finite Display Scales (if any) ------------------------- //
        $fsCount = $map->GetFiniteDisplayScaleCount();
        if ($fsCount > 0) {
            for ($i = 0; $i < $fsCount; $i++) {
                $xml .= "<FiniteDisplayScale>";
                $xml .= $map->GetFiniteDisplayScaleAt($i);
                $xml .= "</FiniteDisplayScale>";
            }
        }

        $xml .= "</RuntimeMap>";

        $bs = new MgByteSource($xml, strlen($xml));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();

        return $br;
    }

    public function CreateRuntimeMap($format) {
        $session = $this->app->request->params("session");
        $mapDefIdStr = $this->app->request->params("mapdefinition");
        $mapName = $this->app->request->params("targetmapname");
        $reqFeatures = $this->app->request->params("requestedfeatures");
        $iconFormat = $this->app->request->params("iconformat");
        $iconWidth = $this->app->request->params("iconwidth");
        $iconHeight = $this->app->request->params("iconheight");
        $iconsPerScaleRange = $this->app->request->params("iconsperscalerange");

        $this->EnsureAuthenticationForSite($session, true);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        if ($session == null) {
            $site = $siteConn->GetSite();
            $session = $site->CreateSession();

            $this->userInfo = new MgUserInformation($session);
            $siteConn->Open($this->userInfo);
        }
        
        $mdfId = new MgResourceIdentifier($mapDefIdStr);
        if ($mapName == null) {
            $mapName = $mdfId->GetName();
        }

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

            $param->AddParameter("OPERATION", "CREATERUNTIMEMAP");
            //Hmmm, this may violate REST API design, but if we're on MGOS 3.0 or newer, we must return linked tile set 
            //information to the client if applicable. Meaning this API could return 2 different results depending on the
            //underlying MGOS version.
            if (intval($version[0]) >= 3)
                $param->AddParameter("VERSION", "3.0.0");
            else
                $param->AddParameter("VERSION", "2.6.0");
            $param->AddParameter("SESSION", $session);
            $param->AddParameter("MAPDEFINITION", $mapDefIdStr);
            $param->AddParameter("TARGETMAPNAME", $mapName);
            $param->AddParameter("REQUESTEDFEATURES", $reqFeatures);
            $param->AddParameter("ICONSPERSCALERANGE", $iconsPerScaleRange);
            $param->AddParameter("ICONFORMAT", $iconFormat);
            $param->AddParameter("ICONWIDTH", $iconWidth);
            $param->AddParameter("ICONHEIGHT", $iconHeight);

            if ($format === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $this->ExecuteHttpRequest($req);
        } else { //Shim the response
            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

            $map = new MgMap($siteConn);
            $map->Create($mdfId, $mapName);
            $mapStateId = new MgResourceIdentifier("Session:$session//$mapName.Map");
            $sel = new MgSelection($map);

            $sel->Save($resSvc, $mapName);
            $map->Save($resSvc, $mapStateId);

            $br = $this->DescribeRuntimeMapXml($mapDefIdStr, $map, $session, $mapName, $iconFormat, $iconWidth, $iconHeight, $reqFeatures, $iconsPerScaleRange, $resSvc, $mappingSvc);
            if ($format == "json") {
                $this->OutputXmlByteReaderAsJson($br);
            } else {
                $this->OutputByteReader($br);
            }
        }
    }

    public function DescribeRuntimeMap($sessionId, $mapName, $format) {
        $reqFeatures = $this->app->request->params("requestedfeatures");
        $iconFormat = $this->app->request->params("iconformat");
        $iconWidth = $this->app->request->params("iconwidth");
        $iconHeight = $this->app->request->params("iconheight");
        $iconsPerScaleRange = $this->app->request->params("iconsperscalerange");

        $this->EnsureAuthenticationForSite($sessionId, false);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        
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

            $param->AddParameter("OPERATION", "DESCRIBERUNTIMEMAP");
            $param->AddParameter("VERSION", "2.6.0");
            $param->AddParameter("SESSION", $sessionId);
            $param->AddParameter("MAPNAME", $mapName);
            $param->AddParameter("REQUESTEDFEATURES", $reqFeatures);
            $param->AddParameter("ICONSPERSCALERANGE", $iconsPerScaleRange);
            $param->AddParameter("ICONFORMAT", $iconFormat);
            $param->AddParameter("ICONWIDTH", $iconWidth);
            $param->AddParameter("ICONHEIGHT", $iconHeight);

            if ($format === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $this->ExecuteHttpRequest($req);
        } else { //Shim the response
            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

            $map = new MgMap($siteConn);
            $map->Open($mapName);
            $mapDefId = $map->GetMapDefinition();
            $mapDefIdStr = $mapDefId->ToString();

            $br = $this->DescribeRuntimeMapXml($mapDefIdStr, $map, $sessionId, $mapName, $iconFormat, $iconWidth, $iconHeight, $reqFeatures, $iconsPerScaleRange, $resSvc, $mappingSvc);
            if ($format == "json") {
                $this->OutputXmlByteReaderAsJson($br);
            } else {
                $this->OutputByteReader($br);
            }
        }
    }

    public function GeneratePlotFromMapDefinition($resId, $format) {
        $fmt = $this->ValidateRepresentation($format, array("dwf", "pdf"));
        $x = $this->GetRequestParameter("x", "");
        $y = $this->GetRequestParameter("y", "");
        $scale = $this->GetRequestParameter("scale", "");

        if ($x == "")
            $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "x"), MgMimeType::Html);
        if ($y == "")
            $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "y"), MgMimeType::Html);
        if ($scale == "")
            $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "scale"), MgMimeType::Html);

        $this->EnsureAuthenticationForSite();
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $map = new MgMap($siteConn);
        $map->Create($resId, "Plot");

        $title = $this->GetRequestParameter("title", "");
        $paperSize = $this->GetRequestParameter("papersize", 'A4');
        $orientation = $this->GetRequestParameter("orientation", 'P');
        $marginLeft = floatval($this->GetRequestParameter("marginleft", 0.5));
        $marginRight = floatval($this->GetRequestParameter("marginright", 0.5));
        $marginTop = floatval($this->GetRequestParameter("margintop", ($title == "") ? 0.5: 1.0));
        $marginBottom = floatval($this->GetRequestParameter("marginbottom", 0.5));

        $geomFact = new MgGeometryFactory();
        $center = $geomFact->CreateCoordinateXY(floatval($x), floatval($y));

        if ($fmt == "dwf") {
            $size = MgUtils::GetPaperSize($this->app, $paperSize);
            //If landscape, flip the width/height
            $width = ($orientation == 'L') ? MgUtils::MMToIn($size[1]) : MgUtils::MMToIn($size[0]);
            $height = ($orientation == 'L') ? MgUtils::MMToIn($size[0]) : MgUtils::MMToIn($size[1]);
            $printLayoutStr = $this->GetRequestParameter("printlayout", null);
            $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

            $dwfVersion = new MgDwfVersion("6.01", "1.2");
            $plotSpec = new MgPlotSpecification($width, $height, MgPageUnitsType::Inches);
            $plotSpec->SetMargins($marginLeft, $marginTop, $marginRight, $marginBottom);

            $layout = null;
            if ($printLayoutStr != null) {
                $layoutRes = new MgResourceIdentifier($printLayoutStr);
                $layout = new MgLayout($layoutRes, $title, MgPageUnitsType::Inches);
            }

            $br = $mappingSvc->GeneratePlot($map, $center, floatval($scale), $plotSpec, $layout, $dwfVersion);
            //Apply download headers
            $downloadFlag = $this->app->request->params("download");
            if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
                $name = $this->app->request->params("downloadname");
                if (!$name) {
                    $name = "download";
                }
                $name .= ".dwf";
                $this->app->response->header("Content-Disposition", "attachment; filename=".$name);
            }
            $this->OutputByteReader($br);
        } else { //pdf
            //FIXME: Initial visibility state is inconsistent (probably because this map hasn't been rendered
            //yet). As a result, adding layered PDF support won't really work here.
            /*
            $bLayered = $this->app->request->params("layeredpdf");
            if ($bLayered == null)
                $bLayered = false;
            else
                $bLayered = ($bLayered == "1" || $bLayered == "true");
            */
            $renderingService = $siteConn->CreateService(MgServiceType::RenderingService);
            $plotter = new MgPdfPlotter($this->app, $renderingService, $map);
            $plotter->SetTitle($title);
            $plotter->SetPaperType($paperSize);
            $plotter->SetOrientation($orientation);
            $plotter->ShowCoordinates(true);
            $plotter->ShowNorthArrow(true);
            $plotter->ShowScaleBar(true);
            $plotter->ShowDisclaimer(true);
            //$plotter->SetLayered($bLayered);
            $plotter->SetMargins($marginTop, $marginBottom, $marginLeft, $marginRight);
            //Apply download headers
            $downloadFlag = $this->app->request->params("download");
            if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
                $name = $this->app->request->params("downloadname");
                if (!$name) {
                    $name = "download";
                }
                $name .= ".pdf";
                $plotter->Plot($center, floatval($scale), $name);
            } else {
                $plotter->Plot($center, floatval($scale));
            }
        }
    }

    public function GeneratePlot($sessionId, $mapName, $format) {
        $fmt = $this->ValidateRepresentation($format, array("dwf", "pdf"));
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

        $title = $this->GetRequestParameter("title", "");
        $paperSize = $this->GetRequestParameter("papersize", 'A4');
        $orientation = $this->GetRequestParameter("orientation", 'P');
        $marginLeft = floatval($this->GetRequestParameter("marginleft", 0.5));
        $marginRight = floatval($this->GetRequestParameter("marginright", 0.5));
        $marginTop = floatval($this->GetRequestParameter("margintop", ($title == "") ? 0.5: 1.0));
        $marginBottom = floatval($this->GetRequestParameter("marginbottom", 0.5));

        if ($fmt == "dwf") {
            $size = MgUtils::GetPaperSize($this->app, $paperSize);
            //If landscape, flip the width/height
            $width = ($orientation == 'L') ? MgUtils::MMToIn($size[1]) : MgUtils::MMToIn($size[0]);
            $height = ($orientation == 'L') ? MgUtils::MMToIn($size[0]) : MgUtils::MMToIn($size[1]);
            $printLayoutStr = $this->GetRequestParameter("printlayout", null);
            
            $dwfVersion = new MgDwfVersion("6.01", "1.2");
            $plotSpec = new MgPlotSpecification($width, $height, MgPageUnitsType::Inches);
            $plotSpec->SetMargins($marginLeft, $marginTop, $marginRight, $marginBottom);

            $layout = null;
            if ($printLayoutStr != null) {
                $layoutRes = new MgResourceIdentifier($printLayoutStr);
                $layout = new MgLayout($layoutRes, $title, MgPageUnitsType::Inches);
            }

            $br = $mappingSvc->GeneratePlot($map, $plotSpec, $layout, $dwfVersion);
            //Apply download headers
            $downloadFlag = $this->app->request->params("download");
            if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
                $name = $this->app->request->params("downloadname");
                if (!$name) {
                    $name = "download";
                }
                $name .= ".dwf";
                $this->app->response->header("Content-Disposition", "attachment; filename=".$name);
            }
            $this->OutputByteReader($br);
        } else { //pdf
            $bLayered = $this->app->request->params("layeredpdf");
            if ($bLayered == null)
                $bLayered = false;
            else
                $bLayered = ($bLayered == "1" || $bLayered == "true");

            $renderingService = $siteConn->CreateService(MgServiceType::RenderingService);
            $plotter = new MgPdfPlotter($this->app, $renderingService, $map);
            $plotter->SetTitle($title);
            $plotter->SetPaperType($paperSize);
            $plotter->SetOrientation($orientation);
            $plotter->ShowCoordinates(true);
            $plotter->ShowNorthArrow(true);
            $plotter->ShowScaleBar(true);
            $plotter->ShowDisclaimer(true);
            $plotter->SetLayered($bLayered);
            $plotter->SetMargins($marginTop, $marginBottom, $marginLeft, $marginRight);

            $mapCenter = $map->GetViewCenter();
            //Apply download headers
            $downloadFlag = $this->app->request->params("download");
            if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
                $name = $this->app->request->params("downloadname");
                if (!$name) {
                    $name = "download";
                }
                $name .= ".pdf";
                $plotter->Plot($mapCenter->GetCoordinate(), $map->GetViewScale(), $name);
            } else {
                $plotter->Plot($mapCenter->GetCoordinate(), $map->GetViewScale());
            }
        }
    }
}

?>