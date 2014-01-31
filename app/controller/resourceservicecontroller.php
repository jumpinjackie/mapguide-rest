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

    public function DeleteResource($resId) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr) {
            $param->AddParameter("OPERATION", "DELETERESOURCE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
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

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCEDATA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "ResourceDataList.xsl");
            }
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
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

    public function SetResourceContent($resId) {
        try {
            $resIdStr = $resId->ToString();
            $this->EnsureAuthenticationForSite();
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
        });
    }

    public function EnumerateResources($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "ENUMERATERESOURCES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("TYPE", $that->GetRequestParameter("type"));
            $param->AddParameter("COMPUTECHILDREN", $that->GetRequestParameter("computechildren", "0"));
            //Default the depth to 1 if not specified (think of the MapGuide Server!)
            $param->AddParameter("DEPTH", $that->GetRequestParameter("depth", "1"));
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
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
            $param->AddParameter("OVERWRITE", $that->GetRequestParameter("overwrite", "0"));
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
            $param->AddParameter("OVERWRITE", $that->GetRequestParameter("overwrite", "0"));
            $param->AddParameter("OVERWRITE", $that->GetRequestParameter("cascade", "1"));
            $that->ExecuteHttpRequest($req);
        });
    }
}

?>