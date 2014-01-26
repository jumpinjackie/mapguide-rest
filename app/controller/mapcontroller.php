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

class MgMapController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
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
}

?>