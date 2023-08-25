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
    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    public function EnumerateApplicationTemplates(/*php_string*/ $format) {
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

    public function EnumerateApplicationWidgets(/*php_string*/ $format) {
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

    public function EnumerateApplicationContainers(/*php_string*/ $format) {
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

    public function GetSessionTimeout(/*php_string*/ $sessionId, /*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $siteConn = new MgSiteConnection();
        $userInfo = new MgUserInformation($sessionId);
        $siteConn->Open($userInfo);
        $site = $siteConn->GetSite();
        $timeout = $site->GetSessionTimeout();

        $body = MgBoxedValue::Int32($timeout, $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function CreateSession(/*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        try {
            $this->TrySetCredentialsFromRequest();
            $this->EnsureAuthenticationForSite("", false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            $session = $site->CreateSession();

            $this->app->SetResponseStatus(201);
            $body = MgBoxedValue::String($session, $fmt);
            $this->app->SetResponseHeader("Content-Type", $mimeType);
            $this->app->SetResponseBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function DestroySession(/*php_string*/ $sessionId) {
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