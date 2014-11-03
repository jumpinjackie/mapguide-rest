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

class MgKmlDocument
{
    private $writer;

    public function __construct($writer) {
        $this->writer = $writer;
    }

    public function StartDocument() {
        $this->writer->SetHeader("Content-Type", MgMimeType::Kml);
        $this->writer->StartChunking();

        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $content .= "<kml xmlns=\"http://earth.google.com/kml/2.1\">";
        $content .= "<NetworkLinkControl><minRefreshPeriod>2</minRefreshPeriod></NetworkLinkControl>";
        $content .= "<Document>";

        $this->writer->WriteChunk($content);
    }

    public function WriteString($str, $lineBreak = true) {
        if ($lineBreak) {
            $this->writer->WriteChunk($str."\n");
        } else {
            $this->writer->WriteChunk($str);
        }
    }

    public function EndDocument() {
        $this->WriteString("</Document></kml>");

        $this->writer->EndChunking();
    }
}

class MgKmlServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    private function WriteRegion($extent, $doc, $dpi, $dimension, $minScale, $maxScale) {
        if ($extent != null) {
            $ll = $extent->GetLowerLeftCoordinate();
            $ur = $extent->GetUpperRightCoordinate();
            $north = $ur->GetY();
            $south = $ll->GetY();
            $east  = $ur->GetX();
            $west  = $ll->GetX();
            $content = "<Region>";
            $content .= "<LatLonAltBox>";
            $content .= sprintf("<north>%f</north><south>%f</south><east>%f</east><west>%f</west>", $north, $south, $east, $west);
            $content .= "</LatLonAltBox>";
            if ($dimension > 0)
            {
                $pixelSize = 0.0254 / $dpi; //METERS_PER_INCH
                $minPix = intval($dimension / $maxScale / $pixelSize);
                $maxPix = $minScale > 0 ? intval($dimension / $minScale / $pixelSize) : -1;
                $content .= "<Lod>";
                $content .= sprintf("<minLodPixels>%d</minLodPixels><maxLodPixels>%d</maxLodPixels>", $minPix, $maxPix);
                $content .= "</Lod>";
            }
            $content .= "</Region>";
        }
    }

    private function AppendScaleRange($resId, $extent, $dimension, $minScale, $maxScale, $dpi, $drawOrder, $format, $sessionId, $doc) {
        $baseUrl = $this->app->config("SelfUrl");
        $content = "<NetworkLink>";
        $content .= "<name><![CDATA[";
        $content .= sprintf("%f - %f", $minScale, $maxScale);
        $content .= "]]></name>";
        $this->WriteRegion($extent, $doc, $dpi, $dimension, $minScale, $maxScale);
        $content .= "<open>1</open>";
        $content .= "<Link>";
        $content .= "<href>";

        //TODO: If we can reverse-route we should.
        $url = MgUtils::TranscodeResourceUrl($baseUrl, $resId) . "/".strtolower($format)."features?draworder=" . $drawOrder . "&amp;session=" . $sessionId;
        $content .= $url;

        /*
        $content .= agentUri, false);
        $content .= "?OPERATION=GetFeaturesKml&amp;VERSION=1.0.0&amp;LAYERDEFINITION=", false);
        Ptr<MgResourceIdentifier> resource = layer->GetLayerDefinition();
        $content .= MgUtil::WideCharToMultiByte(resource->ToString()), false);
        sprintf(buffer,"&amp;DPI=%f", dpi);
        $content .= buffer, false);
        sprintf(buffer,"&amp;DRAWORDER=%d", drawOrder);
        $content .= buffer, false);
        $content .= "&amp;FORMAT=", false);
        $content .= MgUtil::WideCharToMultiByte(format), false);
        $content .= "&amp;SESSION=", false);
        $content .= MgUtil::WideCharToMultiByte(sessionId));
        */
        $content .= "</href>";
        $content .= "<viewRefreshMode>onStop</viewRefreshMode>";
        $content .= "<viewRefreshTime>1</viewRefreshTime>";
        $content .= "<viewFormat>bbox=[bboxWest],[bboxSouth],[bboxEast],[bboxNorth]&amp;width=[horizPixels]&amp;height=[vertPixels]</viewFormat>";
        $content .= "</Link>";
        $content .= "</NetworkLink>";
        $doc->WriteString($content);
    }

    private function AppendLayer($layer, $extent, $drawOrder, $format, $sessionId, $writer) {
        $baseUrl = $this->app->config("SelfUrl");
        $layerKml = "<NetworkLink>";
        $layerKml .= "<visibility>";
        $layerKml .= $layer->GetVisible() ? "1" : "0";
        $layerKml .= "</visibility>";
        $layerKml .= "<name><![CDATA[";
        $layerKml .= $layer->GetLegendLabel();
        $layerKml .= "]]></name>";
        $layerKml .= "<Link>";
        $layerKml .= "<href>";

        //TODO: If we can reverse-route we should.
        $ldfId = $layer->GetLayerDefinition();
        $url = MgUtils::TranscodeResourceUrl($baseUrl, $ldfId) . "/".strtolower($format)."?draworder=" . $drawOrder . "&amp;session=" . $sessionId;
        $layerKml .= $url;

        $layerKml .= "</href>";
        $layerKml .= "<viewRefreshMode>onStop</viewRefreshMode>";
        $layerKml .= "<viewRefreshTime>1</viewRefreshTime>";
        $layerKml .= "<viewFormat>bbox=[bboxWest],[bboxSouth],[bboxEast],[bboxNorth]&amp;width=[horizPixels]&amp;height=[vertPixels]</viewFormat>";
        $layerKml .= "</Link>";
        $layerKml .= "</NetworkLink>";

        $writer->WriteChunk($layerKml);
    }

    private function _GetKmlForMap($map, $sessionId, $format = "kml") {
        $writer = new MgHttpChunkWriter();
        $doc = new MgKmlDocument($writer);
        $csFactory = new MgCoordinateSystemFactory();

        $doc->StartDocument();

        $doc->WriteString("<visibility>1</visibility>");
        $layers = $map->GetLayers();
        $extent = $map->GetMapExtent();
        if ($extent != NULL) {
            $wkt = $map->GetMapSRS();
            if ($wkt != NULL && strlen($wkt) > 0) {
                $mapCs = $csFactory->Create($wkt);
                $llCs = $csFactory->CreateFromCode("LL84");
                $trans = $csFactory->GetTransform($mapCs, $llCs);
                
                $trans->IgnoreDatumShiftWarning(true);
                $trans->IgnoreOutsideDomainWarning(true);

                $extent = $trans->Transform($extent);
            }
        }
        $numLayers = $layers->GetCount();
        for($i = 0; $i < $numLayers; $i++)
        {
            $layer = $layers->GetItem($i);
            $this->AppendLayer($layer, $extent, $numLayers - $i, $format, $sessionId, $writer);
        }
        $doc->EndDocument();
    }

    private static function GetLayerExtent($csFactory, $doc, $csObj, $featSvc, $resSvc) {
        $agfRw = new MgAgfReaderWriter();
        $vlNodes = $doc->getElementsByTagName("VectorLayerDefinition");
        $glNodes = $doc->getElementsByTagName("GridLayerDefinition");
        $dlNodes = $doc->getElementsByTagName("DrawingLayerDefinition");
        if ($vlNodes->length == 1 || $glNodes->length == 1) {

            $node = null;
            if ($vlNodes->length == 1)
                $node = $vlNodes->item(0);
            else if ($glNodes->length == 1)
                $node = $glNodes->item(0);

            $resIdStr = $node->getElementsByTagName("ResourceId")->item(0)->nodeValue;
            $qClsName = $node->getElementsByTagName("FeatureName")->item(0)->nodeValue;
            $geom = $node->getElementsByTagName("Geometry")->item(0)->nodeValue;

            $tokens = explode(":", $qClsName);
            $fsId = new MgResourceIdentifier($resIdStr);

            $clsDef = $featSvc->GetClassDefinition($fsId, $tokens[0], $tokens[1]);
            $clsProps = $clsDef->GetProperties();
            $scReader = $featSvc->GetSpatialContexts($fsId, false);
            $pi = $clsProps->IndexOf($geom);
            $scName = null;
            if ($pi >= 0) {
                $prop = $clsProps->GetItem($pi);
                if (is_callable(array($prop, "GetSpatialContextAssociation"))) {
                    $scName = $prop->GetSpatialContextAssociation();
                }
            }

            if ($scName == null) {
                if ($scReader->ReadNext()) {
                    $sourceCs = $csFactory->Create($scReader->GetCoordinateSystemWkt());
                    $transform = $csFactory->GetTransform($sourceCs, $csObj);

                    $scExtent = $scReader->GetExtent();
                    if ($scExtent != null) {
                        $geom = $agfRw->Read($scExtent);
                        if ($geom != null) {
                            return $geom->Envelope();
                        }
                    }
                }
            } else {
                while($scReader->ReadNext()) {
                    if ($scReader->GetName() == $scName) {
                        $sourceCs = $csFactory->Create($scReader->GetCoordinateSystemWkt());
                        $transform = $csFactory->GetTransform($sourceCs, $csObj);

                        $scExtent = $scReader->GetExtent();
                        if ($scExtent != null) {
                            $geom = $agfRw->Read($scExtent);
                            if ($geom != null) {
                                return $geom->Envelope();
                            }
                        }
                    }
                }
            }
        }
        else if ($dlNodes->length == 1) {
            $dsId = new MgResourceIdentifier($dlNodes->item(0)->getElementsByTagName("ResourceId")->item(0)->nodeValue);
            $sheetName = $dlNodes->item(0)->getElementsByTagName("Sheet")->nodeValue;
            $dsContent = $resSvc->GetResourceContent($dsId);
            $dsDoc = new DOMDocument();
            $dsDoc->loadXML($dsContent->ToString());

            $csNodes = $dsDoc->getElementsByTagName("CoordinateSpace");
            $csTrans = null;
            if ($csNodes->length == 1) {
                $srcCs = $csFactory->Create($csNodes->item(0)->nodeValue);
                $csTrans = $csFactory->GetTransform($srcCs, $csObj);
                $csTrans->IgnoreDatumShiftWarning(true);
                $csTrans->IgnoreOutsideDomainWarning(true);
            }

            $shtNodes = $dlNodes->item(0)->getElementsByTagName("Sheet");
            if ($shtNodes->length > 0) {
                for ($i = 0; $i < $shtNodes->length; $i++) {
                    $shtNode = $shtNodes->item($i);
                    $shtName = $shtNode->getElementsByTagName("Name")->item(0)->nodeValue;
                    if ($sheetName == $shtName) {
                        $extNodes = $shtNode->getElementsByTagName("Extent");
                        if ($extNodes->length == 1) {
                            $minX = $extNodes->item(0)->getElementsByTagName("MinX")->item(0)->nodeValue;
                            $minY = $extNodes->item(0)->getElementsByTagName("MinY")->item(0)->nodeValue;
                            $maxX = $extNodes->item(0)->getElementsByTagName("MaxX")->item(0)->nodeValue;
                            $maxY = $extNodes->item(0)->getElementsByTagName("MaxY")->item(0)->nodeValue;

                            $env = new MgEnvelope($minX, $minY, $maxX, $maxY);
                            if ($csTrans != null)
                                return $env->Transform($csTrans);
                            else
                                return $env;
                        }
                    }
                }
            }
        }
        return null;
    }

    public function GetMapKml($resId, $format = "kml") {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("kml", "kmz"));
        $native = ($this->GetBooleanRequestParameter("native", "1") == "1");
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        } else {
            $sessionId = $this->GetRequestParameter("session", "");
        }
        $this->EnsureAuthenticationForSite($sessionId, true);
        $siteConn = new MgSiteConnection();
        if ($sessionId !== "") {
            $userInfo = new MgUserInformation($sessionId);
            $siteConn->Open($userInfo);
        } else {
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            $sessionId = $site->CreateSession();

            $userInfo = new MgUserInformation($sessionId);
            $siteConn->Open($userInfo);
        }
        if ($native) {
            $mdfIdStr = $resId->ToString();
            $selfUrl = $this->app->config("SelfUrl");
            $this->app->redirect("$selfUrl/../mapagent/mapagent.fcgi?OPERATION=GETMAPKML&VERSION=1.0.0&SESSION=$sessionId&MAPDEFINITION=$mdfIdStr&CLIENTAGENT=MapGuide REST Extension");
        } else {
            $map = new MgMap($siteConn);
            $map->Create($resId, $resId->GetName());

            $this->_GetKmlForMap($map, $sessionId, $format);
        }
    }    

    public function GetLayerKml($resId, $format = "kml") {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("kml", "kmz"));

        $width = $this->GetRequestParameter("width", null);
        $height = $this->GetRequestParameter("height", null);
        $drawOrder = $this->GetRequestParameter("draworder", null);
        $dpi = $this->GetRequestParameter("dpi", 96);
        $bbox = $this->GetRequestParameter("bbox", null);
        $extents = null;

        if ($width == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "width"));
        if ($height == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "height"));
        if ($drawOrder == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "draworder"));
        if ($bbox == null) {
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "bbox"));
        } else {
            $parts = explode(",", $bbox);
            if (count($parts) == 4) {
                $extents = new MgEnvelope($parts[0], $parts[1], $parts[2], $parts[3]);
            }
        }

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        } else {
            $sessionId = $this->GetRequestParameter("session", "");
        }
        $this->EnsureAuthenticationForSite($sessionId, true);
        $siteConn = new MgSiteConnection();
        if ($sessionId !== "") {
            $userInfo = new MgUserInformation($sessionId);
            $siteConn->Open($userInfo);
        } else {
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            $sessionId = $site->CreateSession();

            $userInfo = new MgUserInformation($sessionId);
            $siteConn->Open($userInfo);
        }

        $csFactory = new MgCoordinateSystemFactory();
        $csObj = $csFactory->CreateFromCode("LL84");
        $scale = MgUtils::GetScale($extents, $csObj, $width, $height, $dpi);

        $writer = new MgSlimChunkWriter($this->app);
        $doc = new MgKmlDocument($writer);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
        $ldfContent = $resSvc->GetResourceContent($resId);
        $xml = new DOMDocument();
        $xml->loadXML($ldfContent->ToString());

        $destExtent = self::GetLayerExtent($csFactory, $xml, $csObj, $featSvc, $resSvc);

        $doc->StartDocument();

        $doc->WriteString("<visibility>1</visibility>");

        if ($destExtent != null) {
            $widthMeters = $csObj->ConvertCoordinateSystemUnitsToMeters($destExtent->GetWidth());
            $heightMeters = $csObj->ConvertCoordinateSystemUnitsToMeters($destExtent->GetHeight());
            $dimension = sqrt($widthMeters * $heightMeters);

            $vlNodes = $xml->getElementsByTagName("VectorLayerDefinition");
            $glNodes = $xml->getElementsByTagName("GridLayerDefinition");
            $dlNodes = $xml->getElementsByTagName("DrawingLayerDefinition");
            if ($vlNodes->length == 1) {
                $scaleRangeNodes = $vlNodes->item(0)->getElementsByTagName("VectorScaleRange");
                for ($i = 0; $i < $scaleRangeNodes->length; $i++) {
                    $scaleRange = $scaleRangeNodes->item($i);

                    $minElt = $scaleRange->getElementsByTagName('MinScale');
                    $maxElt = $scaleRange->getElementsByTagName('MaxScale');
                    $minScale = "0";
                    $maxScale = 'infinity';  // as MDF's VectorScaleRange::MAX_MAP_SCALE
                    if($minElt->length > 0)
                        $minScale = $minElt->item(0)->nodeValue;
                    if($maxElt->length > 0)
                        $maxScale = $maxElt->item(0)->nodeValue;

                    if ($minScale != 'infinity')
                        $minScale = intval($minScale);
                    if ($maxScale != 'infinity')
                        $maxScale = intval($maxScale);
                    else
                        $maxScale = 1000000000000.0; // as MDF's VectorScaleRange::MAX_MAP_SCALE

                    if ($scale > $minScale && $scale <= $maxScale)
                    {
                        $this->AppendScaleRange($resId, $destExtent, $dimension, $minScale, $maxScale, $dpi, $drawOrder, $format, $sessionId, $doc);
                    }
                }
            }
            else if ($glNodes->length == 1) {

            }
        }

        $doc->EndDocument();
    }

    public function GetFeaturesKml($resId, $format = "kml") {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("kml", "kmz"));

        $bbox = $this->GetRequestParameter("bbox", null);
        $dpi = $this->GetRequestParameter("dpi", 96);
        $width = $this->GetRequestParameter("width", null);
        $height = $this->GetRequestParameter("height", null);
        $drawOrder = $this->GetRequestParameter("draworder", null);

        if ($width == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "width"));
        if ($height == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "height"));
        if ($drawOrder == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "draworder"));
        if ($bbox == null)
            $this->app->halt(400, $this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "bbox"));

        //We still need the mapagent URL so that GETFEATURESKML can generate legend icons from
        //within the operation
        $agentUri = $this->app->config("SelfUrl")."/../mapagent/mapagent.fcgi";

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resId, $bbox, $width, $height, $drawOrder, $dpi) {
            $param->AddParameter("OPERATION", "GETFEATURESKML");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("LAYERDEFINITION", $resId->ToString());
            $param->AddParameter("BBOX", $bbox);
            $param->AddParameter("WIDTH", $width);
            $param->AddParameter("HEIGHT", $height);
            $param->AddParameter("DRAWORDER", $drawOrder);
            $param->AddParameter("DPI", $dpi);
            $param->AddParameter("FORMAT", strtoupper($fmt));
            $param->AddParameter("X-CHUNK-RESPONSE", "true");
            $that->ExecuteHttpRequest($req);
        }, false, $agentUri);
    }
}

?>