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

class MgResourceServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function ApplyResourcePackage() {
        if (!array_key_exists("package", $_FILES))
            $this->app->halt(400, "Missing required file parameter: package"); //TODO: Localize
        
        $this->EnsureAuthenticationForSite();
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $err = $_FILES["package"]["error"];
        if ($err == 0) {
            $source = new MgByteSource($_FILES["package"]["tmp_name"]);
            $reader = $source->GetReader();

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $resSvc->ApplyResourcePackage($reader);
        } else {
            $this->app->response->setBody("File upload error (code: $err). See <a href='http://www.php.net/manual/en/features.file-upload.errors.php'>http://www.php.net/manual/en/features.file-upload.errors.php</a> for more information");
        }
    }

    public function DeleteResource($resId) {
        $resIdStr = $resId->ToString();
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr) {
            $param->AddParameter("OPERATION", "DELETERESOURCE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function GetResourceData($resId, $dataName) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $dataName, $resIdStr) {
            $param->AddParameter("OPERATION", "GETRESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("DATANAME", $dataName);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function EnumerateResourceData($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
        $resName = $resId->GetName().".".$resId->GetResourceType();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $resName) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ResourceDataList.xsl");
                $param->AddParameter("XSLPARAM.RESOURCENAME", $resName);
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function EnumerateResourceReferences($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCEREFERENCES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ResourceReferenceList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetResourceHeader($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETRESOURCEHEADER");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ResourceHeader.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function SetResourceHeader($resId) {
        try {
            $this->EnsureAuthenticationForSite();
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $body = $this->app->request->getBody();
            $bs = new MgByteSource($body, strlen($body));
            $header = $bs->GetReader();

            $resSvc->SetResource($resId, null, $header);

            //$this->app->response->setStatus(201);
            $this->app->response->setBody($resId->ToString());
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function SetResourceContent($resId) {
        try {
            $sessionId = "";
            if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
                $sessionId = $resId->GetRepositoryName();
            }
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $body = $this->app->request->getBody();
            $bs = new MgByteSource($body, strlen($body));
            $content = $bs->GetReader();

            $resSvc->SetResource($resId, $content, null);

            $this->app->response->setStatus(201);
            $this->app->response->setBody($resId->ToString());
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function GetResourceContent($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = "";
        if ($resId->GetRepositoryType() == MgRepositoryType::Session) {
            $sessionId = $resId->GetRepositoryName();
        }
        $resIdStr = $resId->ToString();
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
        }, false, "", $sessionId);
    }

    public function EnumerateResources($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        $resIdStr = $resId->ToString();
        $pathInfo = $this->app->request->getPathInfo();
        $selfUrl = $this->app->config("SelfUrl");
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr, $selfUrl, $pathInfo) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("TYPE", $that->GetRequestParameter("type"));
            $param->AddParameter("COMPUTECHILDREN", $that->GetBooleanRequestParameter("computechildren", "0"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("DEPTH", $that->GetRequestParameter("depth", "1"));
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $thisUrl = $selfUrl.$pathInfo;
                //Chop off the list.html
                $rootPath = substr($thisUrl, 0, strlen($thisUrl) - strlen("list.html"));
                $folderPath = substr($pathInfo, 0, strlen($pathInfo) - strlen("list.html"));
                $tokens = explode("/", $pathInfo);
                if (count($tokens) > 3) {
                    //Pop off list.html and current folder name
                    array_pop($tokens);
                    array_pop($tokens);
                    $parentPath = implode("/", $tokens);
                    $param->AddParameter("XSLPARAM.PARENTPATHROOT", $selfUrl.$parentPath);
                }
                $param->AddParameter("XSLPARAM.ASSETPATH", $selfUrl."/assets");
                $param->AddParameter("XSLPARAM.FOLDERPATH", $folderPath);
                $param->AddParameter("XSLPARAM.ROOTPATH", $rootPath);

                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ResourceList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function CopyResource() {
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that) {
            $param->AddParameter("OPERATION", "COPYRESOURCE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("SOURCE", $that->GetRequestParameter("source"));
            $param->AddParameter("DESTINATION", $that->GetRequestParameter("destination"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("overwrite", "0"));
            $that->ExecuteHttpRequest($req);
        });
    }

    public function MoveResource() {
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that) {
            $param->AddParameter("OPERATION", "MOVERESOURCE");
            $param->AddParameter("VERSION", "2.2.0");
            $param->AddParameter("SOURCE", $that->GetRequestParameter("source"));
            $param->AddParameter("DESTINATION", $that->GetRequestParameter("destination"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("overwrite", "0"));
            $param->AddParameter("OVERWRITE", $that->GetBooleanRequestParameter("cascade", "1"));
            $that->ExecuteHttpRequest($req);
        });
    }
}

?>