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

class MgMapImageRestAdapter extends MgRestAdapter {
    private $mapDefId;
    private $map;
    private $sel;
    private $sessionId;

    private $limit;
    private $imgWidth;
    private $imgHeight;
    private $imgFormat;
    private $dpi;
    private $zoomFactor;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath) {
        $this->limit = 0;
        $this->mapDefId = null;
        $this->map = null;
        $this->sel = null;
        $this->sessionId = "";
        $this->imgFormat = "PNG";
        $this->imgWidth = 300;
        $this->imgHeight = 200;
        $this->dpi = 96;
        $this->zoomFactor = 1.3;
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath);
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (array_key_exists("MaxCount", $config))
            $this->limit = intval($config["MaxCount"]);
        if (!array_key_exists("MapDefinition", $config))
            throw new Exception("Missing required adapter property 'MapDefinition'"); //TODO: Localize
        if (!array_key_exists("SelectionLayer", $config))
            throw new Exception("Missing required adapter property 'SelectionLayer'"); //TODO: Localize
        
        $this->mapDefId = new MgResourceIdentifier($config["MapDefinition"]);
        $this->selLayerName = $config["SelectionLayer"];
        if (array_key_exists("ZoomFactor", $config))
            $this->zoomFactor = floatval($config["ZoomFactor"]);
        if (array_key_exists("ImageFormat", $config))
            $this->imgFormat = $config["ImageFormat"];
    }

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {
        try {
            //Apply any overrides from query string
            $ovWidth = $this->app->request->get("width");
            $ovHeight = $this->app->request->get("height");
            $ovDpi = $this->app->request->get("dpi");
            if ($ovWidth != null)
                $this->imgWidth = $ovWidth;
            if ($ovHeight != null)
                $this->imgHeight = $ovHeight;
            if ($ovDpi != null)
                $this->dpi = $ovDpi;
        
            $site = $this->siteConn->GetSite();
            $this->sessionId = $site->GetCurrentSession();
            if ($this->sessionId === "") {
                $this->sessionId = $site->CreateSession();
            }
            $userInfo = new MgUserInformation($this->sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($userInfo);
            $this->resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $this->featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

            $mapName = "MapImageAdapter";
            $this->map = new MgMap($siteConn);
            $this->map->Create($this->mapDefId, $mapName);
            $this->sel = new MgSelection($this->map);
            $mapId = new MgResourceIdentifier("Session:".$this->sessionId."//$mapName.Map");
            $this->map->Save($this->resSvc, $mapId);

            $layers = $this->map->GetLayers();
            $idx = $layers->IndexOf($this->selLayerName);
            if ($idx < 0)
                throw new Exception("No layer named ".$this->selLayerName." found in map"); //TODO: Localize
            $layer = $layers->GetItem($idx);
            if ($layer->GetFeatureSourceId() !== $this->featureSourceId->ToString())
                throw new Exception("Layer ".$this->selLayerName." does not point to the expected feature source of (".$this->featureSourceId->ToString()."). It instead points to: ".$layer->GetFeatureSourceId()); //TODO: Localize

            $this->selLayer = $layer;

            $query = $this->CreateQueryOptions($single);
            $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
           
            $this->sel->AddFeatures($this->selLayer, $reader, $this->limit);
            $reader->Close();
            $this->sel->Save($this->resSvc, $mapName);
            
            $extents = $this->sel->GetExtents($this->featSvc);
            $extLL = $extents->GetLowerLeftCoordinate();
            $extUR = $extents->GetUpperRightCoordinate();
            $x = ($extLL->GetX() + $extUR->GetX()) / 2.0;
            $y = ($extLL->GetY() + $extUR->GetY()) / 2.0;


            $csFactory = new MgCoordinateSystemFactory();
            $cs = $csFactory->Create($this->map->GetMapSRS());
            $metersPerUnit = $cs->ConvertCoordinateSystemUnitsToMeters(1.0);

            $mcsH = $extUR->GetY() - $extLL->GetY();
            $mcsW = $extUR->GetX() - $extLL->GetX();
            
            $mcsH = $mcsH * $this->zoomFactor;
            $mcsW = $mcsW * $this->zoomFactor;
                 
            $metersPerPixel  = 0.0254 / $this->dpi;

            $scale = 0.0;
            if ($this->imgHeight * $mcsW > $this->imgWidth * $mcsH)
                $scale = $mcsW * $metersPerUnit / ($this->imgWidth * $metersPerPixel); // width-limited
            else
                $scale = $mcsH * $metersPerUnit / ($this->imgHeight * $metersPerPixel); // height-limited

            $req = new MgHttpRequest("");
            $param = $req->GetRequestParam();

            $param->AddParameter("OPERATION", "GETMAPIMAGE");
            $param->AddParameter("VERSION", "2.0.0");
            $param->AddParameter("SESSION", $this->sessionId);
            $param->AddParameter("LOCALE", $this->app->config("Locale"));
            $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
            $param->AddParameter("CLIENTIP", $this->GetClientIp());

            $param->AddParameter("FORMAT", $this->imgFormat);
            $param->AddParameter("MAPNAME", $mapName);
            $param->AddParameter("KEEPSELECTION", "1");
            $param->AddParameter("SETDISPLAYWIDTH", $this->imgWidth);
            $param->AddParameter("SETDISPLAYHEIGHT", $this->imgHeight);
            $param->AddParameter("SETDISPLAYDPI", $this->dpi);
            $param->AddParameter("SETVIEWCENTERX", $x);
            $param->AddParameter("SETVIEWCENTERY", $y);
            $param->AddParameter("SETVIEWSCALE", $scale);
            $param->AddParameter("BEHAVIOR", 3); //Layers + Selection

            $this->ExecuteHttpRequest($req);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }
}

?>