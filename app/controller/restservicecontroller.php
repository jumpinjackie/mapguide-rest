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

class MgRestServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function EnumerateApplicationTemplates($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "ENUMERATEAPPLICATIONTEMPLATES");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("XSLSTYLESHEET", "FusionTemplateInfo.xsl");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            }
            $that->ExecuteHttpRequest($req);
        });
    }

    public function EnumerateApplicationWidgets($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "ENUMERATEAPPLICATIONWIDGETS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            }
            $that->ExecuteHttpRequest($req);
        });
    }

    public function EnumerateApplicationContainers($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "ENUMERATEAPPLICATIONCONTAINERS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            }
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetSessionTimeout($sessionId, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $siteConn = new MgSiteConnection();
        $userInfo = new MgUserInformation($sessionId);
        $siteConn->Open($userInfo);
        $site = $siteConn->GetSite();
        $timeout = $site->GetSessionTimeout();

        $body = MgBoxedValue::Int32($timeout, $fmt);
        if ($fmt == "xml") {
            $this->app->response->header("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->response->header("Content-Type", MgMimeType::Json);
        }
        $this->app->response->setBody($body);
    }

    public function CreateSession($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        try {
            TrySetCredentialsFromRequest($this->app->request);
            $this->EnsureAuthenticationForSite();
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            $session = $site->CreateSession();

            $this->app->response->setStatus(201);
            $body = MgBoxedValue::String($session, $fmt);
            if ($fmt == "xml") {
                $this->app->response->header("Content-Type", MgMimeType::Xml);
            } else {
                $this->app->response->header("Content-Type", MgMimeType::Json);
            }
            $this->app->response->setBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }

    public function DestroySession($sessionId) {
        try {
            $siteConn = new MgSiteConnection();
            $userInfo = new MgUserInformation($sessionId);
            $userInfo->SetClientAgent("MapGuide REST Extension");
            $userInfo->SetClientIp($this->GetClientIp());
            $siteConn->Open($userInfo);
            $site = $siteConn->GetSite();
            $site->DestroySession($sessionId);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }
}

?>