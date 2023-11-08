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

class MgSiteAdminController extends MgBaseController {
    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    public function GetSiteInformation(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "GETSITEINFO");
            $param->AddParameter("VERSION", "2.2.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            if ($fmt === "json") {
                //Instructs ExecuteHttpRequest to force convert the XML content to JSON. This is a workaround
                //for mapagent APIs that do not support the FORMAT request parameter to let us specify a JSON
                //response (which is a bug, because they should!)
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $this->GetMimeTypeForFormat($format));
    }

    public function GetSiteStatus(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = $this->app->GetRequestParameter("session");
        $mimeType = $this->GetMimeTypeForFormat($format);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            //WTF?: MgServerAdmin::Open will happily work with bogus credentials????
            //HACK: Check with a MgSiteConnection first
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $admin = new MgServerAdmin();
            $admin->Open($this->userInfo);
            $status = $admin->GetSiteStatus();
            $this->OutputMgPropertyCollection($status, $mimeType);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function GetSiteVersion(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        try {
            $this->EnsureAuthenticationForSite("", false, $mimeType);
            //WTF?: MgServerAdmin::Open will happily work with bogus credentials????
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            //HACK: Check with a MgSiteConnection first
            $admin = new MgServerAdmin();
            $admin->Open($this->userInfo);
            $body = MgBoxedValue::String($admin->GetSiteVersion(), $fmt);
            $this->app->SetResponseHeader("Content-Type", $mimeType);
            $this->app->SetResponseBody($body);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function EnumerateGroups(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "ENUMERATEGROUPS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            if ($fmt === "json") {
                //Instructs ExecuteHttpRequest to force convert the XML content to JSON. This is a workaround
                //for mapagent APIs that do not support the FORMAT request parameter to let us specify a JSON
                //response (which is a bug, because they should!)
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $this->GetMimeTypeForFormat($format));
    }

    public function EnumerateUsersForGroup(/*php_string*/ $groupName, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $groupName) {
            $param->AddParameter("OPERATION", "ENUMERATEUSERS");
            $param->AddParameter("GROUP", $groupName);
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $this->GetMimeTypeForFormat($format));
    }

    public function EnumerateGroupsForUser(/*php_string*/ $userName, /*php_string*/ $format) {
        $sessionId = $this->app->GetRequestParameter("session");
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $site = $siteConn->GetSite();
            try {
                $user = $site->GetUserForSession();
                //Hmmm. Should we allow Anonymous to discover its own roles?
                if($user === "Anonymous" && $userName !== "Anonymous") {
                    $this->Unauthorized($mimeType);
                }
            } catch (MgException $ex) {
                //Could happen if we have non-anonymous credentials in the http authentication header
            }
            $content = $site->EnumerateGroups($userName);

            if ($fmt === "json") {
                $this->OutputXmlByteReaderAsJson($content);
            } else {
                $this->OutputByteReader($content);
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }

    public function EnumerateRolesForUser(/*php_string*/ $userName, /*php_string*/ $format) {
        $sessionId = $this->app->GetRequestParameter("session");
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        try {
            $this->EnsureAuthenticationForSite($sessionId, false, $mimeType);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $site = $siteConn->GetSite();
            try {
                $user = $site->GetUserForSession();
                //Hmmm. Should we allow Anonymous to discover its own roles?
                if($user === "Anonymous" && $userName !== "Anonymous") {
                    $this->Unauthorized($mimeType);
                }
            } catch (MgException $ex) {
                //Could happen if we have non-anonymous credentials in the http authentication header
            }

            $content = $site->EnumerateRoles($userName);
            $this->app->SetResponseHeader("Content-Type", $mimeType);
            $this->OutputMgStringCollection($content, $mimeType);
        } catch (MgException $ex) {
            $this->OnException($ex, $mimeType);
        }
    }
}