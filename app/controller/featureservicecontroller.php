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
require_once dirname(__FILE__)."/../constants.php";
require_once dirname(__FILE__)."/../util/paginatedfeaturereader.php";
require_once dirname(__FILE__)."/../util/readerchunkedresult.php";
require_once dirname(__FILE__)."/../util/czmlresult.php";
require_once dirname(__FILE__)."/../util/utils.php";
require_once dirname(__FILE__)."/../util/whitelist.php";

class MgFeatureServiceController extends MgBaseController {
    const PROP_ALLOW_INSERT = "_MgRestAllowInsert";
    const PROP_ALLOW_UPDATE = "_MgRestAllowUpdate";
    const PROP_ALLOW_DELETE = "_MgRestAllowDelete";
    const PROP_USE_TRANSACTION = "_MgRestUseTransaction";

    private $whitelistConf;
    private $whitelist;

    public function __construct($app) {
        parent::__construct($app);
        $this->whitelistConf = $this->app->config("MapGuide.FeatureSourceConfiguration");
        $this->whitelist = new MgFeatureSourceWhitelist($this->whitelistConf);
    }

    private function VerifyWhitelist($resIdStr, $mimeType, $requiredAction, $requiredRepresentation, $site, $userName) {
        $this->whitelist->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) {
            $this->Forbidden($msg, $mt);
        }, $requiredAction, $requiredRepresentation, $site, $userName);
    }
    
    private function VerifyGlobalWhitelist($mimeType, $requiredAction, $requiredRepresentation, $site, $userName) {
        $this->whitelist->VerifyGlobalWhitelist($mimeType, function($msg, $mt) {
            $this->Forbidden($msg, $mt);
        }, $requiredAction, $requiredRepresentation, $site, $userName);
    }

    public function GetConnectPropertyValues($providerName, $propName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetRequestParameter("connection", "");
        $sessionId = $this->app->request->params("session");

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyGlobalWhitelist($mimeType, "GETCONNECTIONPROPERTYVALUES", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $providerName, $propName, $partialConnStr) {
            $param->AddParameter("OPERATION", "GETCONNECTIONPROPERTYVALUES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("PROVIDER", $providerName);
            $param->AddParameter("PROPERTY", $propName);
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            if ($partialConnStr !== "") {
                $param->AddParameter("CONNECTIONSTRING", $partialConnStr);
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function EnumerateDataStores($providerName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetRequestParameter("connection", "");
        $sessionId = $this->app->request->params("session");
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyGlobalWhitelist($mimeType, "ENUMERATEDATASTORES", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $providerName, $partialConnStr) {
            $param->AddParameter("OPERATION", "ENUMERATEDATASTORES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("PROVIDER", $providerName);
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            if ($partialConnStr !== "") {
                $param->AddParameter("CONNECTIONSTRING", $partialConnStr);
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetProviderCapabilities($providerName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetRequestParameter("connection", "");
        $sessionId = $this->app->request->params("session");

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyGlobalWhitelist($mimeType, "GETPROVIDERCAPABILITIES", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $providerName, $partialConnStr) {
            $param->AddParameter("OPERATION", "GETPROVIDERCAPABILITIES");
            $param->AddParameter("VERSION", "2.0.0");
            $param->AddParameter("PROVIDER", $providerName);
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            if ($partialConnStr !== "") {
                $param->AddParameter("CONNECTIONSTRING", $partialConnStr);
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetFeatureProviders($format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        $sessionId = $this->app->request->params("session");
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyGlobalWhitelist($mimeType, "GETFEATUREPROVIDERS", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "GETFEATUREPROVIDERS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "FdoProviderList.xsl");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetSchemaMapping($format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $provider = $this->GetRequestParameter("provider", "");
        $connStr = $this->GetRequestParameter("connection", "");
        $sessionId = $this->app->request->params("session");
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyGlobalWhitelist($mimeType, "GETSCHEMAMAPPING", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $provider, $connStr) {
            $param->AddParameter("OPERATION", "GETSCHEMAMAPPING");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("PROVIDER", $provider);
            $param->AddParameter("CONNECTIONSTRING", $connStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function TestConnection($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "TESTCONNECTION", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "TESTCONNECTION");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetSpatialContexts($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETSPATIALCONTEXTS", $fmt, $site, $this->userName);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETSPATIALCONTEXTS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("ACTIVEONLY", "0");
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetLongTransactions($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETLONGTRANSACTIONS", $fmt, $site, $this->userName);
        $active = $this->GetBooleanRequestParameter("active", false);

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $active) {
            $param->AddParameter("OPERATION", "GETLONGTRANSACTIONS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("ACTIVEONLY", ($active ? "1" : "0"));
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetSchemaNames($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETSCHEMANAMES", $fmt, $site, $this->userName);

        $resName = $resId->GetName().".".$resId->GetResourceType();
        $pathInfo = $this->app->request->getPathInfo();
        $selfUrl = $this->app->config("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $resName, $selfUrl, $pathInfo) {
            $param->AddParameter("OPERATION", "GETSCHEMAS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $thisUrl = $selfUrl.$pathInfo;
                //Chop off the schemas.html
                $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("schemas.html"));
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "FeatureSchemaNameList.xsl");
                $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);
                $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
                $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function CreateFeatureSource($resId, $inputFormat) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($inputFormat, array("xml", "json"));
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resId->ToString(), $mimeType, "CREATEFEATURESOURCE", $fmt, $site, $this->userName);
        
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

        if ($fmt == "json") {
            $body = $this->app->request->getBody();
            $json = json_decode($body);
            if ($json == NULL)
                throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
        } else {
            $body = $this->app->request->getBody();
            $jsonStr = MgUtils::Xml2Json($body);
            $json = json_decode($jsonStr);
        }

        if (!isset($json->FeatureSourceParams)) {
            throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
        }
        $fsParams = $json->FeatureSourceParams;
        if (!isset($fsParams->File)) {
            throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
        }
        if (!isset($fsParams->SpatialContext)) {
            throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
        }
        if (!isset($fsParams->FeatureSchema)) {
            throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
        }

        $mkParams = new MgFileFeatureSourceParams();
        if (isset($fsParams->File->Provider))
            $mkParams->SetProviderName($fsParams->File->Provider);
        if (isset($fsParams->File->FileName))
            $mkParams->SetFileName($fsParams->File->FileName);
        if (isset($fsParams->SpatialContext->Name))
            $mkParams->SetSpatialContextName($fsParams->SpatialContext->Name);
        if (isset($fsParams->SpatialContext->Description))
            $mkParams->SetSpatialContextDescription($fsParams->SpatialContext->Description);
        if (isset($fsParams->SpatialContext->CoordinateSystem))
            $mkParams->SetCoordinateSystemWkt($fsParams->SpatialContext->CoordinateSystem);
        if (isset($fsParams->SpatialContext->XYTolerance))
            $mkParams->SetXYTolerance($fsParams->SpatialContext->XYTolerance);
        if (isset($fsParams->SpatialContext->ZTolerance))
            $mkParams->SetZTolerance($fsParams->SpatialContext->ZTolerance);

        $mkSchema = $fsParams->FeatureSchema;
        $schema = new MgFeatureSchema();
        if (isset($mkSchema->Name))
            $schema->SetName($mkSchema->Name);
        if (isset($mkSchema->Description))
            $schema->SetDescription($mkSchema->Description);

        $classes = $schema->GetClasses();
        foreach ($mkSchema->ClassDefinition as $mkClass) {
            $cls = new MgClassDefinition();
            if (isset($mkClass->Name))
                $cls->SetName($mkClass->Name);
            if (isset($mkClass->Description))
                $cls->SetDescription($mkClass->Description);

            $clsProps = $cls->GetProperties();
            $idProps = $cls->GetIdentityProperties();
            foreach ($mkClass->PropertyDefinition as $propDef) {
                if (isset($propDef->PropertyType)) {
                    $mkProp = null;
                    switch ($propDef->PropertyType) {
                        case MgFeaturePropertyType::DataProperty:
                            {
                                $mkProp = new MgDataPropertyDefinition($propDef->Name);
                                if (isset($propDef->DataType))
                                    $mkProp->SetDataType($propDef->DataType);
                                if (isset($propDef->Nullable))
                                    $mkProp->SetNullable($propDef->Nullable);
                                if (isset($propDef->IsAutoGenerated))
                                    $mkProp->SetAutoGeneration($propDef->IsAutoGenerated);
                                if (isset($propDef->DefaultValue))
                                    $mkProp->SetDefaultValue($propDef->DefaultValue);
                                if (isset($propDef->Length))
                                    $mkProp->SetLength($propDef->Length);
                                if (isset($propDef->Precision))
                                    $mkProp->SetPrecision($propDef->Precision);
                                if (isset($propDef->Scale))
                                    $mkProp->SetScale($propDef->Scale);
                            }
                            break;
                        case MgFeaturePropertyType::GeometricProperty:
                            {
                                $mkProp = new MgGeometricPropertyDefinition($propDef->Name);
                                if (isset($propDef->GeometryTypes))
                                    $mkProp->SetGeometryTypes($propDef->GeometryTypes);
                                if (isset($propDef->HasElevation))
                                    $mkProp->SetHasElevation($propDef->HasElevation);
                                if (isset($propDef->HasMeasure))
                                    $mkProp->SetHasMeasure($propDef->HasMeasure);
                                if (isset($propDef->SpatialContextAssociation))
                                    $mkProp->SetSpatialContextAssociation($propDef->SpatialContextAssociation);
                            }
                            break;
                        default:
                            throw new Exception($this->app->localizer->getText("E_UNSUPPORTED_PROPERTY_TYPE"));
                    }
                    if ($mkProp != null) {
                        if (isset($propDef->Description))
                            $mkProp->SetDescription($propDef->Description);
                        if (isset($propDef->ReadOnly))
                            $mkProp->SetReadOnly($propDef->ReadOnly);

                        $clsProps->Add($mkProp);
                        if (isset($propDef->IsIdentity) && $propDef->IsIdentity == true)
                            $idProps->Add($mkProp);
                    }
                }
            }

            if (isset($mkClass->DefaultGeometryPropertyName))
                $cls->SetDefaultGeometryPropertyName($mkClass->DefaultGeometryPropertyName);

            $classes->Add($cls);
        }

        $mkParams->SetFeatureSchema($schema);
        try {
            $featSvc->CreateFeatureSource($resId, $mkParams);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function DescribeSchema($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "DESCRIBESCHEMA", $fmt, $site, $this->userName);

        $resName = $resId->GetName().".".$resId->GetResourceType();
        $pathInfo = $this->app->request->getPathInfo();
        $selfUrl = $this->app->config("SelfUrl");

        if ($fmt == "json") {
            //For JSON, we are defining a completely new response format. No point trying to transform the XML version, which is just a plain
            //XML schema. Do we really want to serve a JSON-ified version of that?
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $classNames = explode(",", $this->GetRequestParameter("classnames", ""));
            $collClassNames = null;
            if (count($classNames) > 0)
            {
                $collClassNames = new MgStringCollection();
                for ($i = 0; $i < count($classNames); $i++) {
                    $collClassNames->Add($classNames[$i]);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $schemas = $featSvc->DescribeSchema($resId, $schemaName, $collClassNames);

            if ($collClassNames != null && $collClassNames->GetCount() > 0) {
                MgUtils::EnsurePartialSchema($schemas, $schemaName, $collClassNames);
            }

            $this->app->response->header("Content-Type", MgMimeType::Json);
            $this->app->response->setBody(MgUtils::SchemasToJson($schemas));
        } else {
            $that = $this;
            $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr, $resName, $selfUrl, $pathInfo) {
                $param->AddParameter("OPERATION", "DESCRIBEFEATURESCHEMA");
                $param->AddParameter("VERSION", "1.0.0");
                if ($fmt === "json") {
                    $param->AddParameter("FORMAT", MgMimeType::Json);
                } else if ($fmt === "xml") {
                    $param->AddParameter("FORMAT", MgMimeType::Xml);
                } else if ($fmt === "html") {
                    $thisUrl = $selfUrl.$pathInfo;
                    $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("schemas.html"));
                    $folderPath = substr($pathInfo, 0, strlen($pathInfo) - strlen("schemas.html"));
                    $tokens = explode("/", $pathInfo);
                    if (count($tokens) > 3) {
                        //Pop off schemas.html and current folder name
                        array_pop($tokens);
                        array_pop($tokens);
                        $parentPath = implode("/", $tokens);
                        $param->AddParameter("XSLPARAM.BASEPATH", $selfUrl.$parentPath);
                    }
                    $param->AddParameter("FORMAT", MgMimeType::Xml);
                    $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                    $param->AddParameter("XSLSTYLESHEET", "FeatureSchema.xsl");
                    $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);
                    $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
                    $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
                }
                $param->AddParameter("RESOURCEID", $resIdStr);
                $param->AddParameter("SCHEMA", $schemaName);
                $classNames = $that->GetRequestParameter("classnames", null);
                if ($classNames != null)
                    $param->AddParameter("CLASSNAMES", $classNames);
                $that->ExecuteHttpRequest($req);
            }, false, "", $sessionId, $mimeType);
        }
    }

    public function GetClassNames($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETCLASSNAMES", $fmt, $site, $this->userName);

        $selfUrl = $this->app->config("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr, $selfUrl) {
            $param->AddParameter("OPERATION", "GETCLASSES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "ClassNameList.xsl");
                $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetClassDefinition($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETCLASSDEFINITION", $fmt, $site, $this->userName);

        if ($fmt == "json") {
            //For JSON, we are defining a completely new response format. No point trying to transform the XML version, which is just a plain
            //XML schema. Do we really want to serve a JSON-ified version of that?
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $clsDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);

            $this->app->response->header("Content-Type", MgMimeType::Json);
            $this->app->response->setBody(MgUtils::ClassDefinitionToJson($clsDef));
        } else {
            $that = $this;
            $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $className, $resIdStr) {
                $param->AddParameter("OPERATION", "DESCRIBEFEATURESCHEMA");
                $param->AddParameter("VERSION", "1.0.0");
                if ($fmt === "json")
                    $param->AddParameter("FORMAT", MgMimeType::Json);
                else
                    $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("RESOURCEID", $resIdStr);
                $param->AddParameter("SCHEMA", $schemaName);
                $param->AddParameter("CLASSNAMES", $className);
                $that->ExecuteHttpRequest($req);
            }, false, "", $sessionId, $mimeType);
        }
    }

    public function GetEditCapabilities($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resId->ToString(), $mimeType, "GETEDITCAPABILITIES", $fmt, $site, $this->userName);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $perms = self::CheckPermissions($resSvc, $resId);

        if ($fmt == "json") {
            $resp = new stdClass();
            //This is for consistency with the XML version
            $resp->RestCapabilities = $perms;
            $this->app->response->header("Content-Type", MgMimeType::Json);
            $this->app->response->write(json_encode($resp));
        } else { //xml
            $resp = '<?xml version="1.0" encoding="utf-8"?>';
            $resp .= "<RestCapabilities>";
            $resp .= "<AllowInsert>".($perms->AllowInsert ? "true" : "false")."</AllowInsert>";
            $resp .= "<AllowUpdate>".($perms->AllowUpdate ? "true" : "false")."</AllowUpdate>";
            $resp .= "<AllowDelete>".($perms->AllowDelete ? "true" : "false")."</AllowDelete>";
            $resp .= "<UseTransaction>".($perms->UseTransaction ? "true" : "false")."</UseTransaction>";
            $resp .= "</RestCapabilities>";
            $this->app->response->header("Content-Type", MgMimeType::Xml);
            $this->app->response->write($resp);
        }
    }

    public function SetEditCapabilities($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $this->BadRequest($this->app->localizer->getText("E_NOT_SUPPORTED_FOR_SESSION_RESOURCES"), $mimeType);
        }
        
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resId->ToString(), $mimeType, "SETEDITCAPABILITIES", $fmt, $site, $this->userName);
        
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $perms = new stdClass();
        $perms->AllowInsert = false;
        $perms->AllowUpdate = false;
        $perms->AllowDelete = false;
        $perms->UseTransaction = false;
        
        if ($fmt == "json") {
            $body = $this->app->request->getBody();
            $json = json_decode($body);
            if ($json == NULL)
                throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
            
            if (isset($json->RestCapabilities)) {
                if (isset($json->RestCapabilities->AllowInsert)) {
                    $perms->AllowInsert = $json->RestCapabilities->AllowInsert;
                } 
                if (isset($json->RestCapabilities->AllowUpdate)) {
                    $perms->AllowUpdate = $json->RestCapabilities->AllowUpdate;
                }
                if (isset($json->RestCapabilities->AllowDelete)) {
                    $perms->AllowDelete = $json->RestCapabilities->AllowDelete;
                }
                if (isset($json->RestCapabilities->UseTransaction)) {
                    $perms->UseTransaction = $json->RestCapabilities->UseTransaction;
                }
            } else {
                throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
            }
        } else {
            $body = $this->app->request->getBody();
            $jsonStr = MgUtils::Xml2Json($body);
            $json = json_decode($jsonStr);

            if (isset($json->RestCapabilities)) {
                if (isset($json->RestCapabilities->AllowInsert)) {
                    $perms->AllowInsert = $json->RestCapabilities->AllowInsert;
                } 
                if (isset($json->RestCapabilities->AllowUpdate)) {
                    $perms->AllowUpdate = $json->RestCapabilities->AllowUpdate;
                }
                if (isset($json->RestCapabilities->AllowDelete)) {
                    $perms->AllowDelete = $json->RestCapabilities->AllowDelete;
                }
                if (isset($json->RestCapabilities->UseTransaction)) {
                    $perms->UseTransaction = $json->RestCapabilities->UseTransaction;
                }
            }
        }

        try {
            self::PutEditPermissions($resSvc, $resId, $perms);
            $this->app->response->setStatus(201);
            /*
            $perms = self::CheckPermissions($resSvc, $resId);

            if ($fmt == "json") {
                $resp = new stdClass();
                //This is for consistency with the XML version
                $resp->RestCapabilities = $perms;
                $this->app->response->header("Content-Type", MgMimeType::Json);
                $this->app->response->write(json_encode($resp));
            } else { //xml
                $resp = '<?xml version="1.0" encoding="utf-8"?>';
                $resp .= "<RestCapabilities>";
                $resp .= "<AllowInsert>".($perms->AllowInsert ? "true" : "false")."</AllowInsert>";
                $resp .= "<AllowUpdate>".($perms->AllowUpdate ? "true" : "false")."</AllowUpdate>";
                $resp .= "<AllowDelete>".($perms->AllowDelete ? "true" : "false")."</AllowDelete>";
                $resp .= "<UseTransaction>".($perms->UseTransaction ? "true" : "false")."</UseTransaction>";
                $resp .= "</RestCapabilities>";
                $this->app->response->header("Content-Type", MgMimeType::Xml);
                $this->app->response->write($resp);
            }
            */
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    private static function PutEditPermissions($resSvc, $resId, $perms) {
        $resHeader = $resSvc->GetResourceHeader($resId);
        $jsonStr = MgUtils::Xml2Json($resHeader->ToString());
        $json = json_decode($jsonStr);

        if (!isset($json->ResourceDocumentHeader->Metadata))
            $json->ResourceDocumentHeader->Metadata = new stdClass();

        if (!isset($json->ResourceDocumentHeader->Metadata->Simple))
            $json->ResourceDocumentHeader->Metadata->Simple = new stdClass();

        $props = array();
        if (isset($json->ResourceDocumentHeader->Metadata->Simple->Property)) {
            foreach ($json->ResourceDocumentHeader->Metadata->Simple->Property as $prop) {
                if ($prop->Name == "_MgRestAllowInsert" ||
                    $prop->Name == "_MgRestAllowUpdate" ||
                    $prop->Name == "_MgRestAllowDelete" ||
                    $prop->Name == "_MgRestUseTransaction") {
                    continue;
                }
                array_push($props, $prop);
            }
        }
        $insert = new stdClass();
        $update = new stdClass();
        $delete = new stdClass();
        $trans = new stdClass();
        $insert->Name = "_MgRestAllowInsert";
        $insert->Value = ($perms->AllowInsert === true) ? "1" : "0";
        $update->Name = "_MgRestAllowUpdate";
        $update->Value = ($perms->AllowUpdate === true) ? "1" : "0";
        $delete->Name = "_MgRestAllowDelete";
        $delete->Value = ($perms->AllowDelete === true) ? "1" : "0";
        $trans->Name = "_MgRestUseTransaction";
        $trans->Value = ($perms->UseTransaction === true) ? "1" : "0";
        array_push($props, $insert);
        array_push($props, $update);
        array_push($props, $delete);
        array_push($props, $trans);

        $json->ResourceDocumentHeader->Metadata->Simple->Property = $props;
        $newHeaderXml = MgUtils::Json2Xml($json);

        $bs = new MgByteSource($newHeaderXml, strlen($newHeaderXml));
        $br = $bs->GetReader();
        $resSvc->SetResource($resId, null, $br);
    }

    private static function CheckPermissions($resSvc, $resId) {
        $perms = new stdClass();
        $perms->AllowInsert = false;
        $perms->AllowUpdate = false;
        $perms->AllowDelete = false;
        $perms->UseTransaction = false;

        //A session-based user can do whatever they want on a session-based Feature Source
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $perms->AllowInsert = true;
            $perms->AllowUpdate = true;
            $perms->AllowDelete = true;
            $perms->UseTransaction = false;
            return $perms;
        }

        $resHeader = $resSvc->GetResourceHeader($resId);

        $headerStr = MgUtils::Xml2Json($resHeader->ToString());
        $header = json_decode($headerStr);

        if (isset($header->ResourceDocumentHeader)) {
            if (isset($header->ResourceDocumentHeader->Metadata)) {
                if (isset($header->ResourceDocumentHeader->Metadata->Simple)) {
                    if (isset($header->ResourceDocumentHeader->Metadata->Simple->Property)) {
                        foreach ($header->ResourceDocumentHeader->Metadata->Simple->Property as $prop) {
                            if ($prop->Name === self::PROP_ALLOW_INSERT && $prop->Value === "1") {
                                $perms->AllowInsert = true;
                            } else if ($prop->Name === self::PROP_ALLOW_UPDATE && $prop->Value === "1") {
                                $perms->AllowUpdate = true;
                            } else if ($prop->Name === self::PROP_ALLOW_DELETE && $prop->Value === "1") {
                                $perms->AllowDelete = true;
                            } else if ($prop->Name === self::PROP_USE_TRANSACTION && $prop->Value === "1") {
                                $perms->UseTransaction = true;
                            }
                        }
                    }
                }
            }
        }

        return $perms;
    }

    public function InsertFeatures($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyWhitelist($resId->ToString(), $mimeType, "INSERTFEATURES", $fmt, $site, $this->userName);

            $body = $this->app->request->getBody();
            if ($fmt == "json") {
                $json = json_decode($body);
                if ($json == NULL)
                    throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
                $body = MgUtils::Json2Xml($json);
            }

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);
            
            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->AllowInsert === false) {
                    $e = new Exception();
                    $this->OutputException(
                        "Forbidden", 
                        $this->app->localizer->getText("E_OPERATION_NOT_ALLOWED"),
                        $this->app->localizer->getText("E_FEATURE_SOURCE_NOT_CONFIGURED_TO_ALLOW_UPDATES", $resId->ToString()),
                        $e->getTraceAsString(),
                        403,
                        $mimeType);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

            $commands = new MgFeatureCommandCollection();
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $batchProps = MgUtils::ParseMultiFeatureXml($this->app, $classDef, $body);
            $insertCmd = new MgInsertFeatures("$schemaName:$className", $batchProps);
            $commands->Add($insertCmd);

            if ($perms->UseTransaction === true)
                $trans = $featSvc->BeginTransaction($resId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $featSvc->UpdateFeatures($resId, $commands, false);
            else
                $result = $featSvc->UpdateFeatures($resId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef, ($fmt == "json"));
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, $mimeType);
        }
    }

    public function UpdateFeatures($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyWhitelist($resId->ToString(), $mimeType, "UPDATEFEATURES", $fmt, $site, $this->userName);

            $body = $this->app->request->getBody();
            if ($fmt == "json") {
                $json = json_decode($body);
                if ($json == NULL)
                    throw new Exception($this->app->localizer->getText("E_MALFORMED_JSON_BODY"));
                $body = MgUtils::Json2Xml($json);
            }

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->AllowUpdate === false) {
                    $e = new Exception();
                    $this->OutputException(
                        "Forbidden", 
                        $this->app->localizer->getText("E_OPERATION_NOT_ALLOWED"),
                        $this->app->localizer->getText("E_FEATURE_SOURCE_NOT_CONFIGURED_TO_ALLOW_UPDATES", $resId->ToString()),
                        $e->getTraceAsString(),
                        403,
                        $mimeType);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $doc = new DOMDocument();
            $doc->loadXML($body);

            $commands = new MgFeatureCommandCollection();
            $filter = "";
            $filterNode = $doc->getElementsByTagName("Filter");
            if ($filterNode->length == 1)
                $filter = $filterNode->item(0)->nodeValue;
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $props = MgUtils::ParseSingleFeatureDocument($this->app, $classDef, $doc, "UpdateProperties");
            $updateCmd = new MgUpdateFeatures("$schemaName:$className", $props, $filter);
            $commands->Add($updateCmd);

            if ($perms->UseTransaction === true)
                $trans = $featSvc->BeginTransaction($resId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $featSvc->UpdateFeatures($resId, $commands, false);
            else
                $result = $featSvc->UpdateFeatures($resId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef, ($fmt == "json"));
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, $mimeType);
        }
    }

    public function DeleteFeatures($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyWhitelist($resId->ToString(), $mimeType, "DELETEFEATURES", $fmt, $site, $this->userName);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->AllowDelete === false) {
                    $e = new Exception();
                    $this->OutputException(
                        "Forbidden", 
                        $this->app->localizer->getText("E_OPERATION_NOT_ALLOWED"),
                        $this->app->localizer->getText("E_FEATURE_SOURCE_NOT_CONFIGURED_TO_ALLOW_UPDATES", $resId->ToString()),
                        $e->getTraceAsString(),
                        403,
                        $mimeType);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $commands = new MgFeatureCommandCollection();
            $filter = $this->app->request->params("filter");
            if ($filter == null)
                $filter = "";
            $deleteCmd = new MgDeleteFeatures("$schemaName:$className", $filter);
            $commands->Add($deleteCmd);

            if ($perms->UseTransaction === true)
                $trans = $featSvc->BeginTransaction($resId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $featSvc->UpdateFeatures($resId, $commands, false);
            else
                $result = $featSvc->UpdateFeatures($resId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef, ($fmt == "json"));
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, $mimeType);
        }
    }

    public function SelectAggregates($resId, $schemaName, $className, $type, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $aggType = $this->ValidateValueInDomain($type, array("count", "bbox", "distinctvalues"), $this->GetMimeTypeForFormat($format));
            $distinctPropName = $this->GetRequestParameter("property", "");
            if ($aggType === "distinctvalues" && $distinctPropName === "") {
                $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "property"), $this->GetMimeTypeForFormat($format));
            }

            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyWhitelist($resId->ToString(), $mimeType, "SELECTAGGREGATES", $fmt, $site, $this->userName);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $query = new MgFeatureAggregateOptions();
            $capsXml = MgUtils::GetProviderCapabilties($featSvc, $resSvc, $resId);

            $supportsDistinct = !(strstr($capsXml, "<SupportsSelectDistinct>true</SupportsSelectDistinct>") === false);
            $supportsCount = !(strstr($capsXml, "<Name>Count</Name>") === false);
            $supportsSpatialExtents = !(strstr($capsXml, "<Name>SpatialExtents</Name>") === false);

            switch ($type) {
                case "count":
                    {
                        $count = MgUtils::GetFeatureCount($featSvc, $resId, $schemaName, $className, $supportsCount);
                        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><AggregateResult>";
                        $output .= "<Type>count</Type>";
                        $output .= "<Total>$count</Total>";
                        $output .= "</AggregateResult>";

                        $bs = new MgByteSource($output, strlen($output));
                        $bs->SetMimeType(MgMimeType::Xml);
                        $br = $bs->GetReader();
                        if ($fmt === "json") {
                            $this->OutputXmlByteReaderAsJson($br);
                        } else {
                            $this->OutputByteReader($br);
                        }
                    }
                    break;
                case "bbox":
                    {
                        $geomName = $this->app->request->get("property");
                        $txTo = $this->app->request->get("transformto");
                        $bounds = MgUtils::GetFeatureClassMBR($this->app, $featSvc, $resId, $schemaName, $className, $geomName, $txTo);
                        $iterator = $bounds->extentGeometry->GetCoordinates();
                        $csCode = $bounds->csCode;
                        $csWkt = $bounds->coordinateSystem;
                        $epsg = $bounds->epsg;
                        $firstTime = true;
                        $minX = null; $minY = null; $maxX = null; $maxY = null;
                        while ($iterator->MoveNext())
                        {
                            $x = $iterator->GetCurrent()->GetX();
                            $y = $iterator->GetCurrent()->GetY();
                            if($firstTime)
                            {
                                $maxX = $x;
                                $minX = $x;
                                $maxY = $y;
                                $minY = $y;
                                $firstTime = false;
                            }
                            if($maxX<$x)
                                $maxX = $x;
                            if($minX>$x||$minX==0)
                                $minX = $x;
                            if($maxY<$y)
                                $maxY = $y;
                            if($minY>$y||$minY==0)
                                $minY = $y;
                        }

                        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><AggregateResult>";
                        $output .= "<Type>bbox</Type>";
                        $output .= "<BoundingBox>";
                        $output .= "<CoordinateSystem>";
                        $output .= "<Code>$csCode</Code><EPSG>$epsg</EPSG>";
                        $output .= "</CoordinateSystem>";
                        $output .= "<LowerLeft><X>$minX</X><Y>$minY</Y></LowerLeft>";
                        $output .= "<UpperRight><X>$maxX</X><Y>$maxY</Y></UpperRight>";
                        $output .= "</BoundingBox>";
                        $output .= "</AggregateResult>";

                        $bs = new MgByteSource($output, strlen($output));
                        $bs->SetMimeType(MgMimeType::Xml);
                        $br = $bs->GetReader();
                        if ($fmt === "json") {
                            $this->OutputXmlByteReaderAsJson($br);
                        } else {
                            $this->OutputByteReader($br);
                        }
                    }
                    break;
                case "distinctvalues":
                    {
                        $values = MgUtils::GetDistinctValues($featSvc, $resId, $schemaName, $className, $distinctPropName);
                        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><AggregateResult>";
                        $output .= "<Type>distinctvalues</Type>";
                        $output .= "<ValueList>";
                        foreach ($values as $val) {
                            $output .= "<Value>".MgUtils::EscapeXmlChars($val)."</Value>";
                        }
                        $output .= "</ValueList>";
                        $output .= "</AggregateResult>";

                        $bs = new MgByteSource($output, strlen($output));
                        $bs->SetMimeType(MgMimeType::Xml);
                        $br = $bs->GetReader();
                        if ($fmt === "json") {
                            $this->OutputXmlByteReaderAsJson($br);
                        } else {
                            $this->OutputByteReader($br);
                        }
                    }
                    break;
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function SelectLayerFeatures($ldfId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "geojson", "html", "czml"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            
            $sessionId = "";
            if ($ldfId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $ldfId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId, true, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $query = new MgFeatureQueryOptions();

            $propList = $this->GetRequestParameter("properties", "");
            $filter = $this->GetRequestParameter("filter", "");
            $orderby = $this->GetRequestParameter("orderby", "");
            $orderOptions = $this->GetRequestParameter("orderoption", "");
            $maxFeatures = $this->GetRequestParameter("maxfeatures", "");
            $transformto = $this->GetRequestParameter("transformto", "");
            $bbox = $this->GetRequestParameter("bbox", "");

            $pageSize = $this->GetRequestParameter("pagesize", -1);
            $pageNo = $this->GetRequestParameter("page", -1);

            //Internal debugging flag
            $chunk = $this->GetBooleanRequestParameter("chunk", true);

            if ($pageNo >= 0 && $pageSize === -1) {
                $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "pagesize"), $mimeType);
            } else {
                //The way that CZML output is done means we cannot support pagination
                if ($pageNo >= 0 && $pageSize > 0 && $fmt === "czml") {
                    $this->BadRequest($this->app->localizer->getText("E_CZML_PAGINATION_NOT_SUPPORTED"), $mimeType);
                }
            }

            $limit = -1;
            if ($maxFeatures !== "") {
                $limit = intval($maxFeatures);
            }

            //Load the Layer Definition document and extract the relevant bits of information
            //we're interested in
            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $ldfContent = $resSvc->GetResourceContent($ldfId);
            $doc = new DOMDocument();
            $doc->loadXML($ldfContent->ToString());
            $vl = $doc->getElementsByTagName("VectorLayerDefinition");
            if ($vl->length == 1) {
                $vlNode = $vl->item(0);
                $fsId = $vlNode->getElementsByTagName("ResourceId");
                $fc = $vlNode->getElementsByTagName("FeatureName");
                $hlink = $vlNode->getElementsByTagName("Url");
                $tt = $vlNode->getElementsByTagName("ToolTip");
                $flt = $vlNode->getElementsByTagName("Filter");
                $elev = $vlNode->getElementsByTagName("ElevationSettings");
                if ($fsId->length == 1) {
                    $fsId = new MgResourceIdentifier($fsId->item(0)->nodeValue);

                    $site = $siteConn->GetSite();
                    
                    $this->VerifyWhitelist($fsId->ToString(), $this->GetMimeTypeForFormat($fmt), "SELECTFEATURES", $fmt, $site, $this->userName);                    

                    if ($fc->length == 1) {
                        //Add hyperlink, tooltip and elevation as special computed properties
                        if ($hlink->length == 1 && strlen($hlink->item(0)->nodeValue) > 0) {
                            $query->AddComputedProperty(MgRestConstants::PROP_HYPERLINK, $hlink->item(0)->nodeValue);
                        }
                        if ($tt->length == 1 && strlen($tt->item(0)->nodeValue) > 0) {
                            $query->AddComputedProperty(MgRestConstants::PROP_TOOLTIP, $tt->item(0)->nodeValue);
                        }
                        if ($elev->length == 1) {
                            $elevNode = $elev->item(0);
                            $zoff = $elevNode->getElementsByTagName("ZOffset");
                            $zofftype = $elevNode->getElementsByTagName("ZOffsetType");
                            $zext = $elevNode->getElementsByTagName("ZExtrusion");
                            $unit = $elevNode->getElementsByTagName("Unit");
                            if ($zoff->length == 1 && strlen($zoff->item(0)->nodeValue) > 0) {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_OFFSET, $zoff->item(0)->nodeValue);
                            } else {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_OFFSET, "0");
                            }
                            if ($zofftype->length == 1 && strlen($zofftype->item(0)->nodeValue) > 0) {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_OFFSET_TYPE, "'".$zofftype->item(0)->nodeValue."'");
                            } else {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_OFFSET_TYPE, "'RelativeToGround'");
                            }
                            if ($zext->length == 1 && strlen($zext->item(0)->nodeValue) > 0) {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_EXTRUSION, $zext->item(0)->nodeValue);
                            } else {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_EXTRUSION, "0");
                            }
                            if ($unit->length == 1 && strlen($unit->item(0)->nodeValue) > 0) {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_UNITS, "'".$unit->item(0)->nodeValue."'");
                            } else {
                                $query->AddComputedProperty(MgRestConstants::PROP_Z_UNITS, "'Meters'");
                            }
                        }
                        $baseFilter = "";
                        //Set filter from layer if defined
                        if ($flt->length == 1 && strlen($flt->item(0)->nodeValue) > 0) {
                            if ($filter !== "") {
                                //logical AND with the layer's filter to combine them
                                $baseFilter = "(".$flt->item(0)->nodeValue.") AND (".$filter.")";
                                $query->SetFilter($baseFilter);
                            } else {
                                $baseFilter = $flt->item(0)->nodeValue;
                                $query->SetFilter($baseFilter);
                            }
                        } else {
                            if ($filter !== "") {
                                $baseFilter = $filter;
                                $query->SetFilter($baseFilter);
                            }
                        }

                        $tokens = explode(":", $fc->item(0)->nodeValue);
                        $schemaName = $tokens[0];
                        $className = $tokens[1];
                        $clsDef = NULL;
                        //Unless an explicit property list has been specified, we're explicitly adding all properties
                        //from the class definition
                        if ($propList !== "") {
                            $propNames = explode(",", $propList); //If you have a comma in your property names, it's your own fault :)
                            foreach ($propNames as $propName) {
                                $query->AddFeatureProperty($propName);
                            }
                        } else {
                            if ($clsDef == NULL)
                                $clsDef = $featSvc->GetClassDefinition($fsId, $schemaName, $className);
                            $clsProps = $clsDef->GetProperties();
                            for ($i = 0; $i < $clsProps->GetCount(); $i++) {
                                $propDef = $clsProps->GetItem($i);
                                $query->AddFeatureProperty($propDef->GetName());
                            }
                        }

                        if ($orderby !== "") {
                            $orderPropNames = explode(",", $orderby); //If you have a comma in your property names, it's your own fault :)
                            $orderProps = new MgStringCollection();
                            foreach ($orderPropNames as $propName) {
                                $orderProps->Add($propName);
                            }
                            $orderOpt = MgOrderingOption::Ascending;
                            if (strtolower($orderOptions) === "desc")
                                $orderOpt = MgOrderingOption::Descending;
                            $query->SetOrderingFilter($orderProps, $orderOpt);
                        }

                        //We must require features as LL84 for CZML output
                        if ($fmt == "czml") {
                            $transformto = "LL84";
                        }

                        $transform = null;
                        if ($transformto !== "") {
                            $transform = MgUtils::GetTransform($featSvc, $fsId, $schemaName, $className, $transformto);
                        }

                        if ($bbox !== "") {
                            $parts = explode(",", $bbox);
                            if (count($parts) == 4) {
                                $wktRw = new MgWktReaderWriter();
                                if ($clsDef == NULL)
                                    $clsDef = $featSvc->GetClassDefinition($fsId, $schemaName, $className);
                                $geom = $wktRw->Read(MgUtils::MakeWktPolygon($parts[0], $parts[1], $parts[2], $parts[3]));
                                
                                //Transform bbox to target cs if flag specified
                                $bboxIsTargetCs = $this->GetBooleanRequestParameter("bboxistargetcs", false);
                                if ($bboxIsTargetCs) {
                                    //Because it has been declared the bbox is in target coordiantes, we have to transform that bbox back to the
                                    //source, which means we need an inverse transform
                                    $invTx = MgUtils::GetTransform($featSvc, $fsId, $schemaName, $className, $transformto, true /* invert */);
                                    $geom = $geom->Transform($invTx);
                                }
                                $query->SetSpatialFilter($clsDef->GetDefaultGeometryPropertyName(), $geom, MgFeatureSpatialOperations::EnvelopeIntersects);
                            }
                        }

                        //Ensure valid page number if specified
                        if ($pageSize > 0) {
                            if ($pageNo < 1) {
                                $pageNo = 1;
                            }
                        }

                        $owriter = null;
                        if ($chunk === "0")
                            $owriter = new MgSlimChunkWriter($this->app);
                        else
                            $owriter = new MgHttpChunkWriter();

                        if ($fmt == "czml") {
                            $result = new MgCzmlResult($featSvc, $fsId, "$schemaName:$className", $query, $limit, $baseFilter, $vlNode, $owriter);
                            $result->CheckAndSetDownloadHeaders($this->app, $format);
                            if ($transform != null)
                                $result->SetTransform($transform);
                            $result->Output($format);
                        } else {
                            $reader = $featSvc->SelectFeatures($fsId, "$schemaName:$className", $query);
                            if ($pageSize > 0) {
                                $pageReader = new MgPaginatedFeatureReader($reader, $pageSize, $pageNo, $limit);
                                $result = new MgReaderChunkedResult($featSvc, $pageReader, $limit, $owriter, $this->app->localizer);
                            } else {
                                $result = new MgReaderChunkedResult($featSvc, $reader, $limit, $owriter, $this->app->localizer);
                            }
                            $result->CheckAndSetDownloadHeaders($this->app, $format);
                            if ($transform != null)
                                $result->SetTransform($transform);
                            if ($fmt === "html") {
                                $result->SetHtmlParams($this->app);
                            }
                            $result->Output($format);
                        }
                    } else {
                        throw new Exception($this->app->localizer->getText("E_LAYER_HAS_INVALID_FEATURE_CLASS", $ldfId->ToString()));
                    }
                } else {
                    throw new Exception($this->app->localizer->getText("E_LAYER_HAS_INVALID_FEATURE_SOURCE", $ldfId->ToString()));
                }
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function SelectFeatures($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "html", "geojson"));
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            
            $this->EnsureAuthenticationForSite($sessionId, true, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyWhitelist($resId->ToString(), $mimeType, "SELECTFEATURES", $fmt, $site, $this->userName);

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $query = new MgFeatureQueryOptions();

            $filter = $this->GetRequestParameter("filter", "");
            $propList = $this->GetRequestParameter("properties", "");
            $orderby = $this->GetRequestParameter("orderby", "");
            $orderOptions = $this->GetRequestParameter("orderoption", "asc");
            $maxFeatures = $this->GetRequestParameter("maxfeatures", "");
            $transformto = $this->GetRequestParameter("transformto", "");
            $bbox = $this->GetRequestParameter("bbox", "");

            $pageSize = $this->GetRequestParameter("pagesize", -1);
            $pageNo = $this->GetRequestParameter("page", -1);

            //Internal debugging flag
            $chunk = $this->GetBooleanRequestParameter("chunk", true);

            if ($pageNo >= 0 && $pageSize === -1) {
                $this->BadRequest($this->app->localizer->getText("E_MISSING_REQUIRED_PARAMETER", "pagesize"), $mimeType);
            }

            if ($filter !== "") {
                $query->SetFilter($filter);
            }
            $limit = -1;
            if ($maxFeatures !== "") {
                $limit = intval($maxFeatures);
            }
            if ($propList !== "") {
                $propNames = explode(",", $propList); //If you have a comma in your property names, it's your own fault :)
                foreach ($propNames as $propName) {
                    $query->AddFeatureProperty($propName);
                }
            }
            if ($orderby !== "") {
                $orderPropNames = explode(",", $orderby); //If you have a comma in your property names, it's your own fault :)
                $orderProps = new MgStringCollection();
                foreach ($orderPropNames as $propName) {
                    $orderProps->Add($propName);
                }
                $orderOpt = MgOrderingOption::Ascending;
                if (strtolower($orderOptions) === "desc")
                    $orderOpt = MgOrderingOption::Descending;
                $query->SetOrderingFilter($orderProps, $orderOpt);
            }
            
            $transform = null;
            if ($transformto !== "") {
                $transform = MgUtils::GetTransform($featSvc, $resId, $schemaName, $className, $transformto);
            }
            
            if ($bbox !== "") {
                $parts = explode(",", $bbox);
                if (count($parts) == 4) {
                    $wktRw = new MgWktReaderWriter();
                    $geom = $wktRw->Read(MgUtils::MakeWktPolygon($parts[0], $parts[1], $parts[2], $parts[3]));
                    
                    //Transform the bbox if we have the flag indicating so
                    $bboxIsTargetCs = $this->GetBooleanRequestParameter("bboxistargetcs", false);
                    if ($bboxIsTargetCs) {
                        //Because it has been declared the bbox is in target coordiantes, we have to transform that bbox back to the
                        //source, which means we need an inverse transform
                        $invTx = MgUtils::GetTransform($featSvc, $resId, $schemaName, $className, $transformto, true /* invert */);
                        $geom = $geom->Transform($invTx);
                    }
                    
                    $clsDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
                    $query->SetSpatialFilter($clsDef->GetDefaultGeometryPropertyName(), $geom, MgFeatureSpatialOperations::EnvelopeIntersects);
                }
            }

            $reader = $featSvc->SelectFeatures($resId, "$schemaName:$className", $query);

            $owriter = null;
            if ($chunk === "0")
                $owriter = new MgSlimChunkWriter($this->app);
            else
                $owriter = new MgHttpChunkWriter();

            if ($pageSize > 0) {
                if ($pageNo < 1) {
                    $pageNo = 1;
                }
                $pageReader = new MgPaginatedFeatureReader($reader, $pageSize, $pageNo, $limit);
                $result = new MgReaderChunkedResult($featSvc, $pageReader, $limit, $owriter, $this->app->localizer);
            } else {
                $result = new MgReaderChunkedResult($featSvc, $reader, $limit, $owriter, $this->app->localizer);
            }
            $result->CheckAndSetDownloadHeaders($this->app, $format);
            if ($transform != null)
                $result->SetTransform($transform);
            if ($fmt === "html") {
                $result->SetHtmlParams($this->app);
            }
            $result->Output($format);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }
}

?>
