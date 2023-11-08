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
require_once dirname(__FILE__)."/../util/whitelist.php";

class MgResourceServiceController extends MgBaseController {
    private $whitelistConf;
    private $whitelist;
    
    public function __construct(IAppServices $app) {
        parent::__construct($app);
        $this->whitelistConf = $this->app->GetConfig("MapGuide.ResourceConfiguration");
        $this->whitelist = new MgWhitelist($this->whitelistConf);
    }
    
    private function VerifyWhitelist(/*php_string*/ $resIdStr, /*php_string*/ $mimeType, /*php_string*/ $requiredAction, /*php_string*/ $requiredRepresentation, MgSite $site, /*php_string*/ $userName) {
        $this->whitelist->VerifyWhitelist($resIdStr, $mimeType, function($msg, $mt) {
            $this->Forbidden($msg, $mt);
        }, $requiredAction, $requiredRepresentation, $site, $userName);
    }
    
    private function VerifyGlobalWhitelist(/*php_string*/ $mimeType, /*php_callable*/ $requiredAction, /*php_string*/ $requiredRepresentation, MgSite $site, /*php_string*/ $userName) {
        $this->whitelist->VerifyGlobalWhitelist($mimeType, function($msg, $mt) {
            $this->Forbidden($msg, $mt);
        }, $requiredAction, $requiredRepresentation, $site, $userName);
    }

    public function EnumerateUnmanagedData(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        
        $sessionId = $this->app->GetRequestParameter("session");
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($SessionId, false);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $this->VerifyGlobalWhitelist($mimeType, "ENUMERATEUNMANAGEDDATA", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "ENUMERATEUNMANAGEDDATA");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("TYPE", $that->app->GetRequestParameter("type"));
            $param->AddParameter("RECURSIVE", $that->GetBooleanRequestParameter("recursive", "0"));
            $param->AddParameter("PATH", $that->app->GetRequestParameter("path"));
            $param->AddParameter("FILTER", $that->app->GetRequestParameter("filter"));
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function ApplyResourcePackage() {
        if (!array_key_exists("package", $_FILES))
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "package"), MgMimeType::Html);
        
        try {
            $sessionId = $this->app->GetRequestParameter("session");
        
            $mimeType = MgMimeType::Json;
            $fmt = "json";
            $this->EnsureAuthenticationForSite($sessionId, false);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            
            $this->VerifyGlobalWhitelist($mimeType, "APPLYRESOURCEPACKAGE", $fmt, $site, $this->userName);

            $err = $_FILES["package"]["error"];
            if ($err == 0) {
                $source = new MgByteSource($_FILES["package"]["tmp_name"]);
                $reader = $source->GetReader();

                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                $resSvc->ApplyResourcePackage($reader);
            } else {
                $this->app->SetResponseStatus(500);
                $this->app->SetResponseBody($this->app->GetLocalizedText("E_PHP_FILE_UPLOAD_ERROR", $err));
            }
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function SetResourceData(MgResourceIdentifier $resId, /*php_string*/ $dataName) {
        if (!array_key_exists("data", $_FILES))
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "data"), MgMimeType::Html);

        $type = $this->app->GetRequestParameter("type", MgResourceDataType::File);
        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        try {
            $mimeType = MgMimeType::Json;
            $fmt = "json";
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);            
            $site = $siteConn->GetSite();
            $resIdStr = $resId->ToString();
            
            $this->VerifyWhitelist($resIdStr, $mimeType, "SETRESOURCEDATA", $fmt, $site, $this->userName);

