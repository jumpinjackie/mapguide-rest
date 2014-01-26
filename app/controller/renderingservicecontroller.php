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
        $param->AddParameter("LOCALE", $this->app->config("Locale"));
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
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $format) {
            $param->AddParameter("OPERATION", "GETMAPIMAGE");
            $param->AddParameter("VERSION", "2.0.0");
            $param->AddParameter("MAPDEFINITION", $resIdStr);
            $param->AddParameter("FORMAT", strtoupper($format));

            $keepSelection = $that->GetRequestParameter("keepselection", null);
            if ($keepSelection != null)
                $param->AddParameter("KEEPSELECTION", $keepSelection);

            $clip = $that->GetRequestParameter("clip", null);
            if ($clip != null)
                $param->AddParameter("CLIP", $clip);

            $x = $that->GetRequestParameter("x", null);
            if ($x != null)
                $param->AddParameter("SETVIEWCENTERX", $x);

            $y = $that->GetRequestParameter("y", null);
            if ($y != null)
                $param->AddParameter("SETVIEWCENTERY", $y);

            $scale = $that->GetRequestParameter("scale", null);
            if ($scale != null)
                $param->AddParameter("SETVIEWSCALE", $scale);

            $dpi = $that->GetRequestParameter("dpi", null);
            if ($dpi != null)
                $param->AddParameter("SETDISPLAYDPI", $dpi);

            $width = $that->GetRequestParameter("width", null);
            if ($width != null)
                $param->AddParameter("SETDISPLAYWIDTH", $width);

            $height = $that->GetRequestParameter("height", null);
            if ($height != null)
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
        $param->AddParameter("LOCALE", $this->app->config("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $param->AddParameter("OPERATION", "GETMAPIMAGE");
        $param->AddParameter("VERSION", "2.0.0");
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
}

?>