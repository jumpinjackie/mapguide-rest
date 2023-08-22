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

require_once "restadapter.php";

class MgMapImageRestAdapterDocumentor extends MgFeatureRestAdapterDocumentor {
    protected function GetAdditionalParameters($app, $bSingle, $method) {
        $params = parent::GetAdditionalParameters($app, $bSingle, $method);
        if ($method == "GET") {
            $pWidth = new stdClass();
            $pWidth->in = "query";
            $pWidth->name = "width";
            $pWidth->type = "integer";
            $pWidth->required = false;
            $pWidth->description = "The width of the image";

            $pHeight = new stdClass();
            $pHeight->in = "query";
            $pHeight->name = "height";
            $pHeight->type = "integer";
            $pHeight->required = false;
            $pHeight->description = "The height of the image";

            $pDpi = new stdClass();
            $pDpi->in = "query";
            $pDpi->name = "dpi";
            $pDpi->type = "integer";
            $pDpi->required = false;
            $pDpi->description = "The dpi of the image";

            $pScale = new stdClass();
            $pScale->in = "query";
            $pScale->name = "scale";
            $pScale->type = "integer";
            $pScale->required = false;
            $pScale->description = "The scale of the image";

            $pSelection = new stdClass();
            $pSelection->in = "query";
            $pSelection->name = "selection";
            $pSelection->type = "boolean";
            $pSelection->required = false;
            $pSelection->description = "Show selection (default: true)";

            array_push($params, $pWidth);
            array_push($params, $pHeight);
            array_push($params, $pDpi);
            array_push($params, $pScale);
            array_push($params, $pSelection);
        }
        return $params;
    }
}

class MgMapImageRestAdapter extends MgRestAdapter {
    private $mapDefId;
    private $map;
    private $sel;
    private $sessionId;

