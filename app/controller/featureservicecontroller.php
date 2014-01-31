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
require_once dirname(__FILE__)."/../util/utils.php";

class MgFeatureServiceController extends MgBaseController {
    const PROP_ALLOW_INSERT = "_MgRestAllowInsert";
    const PROP_ALLOW_UPDATE = "_MgRestAllowUpdate";
    const PROP_ALLOW_DELETE = "_MgRestAllowDelete";

    public function __construct($app) {
        parent::__construct($app);
    }

    private function GetConnectionStringFromRequestParameters() {
        $params = $this->app->request->get();
        $partialConnStr = "";
        foreach ($params as $key => $value) {
            //HACK: In the very infinitsimally small case that there is an FDO connection property named "session", this will obviously break down
            if (strtolower($key) === "session")
                continue;

            if ($partialConnStr === "") {
                $partialConnStr = $key."=".$value;
            } else {
                $partialConnStr .= ";".$key."=".$value;
            }
        }
        return $partialConnStr;
    }

    public function GetConnectPropertyValues($providerName, $propName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetConnectionStringFromRequestParameters();

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
        });
    }

    public function EnumerateDataStores($providerName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetConnectionStringFromRequestParameters();

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
        });
    }

    public function GetProviderCapabilities($providerName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetConnectionStringFromRequestParameters();

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
        });
    }

    public function GetFeatureProviders($format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

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
                $param->AddParameter("XSLSTYLESHEET", "FdoProviderList.xsl");
            }
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetSpatialContexts($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
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
        }, false, "", $sessionId);
    }

    public function GetSchemaNames($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETSCHEMAS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "FeatureSchemaNameList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function DescribeSchema($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr) {
            $param->AddParameter("OPERATION", "DESCRIBEFEATURESCHEMA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "FeatureSchema.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function GetClassNames($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr) {
            $param->AddParameter("OPERATION", "GETCLASSES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ClassNameList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function GetClassDefinition($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
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
        }, false, "", $sessionId);
    }

    private static function HasPermission($resSvc, $resId, $permission) {
        $resHeader = $resSvc->GetResourceHeader($resId);
        $resHeaderDoc = new DOMDocument();
        $resHeaderDoc->loadXML($resHeader->ToString());
        $bAllowed = false;
        $propNodes = $resHeaderDoc->getElementsByTagName("Property");
        for ($i = 0; $i < $propNodes->length; $i++) {
            $propNode = $propNodes->item($i);
            $nameNode = $propNode->getElementsByTagName("Name");
            if ($nameNode->length == 1) {
                if ($nameNode->item(0)->nodeValue === $permission) {
                    $valueNodes = $propNode->getElementsByTagName("Value");
                    if ($valueNodes->length == 1) {
                        if ($valueNodes->item(0)->nodeValue === "1") {
                            $bAllowed = true;
                            break;
                        }
                    }
                }
            }
        }
        return $bAllowed;
    }

    public function InsertFeatures($resId, $schemaName, $className) {
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                $bAllowed = self::HasPermission($resSvc, $resId, self::PROP_ALLOW_INSERT);
                if ($bAllowed === false) {
                    $e = new Exception();
                    $this->OutputException("Forbidden", "Operation not allowed", "The resource ".$resId->ToString()." is not configured to allow feature updates", $e->getTraceAsString(), 403, MgMimeType::Xml);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

            $commands = new MgFeatureCommandCollection();
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $batchProps = MgUtils::ParseMultiFeatureXml($classDef, $this->app->request->getBody());
            $insertCmd = new MgInsertFeatures("$schemaName:$className", $batchProps);
            $commands->Add($insertCmd);

            $result = $featSvc->UpdateFeatures($resId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function UpdateFeatures($resId, $schemaName, $className) {
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                $bAllowed = self::HasPermission($resSvc, $resId, self::PROP_ALLOW_UPDATE);
                if ($bAllowed === false) {
                    $e = new Exception();
                    $this->OutputException("Forbidden", "Operation not allowed", "The resource ".$resId->ToString()." is not configured to allow feature updates", $e->getTraceAsString(), 403, MgMimeType::Xml);
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $doc = new DOMDocument();
            $doc->loadXML($this->app->request->getBody());

            $commands = new MgFeatureCommandCollection();
            $filter = "";
            $filterNode = $doc->getElementsByTagName("Filter");
            if ($filterNode->length == 1)
                $filter = $filterNode->item(0)->nodeValue;
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $props = MgUtils::ParseSingleFeatureDocument($classDef, $doc, "UpdateProperties");
            $updateCmd = new MgUpdateFeatures("$schemaName:$className", $props, $filter);
            $commands->Add($updateCmd);

            $result = $featSvc->UpdateFeatures($resId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function DeleteFeatures($resId, $schemaName, $className) {
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                $bAllowed = self::HasPermission($resSvc, $resId, self::PROP_ALLOW_DELETE);
                if ($bAllowed === false) {
                    $e = new Exception();
                    $this->OutputException("Forbidden", "Operation not allowed", "The resource ".$resId->ToString()." is not configured to allow feature updates", $e->getTraceAsString(), 403, MgMimeType::Xml);
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

            $result = $featSvc->UpdateFeatures($resId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function SelectFeatures($resId, $schemaName, $className, $format) {
        try {
            //Check for unsupported representations
            $fmt = $this->ValidateRepresentation($format, array("xml", "geojson"));

            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $query = new MgFeatureQueryOptions();

            $filter = $this->GetRequestParameter("filter", "");
            $propList = $this->GetRequestParameter("properties", "");
            //$orderby = $this->GetRequestParameter("orderby", "");
            //$orderOptiosn = $this->GetRequestParameter("orderoption", "");
            $spatialFilter = $this->GetRequestParameter("spatialfilter", "");
            $maxFeatures = $this->GetRequestParameter("maxfeatures", "");
            $transformto = $this->GetRequestParameter("transformto", "");

            $finalFilter = "";
            if ($filter !== "") {
                $finalFilter = $filter;
            }
            if ($spatialFilter !== "") {
                if ($finalFilter !== "") {
                    $finalFilter .= " AND " . $spatialFilter;
                } else {
                    $finalFilter = $spatialFilter;
                }
            }
            if ($finalFilter !== "") {
                $query->SetFilter($finalFilter);
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
            $transform = null;
            if ($transformto !== "") {
                $transform = MgUtils::GetTransform($featSvc, $resId, $schemaName, $className, $transformto);
            }

            $reader = $featSvc->SelectFeatures($resId, "$schemaName:$className", $query);
            $result = new MgReaderChunkedResult($this->app, $featSvc, $reader, $limit);
            if ($transform != null)
                $result->SetTransform($transform);
            $result->Output($format);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }
}

?>