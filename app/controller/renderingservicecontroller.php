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

class MgRenderingServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function RenderDynamicOverlayImage($sessionId, $mapName, $format) {
        $req = new MgHttpRequest("");
        $param = $req->GetRequestParam();
        $param->AddParameter("SESSION", $sessionId);
        $param->AddParameter("LOCALE", $this->GetConfig("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $param->AddParameter("OPERATION", "GETDYNAMICMAPOVERLAYIMAGE");
        $param->AddParameter("VERSION", "2.1.0");
        $param->AddParameter("MAPNAME", $mapName);
        $param->AddParameter("FORMAT", strtoupper($format));

        $selColor = $this->GetRequestParameter("selectioncolor", null);
        if ($selColor != null)
            $param->AddParameter("SELECTIONCOLOR", $selColor);

        $behavior = $this->GetRequestParameter("behavior", null);
        if ($behavior != null)
            $param->AddParameter("BEHAVIOR", $behavior);

        $x = $this->GetRequestParameter("x", null);
        if ($x != null)
            $param->AddParameter("SETVIEWCENTERX", $x);

        $y = $this->GetRequestParameter("y", null);
        if ($y != null)
            $param->AddParameter("SETVIEWCENTERY", $y);

        $scale = $this->GetRequestParameter("scale", null);
        if ($scale != null)
            $param->AddParameter("SETVIEWSCALE", $scale);

        $dpi = $this->GetRequestParameter("dpi", null);
        if ($dpi != null)
            $param->AddParameter("SETDISPLAYDPI", $dpi);

        $width = $this->GetRequestParameter("width", null);
        if ($width != null)
            $param->AddParameter("SETDISPLAYWIDTH", $width);

        $height = $this->GetRequestParameter("height", null);
        if ($height != null)
            $param->AddParameter("SETDISPLAYHEIGHT", $height);

        $showlayers = $this->GetRequestParameter("showlayers", null);
        if ($showlayers != null)
            $param->AddParameter("SHOWLAYERS", $showlayers);

        $hidelayers = $this->GetRequestParameter("hidelayers", null);
        if ($hidelayers != null)
            $param->AddParameter("HIDELAYERS", $hidelayers);

        $showgroups = $this->GetRequestParameter("showgroups", null);
        if ($showgroups != null)
            $param->AddParameter("SHOWGROUPS", $showgroups);

        $hidegroups = $this->GetRequestParameter("hidegroups", null);
        if ($hidegroups != null)
            $param->AddParameter("HIDEGROUPS", $hidegroups);

        $this->ExecuteHttpRequest($req);
    }

    public function RenderMapDefinition($mdfId, $format) {
        $resIdStr = $mdfId->ToString();
        $x = $this->GetRequestParameter("x", null);
        $y = $this->GetRequestParameter("y", null);
        $scale = $this->GetRequestParameter("scale", null);
        $width = $this->GetRequestParameter("width", null);
        $height = $this->GetRequestParameter("height", null);

        if ($x == null)
            $this->BadRequest($this->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "x"), $this->GetMimeTypeForFormat($format));
        if ($y == null)
            $this->BadRequest($this->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "y"), $this->GetMimeTypeForFormat($format));
        if ($scale == null)
            $this->BadRequest($this->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "scale"), $this->GetMimeTypeForFormat($format));
        if ($width == null)
            $this->BadRequest($this->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "width"), $this->GetMimeTypeForFormat($format));
        if ($height == null)
            $this->BadRequest($this->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "height"), $this->GetMimeTypeForFormat($format));

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $format, $x, $y, $scale, $width, $height) {
            $param->AddParameter("OPERATION", "GETMAPIMAGE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("MAPDEFINITION", $resIdStr);
            $param->AddParameter("FORMAT", strtoupper($format));

            $keepSelection = $that->GetBooleanRequestParameter("keepselection", null);
            if ($keepSelection != null)
                $param->AddParameter("KEEPSELECTION", $keepSelection);

            $clip = $that->GetRequestParameter("clip", null);
            if ($clip != null)
                $param->AddParameter("CLIP", $clip);

            $param->AddParameter("SETVIEWCENTERX", $x);
            $param->AddParameter("SETVIEWCENTERY", $y);
            $param->AddParameter("SETVIEWSCALE", $scale);

            $dpi = $that->GetRequestParameter("dpi", null);
            if ($dpi != null)
                $param->AddParameter("SETDISPLAYDPI", $dpi);
            
            $param->AddParameter("SETDISPLAYWIDTH", $width);
            $param->AddParameter("SETDISPLAYHEIGHT", $height);

            $showlayers = $that->GetRequestParameter("showlayers", null);
            if ($showlayers != null)
                $param->AddParameter("SHOWLAYERS", $showlayers);

            $hidelayers = $that->GetRequestParameter("hidelayers", null);
            if ($hidelayers != null)
                $param->AddParameter("HIDELAYERS", $hidelayers);

            $showgroups = $that->GetRequestParameter("showgroups", null);
            if ($showgroups != null)
                $param->AddParameter("SHOWGROUPS", $showgroups);

            $hidegroups = $that->GetRequestParameter("hidegroups", null);
            if ($hidegroups != null)
                $param->AddParameter("HIDEGROUPS", $hidegroups);

            $that->ExecuteHttpRequest($req);
        });
    }

    public function RenderRuntimeMap($sessionId, $mapName, $format) {
        $req = new MgHttpRequest("");
        $param = $req->GetRequestParam();
        $param->AddParameter("SESSION", $sessionId);
        $param->AddParameter("LOCALE", $this->GetConfig("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $param->AddParameter("OPERATION", "GETMAPIMAGE");
        $param->AddParameter("VERSION", "1.0.0");
        $param->AddParameter("MAPNAME", $mapName);
        $param->AddParameter("FORMAT", strtoupper($format));

        $keepSelection = $this->GetRequestParameter("keepselection", null);
        if ($keepSelection != null)
            $param->AddParameter("KEEPSELECTION", $keepSelection);

        $clip = $this->GetRequestParameter("clip", null);
        if ($clip != null)
            $param->AddParameter("CLIP", $clip);

        $x = $this->GetRequestParameter("x", null);
        if ($x != null)
            $param->AddParameter("SETVIEWCENTERX", $x);

        $y = $this->GetRequestParameter("y", null);
        if ($y != null)
            $param->AddParameter("SETVIEWCENTERY", $y);

        $scale = $this->GetRequestParameter("scale", null);
        if ($scale != null)
            $param->AddParameter("SETVIEWSCALE", $scale);

        $dpi = $this->GetRequestParameter("dpi", null);
        if ($dpi != null)
            $param->AddParameter("SETDISPLAYDPI", $dpi);

        $width = $this->GetRequestParameter("width", null);
        if ($width != null)
            $param->AddParameter("SETDISPLAYWIDTH", $width);

        $height = $this->GetRequestParameter("height", null);
        if ($height != null)
            $param->AddParameter("SETDISPLAYHEIGHT", $height);

        $showlayers = $this->GetRequestParameter("showlayers", null);
        if ($showlayers != null)
            $param->AddParameter("SHOWLAYERS", $showlayers);

        $hidelayers = $this->GetRequestParameter("hidelayers", null);
        if ($hidelayers != null)
            $param->AddParameter("HIDELAYERS", $hidelayers);

        $showgroups = $this->GetRequestParameter("showgroups", null);
        if ($showgroups != null)
            $param->AddParameter("SHOWGROUPS", $showgroups);

        $hidegroups = $this->GetRequestParameter("hidegroups", null);
        if ($hidegroups != null)
            $param->AddParameter("HIDEGROUPS", $hidegroups);

        $this->ExecuteHttpRequest($req);
    }

    public function RenderRuntimeMapLegend($sessionId, $mapName, $format) {
        $req = new MgHttpRequest("");
        $param = $req->GetRequestParam();
        $param->AddParameter("SESSION", $sessionId);
        $param->AddParameter("LOCALE", $this->GetConfig("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $param->AddParameter("OPERATION", "GETMAPLEGENDIMAGE");
        $param->AddParameter("VERSION", "1.0.0");
        $param->AddParameter("MAPNAME", $mapName);
        $param->AddParameter("FORMAT", strtoupper($format));

        $width = $this->GetRequestParameter("width", null);
        if ($width != null)
            $param->AddParameter("WIDTH", $width);

        $height = $this->GetRequestParameter("height", null);
        if ($height != null)
            $param->AddParameter("HEIGHT", $height);

        $this->ExecuteHttpRequest($req);
    }
}