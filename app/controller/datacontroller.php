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

require_once dirname(__FILE__)."/../util/utils.php";
require_once dirname(__FILE__)."/../version.php";
require_once "controller.php";

class MgDataController extends MgBaseController {

    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    private function rrmdir(/*php_string*/ $dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != "." && $object != "..") { 
                    if (filetype($dir."/".$object) == "dir") 
                        $this->rrmdir($dir."/".$object); 
                    else 
                        unlink($dir."/".$object); 
                } 
            } 
            reset($objects); 
            rmdir($dir); 
        } 
    }

    //Checks that the given user is an author or higher-privileged group
    private function ValidateAuthorPrivileges(/*php_string*/ $mimeType) {
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $config = array(
            "AllowRoles" => array("Author", "Administrator")
        );
        if (!$this->ValidateAcl($siteConn, $config))
            $this->Unauthorized($mimeType);
    }

    //Sanitizes the given URI part to strip off all parent navigator parts to prevent attempts
    //to walk outside of the mapguide-rest installation directory when resolved to a file path
    private static function SanitizeUriPath(/*php_string*/ $uriPath) {
        $path = str_replace("/..", "/", $uriPath);
        $path = str_replace("/.", "/", $path);
        $path = str_replace("..", "", $path);
        return $path;
    }

    private static function SanitizeFileName(/*php_string*/ $fileName) {
        $name = self::SanitizeUriPath($fileName);
        $name = str_replace("/", "", $name);
        return $name;
    }

    public function EnumerateDataConfigurations(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges($fmt == "json" ? MgMimeType::Json : MgMimeType::Xml);
        $configRoot = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath"));
        
        $configDir = new RecursiveDirectoryIterator("$configRoot");
        $iterator = new RecursiveIteratorIterator($configDir);
        
        $resp = "<DataConfigurationList>";
        $resp .= "<RootUri>" . $this->app->GetConfig("SelfUrl") . "</RootUri>";
        $resp .= "<MapAgentUrl>".$this->app->GetConfig("MapGuide.MapAgentUrl")."</MapAgentUrl>";
        foreach ($iterator as $conf) {
            if ($conf->getFilename() != "restcfg.json")
                continue;
            $path = $conf->getRealpath();
            
            $confRelPath = str_replace("\\", "/", str_replace($configRoot, "", $path));
            if ($confRelPath[0] == '/' || $confRelPath[0] == '\\') {
                $confRelPath = substr($confRelPath, 1);
            }
            $resp .= "<Configuration>";
            $resp .= "<ConfigUriPart>data/" . str_replace("restcfg.json", "config", $confRelPath) . "</ConfigUriPart>";
            $resp .= "<DocUriPart>data/" . str_replace("restcfg.json", "doc/index.html", $confRelPath) . "</DocUriPart>";
            $resp .= "</Configuration>";
        }
        $resp .= "</DataConfigurationList>";
        if ($fmt == "json") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
            $this->app->SetResponseBody(MgUtils::Xml2Json($resp));
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
            $this->app->SetResponseBody($resp);
        }
    }

    public function EnumerateDataFiles(array $uriParts, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges($fmt == "json" ? MgMimeType::Json : MgMimeType::Xml);

        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));
        } else {
            $resp = "<DataConfigurationFileList>";
            $files = scandir($path);
            foreach ($files as $f) {
                if ($f === "." || $f === ".." || $f === "restcfg.json")
                    continue;
                $resp .= "<File>" . $f . "</File>";
            }
            $resp .= "</DataConfigurationFileList>";
            if ($fmt == "json") {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
                $this->app->SetResponseBody(MgUtils::Xml2Json($resp));
            } else {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
                $this->app->SetResponseBody($resp);
            }
        }
    }

    public function PutDataFile(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges(MgMimeType::Xml);
        $fileName = self::SanitizeFileName($this->app->GetRequestParameter("filename"));

        //Cannot replace restcfg.json
        if ($fileName == "restcfg.json")
            $this->BadRequest($this->app->GetLocalizedText("E_DATA_FILE_NAME_NOT_ALLOWED", $fileName));

        $configPath = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        
        //We must have a restcfg.json in this directory in order to put any other files in it
        if (!file_exists($configPath))
            $this->ServerError($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));

        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath")."/$fileName";
        
        $err = $_FILES["data"]["error"];
        if ($err == 0) {
            move_uploaded_file($_FILES["data"]["tmp_name"], $path);
        } else {
            $this->app->SetResponseStatus(500);
            $this->app->SetResponseBody($this->app->GetLocalizedText("E_PHP_FILE_UPLOAD_ERROR", $err));
        }
    }

    public function DeleteDataFile(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges(MgMimeType::Xml);
        $fileName = self::SanitizeFileName($this->app->GetRequestParameter("filename"));

        //Can't delete restcfg.json
        if ($fileName == "restcfg.json")
            $this->BadRequest($this->app->GetLocalizedText("E_DATA_FILE_NAME_NOT_ALLOWED", $fileName));

        $configPath = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        
        //We must have a restcfg.json in this directory in order to delete any other files in it
        if ($configPath === false)
            $this->ServerError($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));

        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/$fileName");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_DATA_FILE_NOT_FOUND", $fileName));
        } else {
            unlink($path);
        }
    }

    public function GetDataConfiguration(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges(MgMimeType::Xml);
        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));
        } else {
            $this->app->SetResponseBody(file_get_contents($path));
        }
    }

    public function DeleteConfiguration(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges(MgMimeType::Xml);

        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));
        } else {
            $this->rrmdir($path);
        }
    }

    public function PutDataConfiguration(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $this->EnsureAuthenticationForSite();
        $this->ValidateAuthorPrivileges(MgMimeType::Xml);

        $err = $_FILES["data"]["error"];
        if ($err == 0) {
            //Do some basic sanity checks. This file must parse as JSON
            $obj = json_decode(file_get_contents($_FILES["data"]["tmp_name"]));
            if ($obj == NULL) {
                $this->ServerError($this->app->GetLocalizedText("E_DATA_CONFIGURATION_NOT_JSON"));
            }

            $dir = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath");
            if ($dir === FALSE) {
                mkdir($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath");
            }
            $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath")."/restcfg.json";

            move_uploaded_file($_FILES["data"]["tmp_name"], $path);
        } else {
            $this->app->SetResponseStatus(500);
            $this->app->SetResponseBody($this->app->GetLocalizedText("E_PHP_FILE_UPLOAD_ERROR", $err));
        }
    }

    public function GetApiDocViewer(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath));
        } else {
            
            $verPrefix = MgUtils::GetApiVersionNamespace($this->app, "/data/$uriPath");
            $selfUrlUnprefixed = $this->app->GetConfig("SelfUrl");
            $selfUrl = $selfUrlUnprefixed;
            if (strlen($verPrefix) > 0) {
                $selfUrl = $selfUrlUnprefixed."/".$verPrefix;
            }
            
            $docUrl = "$selfUrl/data/$uriPath/apidoc";
            $assetUrlRoot = "$selfUrlUnprefixed/doc";
            $docTpl = $this->app->GetConfig("AppRootDir")."/assets/doc/viewer.tpl";

            $smarty = new Smarty();
            $smarty->setCompileDir($this->app->GetConfig("Cache.RootDir")."/templates_c");
            $smarty->assign("title", $this->app->GetLocalizedText("L_PRODUCT_API_REFERENCE", $uriParts[count($uriParts)-1]));
            $smarty->assign("docUrl", $docUrl);
            $smarty->assign("docAssetRoot", $assetUrlRoot);

            $output = $smarty->fetch($docTpl);
            $this->app->SetResponseHeader("Content-Type", "text/html");
            $this->app->SetResponseBody($output);
        }
    }

    public function GetApiDoc(array $uriParts) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath), MgMimeType::Json);
        } else {
            $config = json_decode(file_get_contents($path), true);
            
            $urlRoot = "/data/$uriPath";
            $hostPart = ((!array_key_exists("HTTPS", $_SERVER) || ($_SERVER['HTTPS'] === "off")) ? "http://" : "https://") . $_SERVER['HTTP_HOST'];
            $verPrefix = MgUtils::GetApiVersionNamespace($this->app, "/data/$uriPath");
            $selfUrl = $this->app->GetConfig("SelfUrl");
            if (strlen($verPrefix) > 0) {
                $selfUrl .= "/" . $verPrefix;
            }
            $basePath = substr($selfUrl, strlen($hostPart));
            
            $apidoc = new stdClass();
            $apidoc->swagger = SWAGGER_API_VERSION;
            $apidoc->info = new stdClass();
            $apidoc->info->version = MG_REST_API_VERSION;
            $apidoc->basePath = $basePath;
            $apidoc->schemes = array("http", "https");
            $apidoc->paths = array();

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
                        $pId->in = "path";
                        $pId->name = "id";
                        $pId->type = "string";
                        $pId->required = true;
                        $pId->description = $this->app->GetLocalizedText("L_REST_GET_ID_DESC");

                        array_push($singleConf->extraParams, $pId);

                        $confs = array($multiConf, $singleConf);
                        foreach ($confs as $conf) {
                            $repDoc = array();
                            $methodsConf = $adapterConfig["Methods"];
                            foreach ($methodsConf as $method => $methodOptions) {
                                $op = $documentor->DocumentOperation($this->app, $method, $extension, $conf->single);
                                foreach ($conf->extraParams as $extraParam) {
                                    array_push($op->parameters, $extraParam);
                                }
                                $repDoc[strtolower($method)] = $op;
                            }
                            $apidoc->paths[$conf->url] = $repDoc;
                        }
                    }
                }
            }

            $this->app->SetResponseHeader("Content-Type", "application/json");
            $this->app->SetResponseBody(json_encode($apidoc));
        }
    }

    private function ValidateConfiguration(array $config, /*php_string*/ $extension, /*php_string*/ $method) {
        if (!array_key_exists("Source", $config))
            throw new Exception($this->app->GetLocalizedText("E_MISSING_ROOT_PROPERTY", "Source"));

        $result = new stdClass();

        $cfgSource = $config["Source"];
        if (!array_key_exists("Type", $cfgSource))
            throw new Exception($this->app->GetLocalizedText("E_MISSING_PROPERTY_IN_SECTION", "Type", "Source"));
        if ($cfgSource["Type"] !== "MapGuide") 
            throw new Exception($this->app->GetLocalizedText("E_UNSUPPORTED_SOURCE_TYPE", $cfgSource["Type"]));
        if (!array_key_exists("LayerDefinition", $cfgSource)) {
            if (!array_key_exists("FeatureSource", $cfgSource))
                throw new Exception($this->app->GetLocalizedText("E_MISSING_PROPERTY_IN_SECTION", "FeatureSource", "Source"));
            if (!array_key_exists("FeatureClass", $cfgSource))
                throw new Exception($this->app->GetLocalizedText("E_MISSING_PROPERTY_IN_SECTION", "FeatureClass", "Source"));
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
            throw new Exception($this->app->GetLocalizedText("E_NO_REPRESENTATIONS_DEFINED_IN_CONFIGURATION"));
        $cfgRep = $config["Representations"];
        if (!array_key_exists($extension, $cfgRep))
            throw new Exception($this->app->GetLocalizedText("E_REPRESENTATION_NOT_HANDLED_OR_SUPPORTED", $extension));
        $cfgExtension = $cfgRep[$extension];
        if (!array_key_exists("Methods", $cfgExtension))
            throw new Exception($this->app->GetLocalizedText("E_REPRESENTATION_CONFIGURATION_MISSING_PROPERTY", "Methods", $extension));
        $cfgMethods = $cfgExtension["Methods"];
        if (!array_key_exists($method, $cfgMethods))
            throw new Exception($this->app->GetLocalizedText("E_METHOD_NOT_SUPPORTED_ON_REPRESENTATION", $extension, $method));

        $result->config = $cfgMethods[$method];
        $result->adapterName = $cfgExtension["Adapter"];
        return $result;
    }

    public function HandleGet(array $uriParts, /*php_string*/ $extension) {
        $this->HandleMethod($uriParts, $extension, "GET");
    }

    public function HandleGetSingle(array $uriParts, /*php_string*/ $id, /*php_string*/ $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "GET");
    }

    public function HandlePost(array $uriParts, /*php_string*/ $extension) {
        $this->HandleMethod($uriParts, $extension, "POST");
    }

    public function HandlePostSingle(array $uriParts, /*php_string*/ $id, /*php_string*/ $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "POST");
    }

    public function HandlePut(array $uriParts, /*php_string*/ $extension) {
        $this->HandleMethod($uriParts, $extension, "PUT");
    }

    public function HandlePutSingle(array $uriParts, $id, /*php_string*/ $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "PUT");
    }

    public function HandleDelete(array $uriParts, /*php_string*/ $extension) {
        $this->HandleMethod($uriParts, $extension, "DELETE");
    }

    public function HandleDeleteSingle(array $uriParts, /*php_string*/ $id, /*php_string*/ $extension) {
        $this->HandleMethodSingle($uriParts, $id, $extension, "DELETE");
    }

    private function ValidateAcl(MgSiteConnection $siteConn, array $config) {
        $site = $siteConn->GetSite();
        if ($this->userName == null && $this->sessionId != null) {
            $this->userName = $site->GetUserForSession();
        }
        return MgUtils::ValidateAcl($this->userName, $site, $config);
    }

    static function ApplyFeatureSource(MgResourceService $resSvc, $app, /*php_string*/ $layerDefId) {
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
                $app->RegisterDependency("FeatureSource", new MgResourceIdentifier($fsId->item(0)->nodeValue));
                if ($fc->length == 1) {
                    $app->RegisterDependency("FeatureClass", $fc->item(0)->nodeValue);
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

    private function HandleMethod(array $uriParts, /*php_string*/ $extension, /*php_string*/ $method) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath), MgMimeType::Json);
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->HasDependency($result->adapterName)) {
                throw new Exception($this->app->GetLocalizedText("E_ADAPTER_NOT_REGISTERED", $result->adapterName));
            }
            $bAllowAnonymous = false;
            if (array_key_exists("AllowAnonymous", $result->config) && $result->config["AllowAnonymous"] == true)
                $bAllowAnonymous = true;
            try {
                $session = null;
                $extractorName = $result->adapterName."SessionID";
                if ($this->app->HasDependency($extractorName)) {
                    $extractor = $this->app->GetDependency($extractorName);
                    $session = $extractor->TryGetSessionId($this->app, $method);
                }
                if ($session == null)
                    $session = $this->app->GetRequestParameter("session");

                $this->EnsureAuthenticationForSite($session, $bAllowAnonymous);
                $siteConn = new MgSiteConnection();
                $siteConn->Open($this->userInfo);
                if ($this->ValidateAcl($siteConn, $result->config)) {
                    $this->app->RegisterDependency("MgSiteConnection", $siteConn);
                    if (property_exists($result, "LayerDefinition")) {
                        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                        self::ApplyFeatureSource($resSvc, $this->app, $result->LayerDefinition);
                    } else {
                        $this->app->RegisterDependency("FeatureSource", $result->resId);
                        $this->app->RegisterDependency("FeatureClass", $result->className);
                    }
                    $this->app->RegisterDependency("AdapterConfig", $result->config);
                    $this->app->RegisterDependency("ConfigPath", dirname($path));
                    $this->app->RegisterDependency("IdentityProperty", $result->IdentityProperty);
                    $adapter = $this->app->GetDependency($result->adapterName);
                    $adapter->HandleMethod($method, false);
                } else {
                    $this->Forbidden($this->app->GetLocalizedText("E_FORBIDDEN_ACCESS"), $this->GetMimeTypeForFormat($extension));
                }
            } catch (MgException $ex) {
                $this->OnException($ex, $this->GetMimeTypeForFormat($extension));
            }
        }
    }

    private function HandleMethodSingle(array $uriParts, /*php_string*/ $id, /*php_string*/ $extension, /*php_string*/ $method) {
        $uriPath = self::SanitizeUriPath(implode("/", $uriParts));
        $path = realpath($this->app->GetConfig("AppRootDir")."/".$this->app->GetConfig("GeoRest.ConfigPath")."/$uriPath/restcfg.json");
        if ($path === false) {
            $this->NotFound($this->app->GetLocalizedText("E_NO_DATA_CONFIGURATION_FOR_URI", $uriPath), MgMimeType::Json);
        } else {
            $config = json_decode(file_get_contents($path), true);
            $result = $this->ValidateConfiguration($config, $extension, $method);
            if (!$this->app->HasDependency($result->adapterName)) {
                throw new Exception($this->app->GetLocalizedText("E_ADAPTER_NOT_REGISTERED", $result->adapterName));
            }
            $bAllowAnonymous = false;
            if (array_key_exists("AllowAnonymous", $result->config) && $result->config["AllowAnonymous"] == true)
                $bAllowAnonymous = true;
            try {
                $session = null;
                $extractorName = $result->adapterName."SessionID";
                if ($this->app->HasDependency($extractorName)) {
                    $extractor = $this->app->GetDependency($extractorName);
                    $session = $extractor->TryGetSessionId($this->app, $method);
                }
                if ($session == null)
                    $session = $this->app->GetRequestParameter("session");

                $this->EnsureAuthenticationForSite($session, $bAllowAnonymous);
                $siteConn = new MgSiteConnection();
                $siteConn->Open($this->userInfo);
                if ($this->ValidateAcl($siteConn, $result->config)) {
                    $this->app->RegisterDependency("MgSiteConnection", $siteConn);
                    if (property_exists($result, "LayerDefinition")) {
                        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                        self::ApplyFeatureSource($resSvc, $this->app, $result->LayerDefinition);
                    } else {
                        $this->app->RegisterDependency("FeatureSource", $result->resId);
                        $this->app->RegisterDependency("FeatureClass", $result->className);
                    }
                    $this->app->RegisterDependency("AdapterConfig", $result->config);
                    $this->app->RegisterDependency("ConfigPath", dirname($path));
                    $this->app->RegisterDependency("IdentityProperty", $result->IdentityProperty);
                    $adapter = $this->app->GetDependency($result->adapterName);
                    $adapter->SetFeatureId($id);
                    $adapter->HandleMethod($method, true);
                } else {
                    $this->Forbidden($this->app->GetLocalizedText("E_FORBIDDEN_ACCESS"), $this->GetMimeTypeForFormat($extension));
                }
            } catch (MgException $ex) {
                $this->OnException($ex, $this->GetMimeTypeForFormat($extension));
            }
        }
    }
}