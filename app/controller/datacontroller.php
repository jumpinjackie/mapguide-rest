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

class MgDataController extends MgBaseController {

    public function __construct($app) {
        parent::__construct($app);
    }

    public function GetDataConfiguration($uriParts) {
        $uriPath = implode("/", $uriParts);
        $this->EnsureAuthenticationForSite();
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $this->app->response->setBody(file_get_contents($path));
        }
    }

    private function ValidateConfiguration($config, $extension, $method) {
        if (!array_key_exists("Source", $config))
            throw new Exception("Missing root property 'Source' in configuration"); //TODO: Localize

        $result = new stdClass();

        $cfgSource = $config["Source"];
        if (!array_key_exists("Type", $cfgSource))
            throw new Exception("Missing property 'Type' in configuration section 'Source'"); //TODO: Localize
        if ($cfgSource["Type"] !== "MapGuide") 
            throw new Exception("Unsupported source type: ".$cfgSource["Type"]); //TODO: Localize
        if (!array_key_exists("FeatureSource", $cfgSource))
            throw new Exception("Missing property 'FeatureSource' in configuration section 'Source'"); //TODO: Localize
        if (!array_key_exists("FeatureClass", $cfgSource))
            throw new Exception("Missing property 'FeatureClass' in configuration section 'Source'"); //TODO: Localize
        if (array_key_exists("IdentityProperty", $cfgSource))
            $result->IdentityProperty = $cfgSource["IdentityProperty"];
        else
            $result->IdentityProperty = null;

        $result->resId = new MgResourceIdentifier($cfgSource["FeatureSource"]);
        $result->className = $cfgSource["FeatureClass"];

        if (!array_key_exists("Representations", $config))
            throw new Exception("No representations defined in configuration document"); //TODO: Localize
        $cfgRep = $config["Representations"];
        if (!array_key_exists($extension, $cfgRep))
            throw new Exception("This configuration does not support or handle the given representation: ".$extension); //TODO: Localize
        $cfgExtension = $cfgRep[$extension];
        if (!array_key_exists("Methods", $cfgExtension))
            throw new Exception("Missing 'Methods' property in configuration of representation: ".$extension); //TODO: Localize
        $cfgMethods = $cfgExtension["Methods"];
        if (!array_key_exists($method, $cfgMethods))
            throw new Exception("The configured representation '$extension' is not configured to handle $method requests"); //TODO: Localize

        $result->config = $cfgMethods[$method];
        $result->adapterName = $cfgExtension["Adapter"];
        return $result;
    }

    public function HandleGet($uriParts, $extension) {
        $this->HandleMethod($uriParts, $extension, "GET");
    }

    public function HandleGetSingle($uriParts, $id, $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "GET");
    }

    public function HandlePost($uriParts, $extension) {
        $this->HandleMethod($uriParts, $extension, "POST");
    }

    public function HandlePostSingle($uriParts, $id, $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "POST");
    }

    public function HandlePut($uriParts, $extension) {
        $this->HandleMethod($uriParts, $extension, "PUT");
    }

    public function HandlePutSingle($uriParts, $id, $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "PUT");
    }

    public function HandleDelete($uriParts, $extension) {
        $this->HandleMethod($uriParts, $extension, "DELETE");
    }

    public function HandleDeleteSingle($uriParts, $id, $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "DELETE");
    }

    private function HandleMethod($uriParts, $extension, $method) {
        $uriPath = implode("/", $uriParts);
        $this->EnsureAuthenticationForSite("", true);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->container->has($result->adapterName)) {
                throw new Exception("Adapter (".$result->adapterName.") not defined or registered"); //TODO: Localize
            }
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $this->app->MgSiteConnection = $siteConn;
            $this->app->FeatureSource = $result->resId;
            $this->app->AdapterConfig = $result->config;
            $this->app->FeatureClass = $result->className;
            $this->app->ConfigPath = dirname($path);
            $this->app->IdentityProperty = $result->IdentityProperty;
            $adapter = $this->app->container[$result->adapterName];
            $adapter->HandleMethod($method, false);
        }
    }

    private function HandleMethodSingle($uriParts, $id, $extension, $method) {
        $uriPath = implode("/", $uriParts);
        $this->EnsureAuthenticationForSite("", true);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->container->has($result->adapterName)) {
                throw new Exception("Adapter (".$result->adapterName.") not defined or registered"); //TODO: Localize
            }
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $this->app->MgSiteConnection = $siteConn;
            $this->app->FeatureSource = $result->resId;
            $this->app->AdapterConfig = $result->config;
            $this->app->FeatureClass = $result->className;
            $this->app->ConfigPath = dirname($path);
            $this->app->IdentityProperty = $result->IdentityProperty;
            $adapter = $this->app->container[$result->adapterName];
            $adapter->SetFeatureId($id);
            $adapter->HandleMethod($method, true);
        }
    }
}

?>