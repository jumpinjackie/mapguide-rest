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
    const PROP_USE_TRANSACTION = "_MgRestUseTransaction";

    public function __construct($app) {
        parent::__construct($app);
    }

    public function GetConnectPropertyValues($providerName, $propName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $partialConnStr = $this->GetRequestParameter("connection", "");

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

        $partialConnStr = $this->GetRequestParameter("connection", "");

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

        $partialConnStr = $this->GetRequestParameter("connection", "");

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

    public function GetSchemaMapping($format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $provider = $this->GetRequestParameter("provider", "");
        $connStr = $this->GetRequestParameter("connection", "");
        $sessionId = $this->app->request->params("session");

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
        }, false, "", $sessionId);
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
                $param->AddParameter("XSLSTYLESHEET", "FeatureSchemaNameList.xsl");
                $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);
                $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
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

    private static function CheckPermissions($resSvc, $resId) {
        $perms = new stdClass();
        $perms->allowInsert = false;
        $perms->allowUpdate = false;
        $perms->allowDelete = false;
        $perms->useTransaction = false;

        //A session-based user can do whatever they want on a session-based Feature Source
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $perms->allowInsert = true;
            $perms->allowUpdate = true;
            $perms->allowDelete = true;
            $perms->useTransaction = false;
            return $perms;
        }

        $resHeader = $resSvc->GetResourceHeader($resId);
        $resHeaderDoc = new DOMDocument();
        $resHeaderDoc->loadXML($resHeader->ToString());
        $propNodes = $resHeaderDoc->getElementsByTagName("Property");
        for ($i = 0; $i < $propNodes->length; $i++) {
            $propNode = $propNodes->item($i);
            $nameNode = $propNode->getElementsByTagName("Name");
            if ($nameNode->length == 1) {
                if ($nameNode->item(0)->nodeValue === self::PROP_ALLOW_INSERT) {
                    $valueNodes = $propNode->getElementsByTagName("Value");
                    if ($valueNodes->length == 1) {
                        if ($valueNodes->item(0)->nodeValue === "1") {
                            $perms->allowInsert = true;
                        }
                    }
                }
                else if ($nameNode->item(0)->nodeValue === self::PROP_ALLOW_UPDATE) {
                    $valueNodes = $propNode->getElementsByTagName("Value");
                    if ($valueNodes->length == 1) {
                        if ($valueNodes->item(0)->nodeValue === "1") {
                            $perms->allowUpdate = true;
                        }
                    }
                }
                else if ($nameNode->item(0)->nodeValue === self::PROP_ALLOW_DELETE) {
                    $valueNodes = $propNode->getElementsByTagName("Value");
                    if ($valueNodes->length == 1) {
                        if ($valueNodes->item(0)->nodeValue === "1") {
                            $perms->allowDelete = true;
                        }
                    }
                }
                else if ($nameNode->item(0)->nodeValue === self::PROP_USE_TRANSACTION) {
                    $valueNodes = $propNode->getElementsByTagName("Value");
                    if ($valueNodes->length == 1) {
                        if ($valueNodes->item(0)->nodeValue === "1") {
                            $perms->useTransaction = true;
                        }
                    }
                }
            }
        }
        return $perms;
    }

    public function InsertFeatures($resId, $schemaName, $className) {
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->allowInsert === false) {
                    $e = new Exception();
                    $this->OutputException("Forbidden", "Operation not allowed", "The resource ".$resId->ToString()." is not configured to allow feature updates", $e->getTraceAsString(), 403, MgMimeType::Xml); //TODO: Localize
                }
            }

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

            $commands = new MgFeatureCommandCollection();
            $classDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            $batchProps = MgUtils::ParseMultiFeatureXml($classDef, $this->app->request->getBody());
            $insertCmd = new MgInsertFeatures("$schemaName:$className", $batchProps);
            $commands->Add($insertCmd);

            if ($perms->useTransaction === true)
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
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, MgMimeType::Xml);
        }
    }

    public function UpdateFeatures($resId, $schemaName, $className) {
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->allowUpdate === false) {
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

            if ($perms->useTransaction === true)
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
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, MgMimeType::Xml);
        }
    }

    public function DeleteFeatures($resId, $schemaName, $className) {
        $trans = null;
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $perms = self::CheckPermissions($resSvc, $resId);

            //Not a session-based resource, must check for appropriate flag in header before we continue
            if ($sessionId === "") {
                if ($perms->allowDelete === false) {
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

            if ($perms->useTransaction === true)
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
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex, MgMimeType::Xml);
        }
    }

    public function SelectAggregates($resId, $schemaName, $className, $type, $format) {
        try {
            //Check for unsupported representations
            $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
            $aggType = $this->ValidateValueInDomain($type, array("count", "bbox", "distinctvalues"));
            $distinctPropName = $this->GetRequestParameter("property", "");
            if ($aggType === "distinctvalues" && $distinctPropName === "") {
                $this->app->halt(400, "Missing required parameter: property"); //TODO: Localize
            }

            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

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
                        $bounds = MgUtils::GetFeatureClassMBR($featSvc, $resId, $schemaName, $className, $geomName, $txTo);
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
            $mimeType = MgMimeType::Xml;
            if ($fmt === "json")
                $mimeType = MgMimeType::Json;
            $this->OnException($ex, $mimeType);
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
            $this->EnsureAuthenticationForSite($sessionId, true);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
            $query = new MgFeatureQueryOptions();

            $filter = $this->GetRequestParameter("filter", "");
            $propList = $this->GetRequestParameter("properties", "");
            //$orderby = $this->GetRequestParameter("orderby", "");
            //$orderOptiosn = $this->GetRequestParameter("orderoption", "");
            $maxFeatures = $this->GetRequestParameter("maxfeatures", "");
            $transformto = $this->GetRequestParameter("transformto", "");
            $bbox = $this->GetRequestParameter("bbox", "");

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
            if ($bbox !== "") {
                $parts = explode(",", $bbox);
                if (count($parts) == 4) {
                    $wktRw = new MgWktReaderWriter();
                    $geom = $wktRw->Read(MgUtils::MakeWktPolygon($parts[0], $parts[1], $parts[2], $parts[3]));
                    $clsDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
                    $query->SetSpatialFilter($clsDef->GetDefaultGeometryPropertyName(), $geom, MgFeatureSpatialOperations::EnvelopeIntersects);
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