            $err = $_FILES["data"]["error"];
            if ($err == 0) {
                $source = new MgByteSource($_FILES["data"]["tmp_name"]);
                $reader = $source->GetReader();

                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                if (!$resSvc->ResourceExists($resId))
                    $this->NotFound($this->app->GetLocalizedText("E_RESOURCE_NOT_FOUND", $resId->ToString()));

                $resSvc->SetResourceData($resId, $dataName, $type, $reader);
            } else {
                $this->app->SetResponseStatus(500);
                $this->app->SetResponseBody($this->app->GetLocalizedText("E_PHP_FILE_UPLOAD_ERROR", $err));
            }
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function DeleteResourceData(MgResourceIdentifier $resId, /*php_string*/ $dataName) {
        $resIdStr = $resId->ToString();
        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = MgMimeType::Json;
        $fmt = "json";
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $this->VerifyWhitelist($resIdStr, $mimeType, "DELETERESOURCEDATA", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $dataName) {
            $param->AddParameter("OPERATION", "DELETERESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("DATANAME", $dataName);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function DeleteResource(MgResourceIdentifier $resId) {
        $resIdStr = $resId->ToString();
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = MgMimeType::Json;
        $fmt = "json";
        
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
            return;
        }
        $this->VerifyWhitelist($resIdStr, $mimeType, "DELETERESOURCE", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr) {
            $param->AddParameter("OPERATION", "DELETERESOURCE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetResourceData(MgResourceIdentifier $resId, /*php_string*/ $dataName) {
        $resIdStr = $resId->ToString();
        $that = $this;
        
        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = MgMimeType::Json;
        $fmt = "json";
        
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETRESOURCEDATA", $fmt, $site, $this->userName);
        
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $dataName, $resIdStr) {
            $param->AddParameter("OPERATION", "GETRESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("DATANAME", $dataName);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function EnumerateResourceData(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
            return;
        }
        $resIdStr = $resId->ToString();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "ENUMERATERESOURCEDATA", $fmt, $site, $this->userName);
        
        $resName = $resId->GetName().".".$resId->GetResourceType();
        $pathInfo = $this->app->GetRequestPathInfo();
        $selfUrl = $this->app->GetConfig("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $resName, $selfUrl, $pathInfo) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $thisUrl = $selfUrl.$pathInfo;
                //Chop off the list.html
                $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("data/list.html"));
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "ResourceDataList.xsl");
                $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
                $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);
                $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function EnumerateResourceReferences(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = $this->app->GetRequestParameter("session", "");
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $resIdStr = $resId->ToString();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "ENUMERATERESOURCEREFERENCES", $fmt, $site, $this->userName);

        $resName = $resId->GetName().".".$resId->GetResourceType();
        $selfUrl = $this->app->GetConfig("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $resName, $selfUrl) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCEREFERENCES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "ResourceReferenceList.xsl");
                $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
                $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetResourceHeader(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        
        $sessionId = $this->app->GetRequestParameter("session", "");
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $resIdStr = $resId->ToString();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETRESOURCEHEADER", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETRESOURCEHEADER");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                //HACK: This API doesn't put XML prolog
                $param->AddParameter("X-PREPEND-XML-PROLOG", "true");
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "ResourceHeader.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function SetResourceHeader(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        try {
            $this->EnsureAuthenticationForSite();
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $site = $siteConn->GetSite();
            $resIdStr = $resId->ToString();
            $mimeType = $this->GetMimeTypeForFormat($fmt);
            
            $this->VerifyWhitelist($resIdStr, $mimeType, "SETRESOURCEHEADER", $fmt, $site, $this->userName);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            
            $body = $this->app->GetRequestBody();
            if ($fmt == "json") {
                $json = json_decode($body);
                if ($json == NULL)
                    throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
                $body = MgUtils::Json2Xml($json);

                $bs = new MgByteSource($body, strlen($body));
                $header = $bs->GetReader();
                $resSvc->SetResource($resId, null, $header);
            } else if ($body instanceof \Slim\Http\RequestBody) {
                // Have to funnel body contents to a temp file in order to
                // be able to create a MgByteSource from it
                $tmpPath = tempnam(sys_get_temp_dir(), 'BodyRequest');
                $os = fopen($tmpPath, "w");
                $bufSize = 8196;
                while (!$body->eof()) {
                    $buf = $body->read($bufSize);
                    fwrite($os, $buf);
                }
                fclose($os);

                $bs = new MgByteSource($tmpPath);
                $header = $bs->GetReader();
                $resSvc->SetResource($resId, null, $header);

                // Clean up temp file
                unlink($tmpPath);
            } else {
                throw new Exception("Don't know how to process body");
            }

            //$this->app->SetResponseStatus(201);
            $body = MgBoxedValue::String($resId->ToString(), $fmt);
            if ($fmt == "xml") {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
            } else {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
            }
            $this->app->SetResponseBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($fmt));
        }
    }

    public function SetResourceContent(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            
            $site = $siteConn->GetSite();
            $resIdStr = $resId->ToString();
            $mimeType = $this->GetMimeTypeForFormat($fmt);
            
            $this->VerifyWhitelist($resIdStr, $mimeType, "SETRESOURCE", $fmt, $site, $this->userName);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $body = $this->app->GetRequestBody();
            if ($fmt == "json") {
                $json = json_decode($body);
                if ($json == NULL)
                    throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
                $body = MgUtils::Json2Xml($json);

                $bs = new MgByteSource($body, strlen($body));
                $content = $bs->GetReader();
                $resSvc->SetResource($resId, $content, null);
            } else if ($body instanceof \Slim\Http\RequestBody) {
                // Have to funnel body contents to a temp file in order to
                // be able to create a MgByteSource from it
                $tmpPath = tempnam(sys_get_temp_dir(), 'BodyRequest');
                $os = fopen($tmpPath, "w");
                $bufSize = 8196;
                while (!$body->eof()) {
                    $buf = $body->read($bufSize);
                    fwrite($os, $buf);
                }
                fclose($os);

                $bs = new MgByteSource($tmpPath);
                $content = $bs->GetReader();
                $resSvc->SetResource($resId, $content, null);

                // Clean up temp file
                unlink($tmpPath);
            } else {
                throw new Exception("Don't know how to process body");
            }

            $this->app->SetResponseStatus(201);
            $body = MgBoxedValue::String($resId->ToString(), $fmt);
            if ($fmt == "xml") {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
            } else {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
            }
            $this->app->SetResponseBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($fmt));
        }
    }

    public function SetResourceContentOrHeader(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            
            $contentFilePath = $this->GetFileUploadPath("content");
            $headerFilePath = null;
            //Header not supported for session-based resources
            if ($resId->GetRepositoryType() != MgRepositoryType::Session) {
                $headerFilePath = $this->GetFileUploadPath("header");
            }
            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            
            $site = $siteConn->GetSite();
            $resIdStr = $resId->ToString();
            $mimeType = $this->GetMimeTypeForFormat($fmt);
            
            if ($contentFilePath) {
                $this->VerifyWhitelist($resIdStr, $mimeType, "SETRESOURCE", $fmt, $site, $this->userName);
            }
            if ($headerFilePath) {
                $this->VerifyWhitelist($resIdStr, $mimeType, "SETRESOURCEHEADER", $fmt, $site, $this->userName);
            }
            
            $content = null;
            $header = null;
            if ($contentFilePath != null) {
                $cntSource = new MgByteSource($contentFilePath);
                $content = $cntSource->GetReader();
            }
            if ($headerFilePath != null) {
                $hdrSource = new MgByteSource($headerFilePath);
                $header = $hdrSource->GetReader();
            }

            if ($fmt == "json") {
                if ($content != null) {
                    $body = $content->ToString();
                    $json = json_decode($body);
                    if ($json == NULL)
                        throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
                    $body = MgUtils::Json2Xml($json);
                    $cntSource = new MgByteSource($body, strlen($body));
                    $content = $cntSource->GetReader();
                }
                if ($header != null) {
                    $body = $header->ToString();
                    $json = json_decode($body);
                    if ($json == NULL)
                        throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
                    $body = MgUtils::Json2Xml($json);
                    $hdrSource = new MgByteSource($body, strlen($body));
                    $header = $hdrSource->GetReader();
                }
            }

            $resSvc->SetResource($resId, $content, $header);

            $this->app->SetResponseStatus(201);
            $body = MgBoxedValue::String($resId->ToString(), $fmt);
            if ($fmt == "xml") {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
            } else {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
            }
            $this->app->SetResponseBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function GetResourceContent(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETRESOURCECONTENT", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETRESOURCECONTENT");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function GetResourceInfo(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("html"));
        $sessionId = $this->app->GetRequestParameter("session", "");
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        $this->EnsureAuthenticationForSite($sessionId, false);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        $resIdStr = $resId->ToString();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "GETRESOURCEINFO", $fmt, $site, $this->userName);

        $pathInfo = $this->app->GetRequestPathInfo();
        $selfUrl = $this->app->GetConfig("SelfUrl");
        $thisUrl = MgUtils::EnsureEndingSlash($selfUrl).$pathInfo;
        //Chop off the html part of the url
        $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("/html"));

        $resIdStr = $resId->ToString();
        $smarty = new Smarty();
        $smarty->setCompileDir($this->app->GetConfig("Cache.RootDir")."/templates_c");
        $smarty->assign("resId", $resIdStr);
        $smarty->assign("assetPath", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
        $smarty->assign("urlRoot", $rootPath);
        $smarty->assign("resourceType", $resId->GetResourceType());

        $locale = $this->app->GetConfig("Locale");

        $this->app->SetResponseHeader("Content-Type", MgMimeType::Html);
        $this->app->SetResponseBody($smarty->fetch(dirname(__FILE__)."/../res/templates/$locale/resourceinfo.tpl"));
    }

    public function EnumerateResources(MgResourceIdentifier $resId, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        $resIdStr = $resId->ToString();
        
        $sessionId = $this->app->GetRequestParameter("session", "");
        $mimeType = $this->GetMimeTypeForFormat($fmt);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
            return;
        }
        $this->VerifyWhitelist($resIdStr, $mimeType, "ENUMERATERESOURCES", $fmt, $site, $this->userName);
        $pathInfo = $this->app->GetRequestPathInfo();
        $selfUrl = $this->app->GetConfig("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $selfUrl, $pathInfo) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("TYPE", $that->app->GetRequestParameter("type"));
            $param->AddParameter("COMPUTECHILDREN", $that->GetBooleanRequestParameter("computechildren", "0"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("DEPTH", $that->app->GetRequestParameter("depth", "1"));
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $thisUrl = MgUtils::EnsureEndingSlash($selfUrl).$pathInfo;
                //Chop off the list.html
                $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("list.html"));
                $folderPath = substr($pathInfo, 0, strlen($pathInfo) - strlen("list.html"));
                $tokens = explode("/", $pathInfo);
                if (count($tokens) >= 3) {
                    //Pop off list.html and current folder name
                    array_pop($tokens);
                    array_pop($tokens);
                    $parentPath = implode("/", $tokens);
                    $parentRoot = MgUtils::EnsureEndingSlash($selfUrl).$parentPath;
                    $param->AddParameter("XSLPARAM.PARENTPATHROOT", $parentRoot);
                }
                $param->AddParameter("XSLPARAM.ASSETPATH", MgUtils::GetSelfUrlRoot($selfUrl)."/assets");
                $param->AddParameter("XSLPARAM.FOLDERPATH", $folderPath);
                $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);

                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "ResourceList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $mimeType);
    }

    public function CopyResource() {
        $resIdStr = $this->app->GetRequestParameter("source");
        
        $sessionId = $this->app->GetRequestParameter("session", "");
        $fmt = "json";
        $mimeType = MgMimeType::Json;
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "COPYRESOURCE", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that) {
            $param->AddParameter("OPERATION", "COPYRESOURCE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("SOURCE", $that->app->GetRequestParameter("source"));
            $param->AddParameter("DESTINATION", $that->app->GetRequestParameter("destination"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("overwrite", "0"));
            $that->ExecuteHttpRequest($req);
        });
    }

    public function MoveResource() {
        $resIdStr = $this->app->GetRequestParameter("source");
        
        $sessionId = $this->app->GetRequestParameter("session", "");
        $fmt = "json";
        $mimeType = MgMimeType::Json;
        $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        $site = $siteConn->GetSite();
        
        $this->VerifyWhitelist($resIdStr, $mimeType, "MOVE  RESOURCE", $fmt, $site, $this->userName);
        
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that) {
            $param->AddParameter("OPERATION", "MOVERESOURCE");
            $param->AddParameter("VERSION", "2.2.0");
            $param->AddParameter("SOURCE", $that->app->GetRequestParameter("source"));
            $param->AddParameter("DESTINATION", $that->app->GetRequestParameter("destination"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("overwrite", "0"));
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("cascade", "1"));
            $that->ExecuteHttpRequest($req);
        });
    }
}