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

require_once dirname(__FILE__)."/../version.php";
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

    public function GetApiDocViewer($uriParts) {
        $uriPath = implode("/", $uriParts);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $docUrl = $this->app->config("SelfUrl")."/data/$uriPath/apidoc";
            $assetUrlRoot = $this->app->config("SelfUrl")."/doc";
            $docTpl = $this->app->config("AppRootDir")."/assets/doc/viewer.tpl";

            $smarty = new Smarty();
            $smarty->setCompileDir($this->app->config("Cache.RootDir")."/templates_c");
            $smarty->assign("title", $uriParts[count($uriParts)-1]." API Reference"); //TODO: Localize
            $smarty->assign("docUrl", $docUrl);
            $smarty->assign("docAssetRoot", $assetUrlRoot);

            $output = $smarty->fetch($docTpl);
            $this->app->response->header("Content-Type", "text/html");
            $this->app->response->setBody($output);
        }
    }

    public function GetApiDoc($uriParts) {
        $uriPath = implode("/", $uriParts);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $config = json_decode(file_get_contents($path), true);
            
            $urlRoot = "/data/$uriPath";

            $apidoc = new stdClass();
            $apidoc->basePath = $this->app->config("SelfUrl");
            $apidoc->swaggerVersion = SWAGGER_API_VERSION;
            $apidoc->apiVersion = MG_REST_API_VERSION;
            $apidoc->resourcePath = $urlRoot;
            $apidoc->apis = array();

            if (array_key_exists("Representations", $config)) {
                $reps = $config["Representations"];
                foreach ($reps as $extension => $adapterConfig) {
                    if (array_key_exists("Methods", $adapterConfig) && array_key_exists("Adapter", $adapterConfig)) {

                        $adapterName = $adapterConfig["Adapter"];
                        //Resolve documentor
                        $documentor = $this->app->container[$adapterName."Doc"];

                        $multiConf = new stdClass();
                        $multiConf->url = "$urlRoot/.$extension";
                        $multiConf->single = false;
                        $multiConf->extraParams = array();

                        $singleConf = new stdClass();
                        $singleConf->url = "$urlRoot/{id}.$extension";
                        $singleConf->single = true;
                        $singleConf->extraParams = array();

                        $pId = new stdClass();
                        $pId->paramType = "path";
                        $pId->name = "id";
                        $pId->type = "string";
                        $pId->required = true;
                        $pId->description = "The ID of the feature to return";

                        array_push($singleConf->extraParams, $pId);

                        $confs = array($multiConf, $singleConf);
                        foreach ($confs as $conf) {
                            $repDoc = new stdClass();
                            $repDoc->path = $conf->url;
                            $ops = array();

                            $methodsConf = $adapterConfig["Methods"];
                            foreach ($methodsConf as $method => $methodOptions) {
                                $op = $documentor->DocumentOperation($method, $extension, $conf->single);
                                foreach ($conf->extraParams as $extraParam) {
                                    array_push($op->parameters, $extraParam);
                                }
                                array_push($ops, $op);
                            }
                            $repDoc->operations = $ops;
                            array_push($apidoc->apis, $repDoc);
                        }
                    }
                }
            }

            $this->app->response->header("Content-Type", "application/json");
            $this->app->response->setBody(json_encode($apidoc));
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
        if (!array_key_exists("LayerDefinition", $cfgSource)) {
            if (!array_key_exists("FeatureSource", $cfgSource))
                throw new Exception("Missing property 'FeatureSource' in configuration section 'Source'"); //TODO: Localize
            if (!array_key_exists("FeatureClass", $cfgSource))
                throw new Exception("Missing property 'FeatureClass' in configuration section 'Source'"); //TODO: Localize
        }
        if (array_key_exists("IdentityProperty", $cfgSource))
            $result->IdentityProperty = $cfgSource["IdentityProperty"];
        else
            $result->IdentityProperty = null;

        if (!array_key_exists("LayerDefinition", $cfgSource)) {
            $result->resId = new MgResourceIdentifier($cfgSource["FeatureSource"]);
            $result->className = $cfgSource["FeatureClass"];
        } else {
            $result->LayerDefinition = $cfgSource["LayerDefinition"];
        }

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

    private function ValidateAcl($siteConn, $config) {
        $site = $siteConn->GetSite();
        if ($this->userName == null && $this->sessionId != null) {
            $this->userName = $site->GetUserForSession();
        }
        $groups = array();
        $doc = new DOMDocument();
        $br = $site->EnumerateGroups($this->userName);
        $doc->loadXML($br->ToString());
        $groupNodes = $doc->getElementsByTagName("Name");
        for ($i = 0; $i < $groupNodes->length; $i++) {
            $groupName = $groupNodes->item($i)->nodeValue;
            $groups[$groupName] = $groupName;
        }

        // If the user is in the AllowUsers list, or their group is in the AllowGroups list
        // let them through, otherwise 403 them

        //
        if (array_key_exists("AllowUsers", $config)) {
            $count = count($config["AllowUsers"]);
            for ($i = 0; $i < $count; $i++) {
                $user = $config["AllowUsers"][$i];
                if ($user == $this->userName)
                    return true;
            }
        }
        //
        if (array_key_exists("AllowGroups", $config)) {
            $count = count($config["AllowGroups"]);
            for ($i = 0; $i < $count; $i++) {
                $group = $config["AllowGroups"][$i];
                if (array_key_exists($group, $groups))
                    return true;
            }
        }
        return false;
    }

    static function ApplyFeatureSource($resSvc, $app, $layerDefId) {
        $ldfId = new MgResourceIdentifier($layerDefId);
        $ldfContent = $resSvc->GetResourceContent($ldfId);
        $doc = new DOMDocument();
        $doc->loadXML($ldfContent->ToString());
        $vl = $doc->getElementsByTagName("VectorLayerDefinition");
        if ($vl->length == 1) {
            $vlNode = $vl->item(0);
            $fsId = $vlNode->getElementsByTagName("ResourceId");
            $fc = $vlNode->getElementsByTagName("FeatureName");
            $hlink = $vlNode->getElementsByTagName("Hyperlink");
            $tt = $vlNode->getElementsByTagName("ToolTip");
            $flt = $vlNode->getElementsByTagName("Filter");
            $elev = $vlNode->getElementsByTagName("ElevationSettings");
            if ($fsId->length == 1) {
                $app->FeatureSource = new MgResourceIdentifier($fsId->item(0)->nodeValue);
                if ($fc->length == 1) {
                    $app->FeatureClass = $fc->item(0)->nodeValue;
                    $props = array();
                    //Add hyperlink, tooltip and elevation as special computed properties
                    if ($hlink->length == 1 && strlen($hlink->item(0)->nodeValue) > 0) {
                        $props[MgRestConstants::PROP_HYPERLINK] = $hlink->item(0)->nodeValue;
                    }
                    if ($tt->length == 1 && strlen($tt->item(0)->nodeValue) > 0) {
                        $props[MgRestConstants::PROP_TOOLTIP] = $tt->item(0)->nodeValue;
                    }
                    if ($elev->length == 1) {
                        $elevNode = $elev->item(0);
                        $zoff = $elevNode->getElementsByTagName("ZOffset");
                        $zofftype = $elevNode->getElementsByTagName("ZOffsetType");
                        $zext = $elevNode->getElementsByTagName("ZExtrusion");
                        $unit = $elevNode->getElementsByTagName("Unit");
                        if ($zoff->length == 1 && strlen($zoff->item(0)->nodeValue) > 0) {
                            $props[MgRestConstants::PROP_Z_OFFSET] = $zoff->item(0)->nodeValue;
                        } else {
                            $props[MgRestConstants::PROP_Z_OFFSET] = "0";
                        }
                        if ($zofftype->length == 1 && strlen($zofftype->item(0)->nodeValue) > 0) {
                            $props[MgRestConstants::PROP_Z_OFFSET_TYPE] = "'".$zofftype->item(0)->nodeValue."'";
                        } else {
                            $props[MgRestConstants::PROP_Z_OFFSET_TYPE] = "'RelativeToGround'";
                        }
                        if ($zext->length == 1 && strlen($zext->item(0)->nodeValue) > 0) {
                            $props[MgRestConstants::PROP_Z_EXTRUSION] = $zext->item(0)->nodeValue;
                        } else {
                            $props[MgRestConstants::PROP_Z_EXTRUSION] = "0";
                        }
                        if ($unit->length == 1 && strlen($unit->item(0)->nodeValue) > 0) {
                            $props[MgRestConstants::PROP_Z_UNITS] = "'".$unit->item(0)->nodeValue."'";
                        } else {
                            $props[MgRestConstants::PROP_Z_UNITS] = "'Meters'";
                        }
                    }
                    $app->ComputedProperties = $props;
                    //Set filter from layer if defined
                    if ($flt->length == 1 && strlen($flt->item(0)->nodeValue) > 0) {
                        $app->Filter = $flt->item(0)->nodeValue;
                    }
                }
            }
        }
    }

    private function HandleMethod($uriParts, $extension, $method) {
        $uriPath = implode("/", $uriParts);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->container->has($result->adapterName)) {
                throw new Exception("Adapter (".$result->adapterName.") not defined or registered"); //TODO: Localize
            }
            $bAllowAnonymous = false;
            if (array_key_exists("AllowAnonymous", $result->config) && $result->config["AllowAnonymous"] == true)
                $bAllowAnonymous = true;
            try {
                $session = null;
                $extractorName = $result->adapterName."SessionID";
                if ($this->app->container->has($extractorName)) {
                    $extractor = $this->app->container[$extractorName];
                    $session = $extractor->TryGetSessionId($this->app, $method);
                }
                if ($session == null)
                    $session = $this->app->request->params("session");

                $this->EnsureAuthenticationForSite($session, $bAllowAnonymous);
                $siteConn = new MgSiteConnection();
                $siteConn->Open($this->userInfo);
                if ($this->ValidateAcl($siteConn, $result->config)) {
                    $this->app->MgSiteConnection = $siteConn;
                    if (property_exists($result, "LayerDefinition")) {
                        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                        self::ApplyFeatureSource($resSvc, $this->app, $result->LayerDefinition);
                    } else {
                        $this->app->FeatureSource = $result->resId;
                        $this->app->FeatureClass = $result->className;
                    }
                    $this->app->AdapterConfig = $result->config;
                    $this->app->ConfigPath = dirname($path);
                    $this->app->IdentityProperty = $result->IdentityProperty;
                    $adapter = $this->app->container[$result->adapterName];
                    $adapter->HandleMethod($method, false);
                } else {
                    $this->app->halt(403, "You are not authorized to access this resource"); //TODO: Localize
                }
            } catch (MgException $ex) {
                $this->OnException($ex);
            }
        }
    }

    private function HandleMethodSingle($uriParts, $id, $extension, $method) {
        $uriPath = implode("/", $uriParts);
        $path = realpath($this->app->config("AppRootDir")."/".$this->app->config("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->app->halt(404, "No data configuration found for URI part: ".$uriPath); //TODO: Localize
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->container->has($result->adapterName)) {
                throw new Exception("Adapter (".$result->adapterName.") not defined or registered"); //TODO: Localize
            }
            $bAllowAnonymous = false;
            if (array_key_exists("AllowAnonymous", $result->config) && $result->config["AllowAnonymous"] == true)
                $bAllowAnonymous = true;
            try {
                $session = null;
                $extractorName = $result->adapterName."SessionID";
                if ($this->app->container->has($extractorName)) {
                    $extractor = $this->app->container[$extractorName];
                    $session = $extractor->TryGetSessionId($this->app, $method);
                }
                if ($session == null)
                    $session = $this->app->request->params("session");

                $this->EnsureAuthenticationForSite($session, $bAllowAnonymous);
                $siteConn = new MgSiteConnection();
                $siteConn->Open($this->userInfo);
                if ($this->ValidateAcl($siteConn, $result->config)) {
                    $this->app->MgSiteConnection = $siteConn;
                    if (property_exists($result, "LayerDefinition")) {
                        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                        self::ApplyFeatureSource($resSvc, $this->app, $result->LayerDefinition);
                    } else {
                        $this->app->FeatureSource = $result->resId;
                        $this->app->FeatureClass = $result->className;
                    }
                    $this->app->AdapterConfig = $result->config;
                    $this->app->ConfigPath = dirname($path);
                    $this->app->IdentityProperty = $result->IdentityProperty;
                    $adapter = $this->app->container[$result->adapterName];
                    $adapter->SetFeatureId($id);
                    $adapter->HandleMethod($method, true);
                } else {
                    $this->app->halt(403, "You are not authorized to access this resource"); //TODO: Localize
                }
            } catch (MgException $ex) {
                $this->OnException($ex);
            }
        }
    }
}

?>