    private $imgWidth;
    private $imgHeight;
    private $imgFormat;
    private $dpi;
    private $zoomFactor;
    private $viewScale;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        $this->mapDefId = null;
        $this->map = null;
        $this->sel = null;
        $this->sessionId = "";
        $this->imgFormat = "PNG";
        $this->imgWidth = 300;
        $this->imgHeight = 200;
        $this->dpi = 96;
        $this->zoomFactor = 1.3;
        $this->viewScale = 0;
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
    }

    public function GetMimeType() {
        $fmt = strtoupper($this->imgFormat);
        switch ($fmt) {
            case "PNG":
            case "PNG8":
                return MgMimeType::Png;
            case "JPG":
                return MgMimeType::Jpeg;
            case "GIF":
                return MgMimeType::Gif;
            default:
                return MgMimeType::Binary;
        }
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        //Where -1 is normally used to indicate un-bounded limits, 0 is used here.
        if ($this->limit === -1)
            $this->limit = 0;
        if (!array_key_exists("MapDefinition", $config))
            throw new Exception($this->GetLocalizedText("E_MISSING_REQUIRED_ADAPTER_PROPERTY", "MapDefinition"));
        if (!array_key_exists("SelectionLayer", $config))
            throw new Exception($this->GetLocalizedText("E_MISSING_REQUIRED_ADAPTER_PROPERTY", "SelectionLayer"));

        $this->mapDefId = new MgResourceIdentifier($config["MapDefinition"]);
        $this->selLayerName = $config["SelectionLayer"];
        if (array_key_exists("ZoomFactor", $config))
            $this->zoomFactor = floatval($config["ZoomFactor"]);
        if (array_key_exists("ImageFormat", $config))
            $this->imgFormat = $config["ImageFormat"];

        if (array_key_exists("ViewScale", $config))
            $this->viewScale = intval($config["ViewScale"]);
    }

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {
        try {
            //Apply any overrides from query string
            $ovWidth = $this->GetRequestParameter("width");
            $ovHeight = $this->GetRequestParameter("height");
            $ovDpi = $this->GetRequestParameter("dpi");
            $ovScale = $this->GetRequestParameter("scale");
            if ($ovWidth != null)
                $this->imgWidth = $ovWidth;
            if ($ovHeight != null)
                $this->imgHeight = $ovHeight;
            if ($ovDpi != null)
                $this->dpi = $ovDpi;
            if ($ovScale != null)
                $this->viewScale = intval($ovScale);
            $bSelection = true;
            if ($this->GetRequestParameter("selection") === "0" || $this->GetRequestParameter("selection") === "false")
                $bSelection = false;

            $this->featSvc = $this->siteConn->CreateService(MgServiceType::FeatureService);
            $renderSvc = $this->siteConn->CreateService(MgServiceType::RenderingService);

            $mapName = "MapImageAdapter";
            $this->map = new MgMap($this->siteConn);
            $this->map->Create($this->mapDefId, $mapName);
            $this->sel = new MgSelection($this->map);

            $layers = $this->map->GetLayers();
            $idx = $layers->IndexOf($this->selLayerName);
            if ($idx < 0)
                throw new Exception($this->GetLocalizedText("E_LAYER_NOT_FOUND_IN_MAP", $this->selLayerName));
            $layer = $layers->GetItem($idx);
            if ($layer->GetFeatureSourceId() !== $this->featureSourceId->ToString())
                throw new Exception($this->GetLocalizedText("E_LAYER_NOT_POINTING_TO_EXPECTED_FEATURE_SOURCE", $this->selLayerName, $this->featureSourceId->ToString(), $layer->GetFeatureSourceId()));

            $this->selLayer = $layer;

            $query = $this->CreateQueryOptions($single);
            $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);

            $start = -1;
            $end = -1;
            $read = 0;
            $limit = $this->limit;

            $pageNo = $this->GetRequestParameter("page");
            if ($pageNo == null)
                $pageNo = 1;
            else
                $pageNo = intval($pageNo);

            $bEndOfReader = false;
            if ($this->pageSize > 0) {
                if ($pageNo > 1) {
                    $skipThisMany = (($pageNo - 1) * $this->pageSize) - 1;
                    //echo "skip this many: $skipThisMany<br/>";
                    $bEndOfReader = true;
                    while ($reader->ReadNext()) {
                        if ($read == $skipThisMany) {
                            $bEndOfReader = false;
                            $limit = min(($skipThisMany + $this->pageSize), $this->limit - 1) - $read;
                            break;
                        }
                        $read++;
                    }
                } else { //first page, set limit to page size
                    $limit = $this->pageSize;
                }
            }

            //echo "read: $read, limit: $limit, pageSize: ".$this->pageSize." result limit: ".$this->limit;
            //die;
            $this->sel->AddFeatures($this->selLayer, $reader, $limit);
            $reader->Close();

            $extents = $this->sel->GetExtents($this->featSvc);
            $extLL = $extents->GetLowerLeftCoordinate();
            $extUR = $extents->GetUpperRightCoordinate();
            $x = ($extLL->GetX() + $extUR->GetX()) / 2.0;
            $y = ($extLL->GetY() + $extUR->GetY()) / 2.0;

            if ($this->viewScale === 0) {
                $csFactory = new MgCoordinateSystemFactory();
                $cs = $csFactory->Create($this->map->GetMapSRS());
                $metersPerUnit = $cs->ConvertCoordinateSystemUnitsToMeters(1.0);

                $mcsH = $extUR->GetY() - $extLL->GetY();
                $mcsW = $extUR->GetX() - $extLL->GetX();

                $mcsH = $mcsH * $this->zoomFactor;
                $mcsW = $mcsW * $this->zoomFactor;

                $metersPerPixel  = 0.0254 / $this->dpi;

                if ($this->imgHeight * $mcsW > $this->imgWidth * $mcsH)
                    $this->viewScale = $mcsW * $metersPerUnit / ($this->imgWidth * $metersPerPixel); // width-limited
                else
                    $this->viewScale = $mcsH * $metersPerUnit / ($this->imgHeight * $metersPerPixel); // height-limited
            }

            $colorString = $this->map->GetBackgroundColor();
            // The returned color string is in AARRGGBB format. But the constructor of MgColor needs a string in RRGGBBAA format
            $colorString = substr($colorString, 2, 6) . substr($colorString, 0, 2);
            $bgColor = new MgColor($colorString);
            $bKeepSelection = true;
            $geomFactory = new MgGeometryFactory();
            $center = $geomFactory->CreateCoordinateXY($x, $y);
            $image = $renderSvc->RenderMap($this->map,
                                           ($bSelection ? $this->sel : NULL),
                                           $center,
                                           $this->viewScale,
                                           $this->imgWidth,
                                           $this->imgHeight,
                                           $bgColor,
                                           $this->imgFormat,
                                           $bKeepSelection);

            //Set download response headers if specified
            if ($this->GetRequestParameter("download") === "1" || $this->GetRequestParameter("download") === "true") {
                $filebasename = "download";
                if ($this->GetRequestParameter("downloadname")) {
                    $filebasename = $this->GetRequestParameter("downloadname");
                }
                $this->SetResponseHeader("Content-Disposition", "attachment; filename=".MgUtils::GetFileNameFromMimeType($filebasename, $image->GetMimeType()));
            }

            $this->OutputByteReader($image);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    /**
     * Returns the documentor for this adapter
     */
    public static function GetDocumentor() {
        return new MgMapImageRestAdapterDocumentor();
    }
}