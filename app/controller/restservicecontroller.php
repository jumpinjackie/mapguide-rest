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

    public function GetSessionTimeout($sessionId, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $siteConn = new MgSiteConnection();
        $userInfo = new MgUserInformation($sessionId);
        $siteConn->Open($userInfo);
        $site = $siteConn->GetSite();
        $timeout = $site->GetSessionTimeout();

        $output = "<SessionTimeout><Value>$timeout</Value></SessionTimeout>";
        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($fmt === "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function CreateSession() {
        try {
            $this->EnsureAuthenticationForSite();
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);
            $site = $siteConn->GetSite();
            $session = $site->CreateSession();

            $this->app->response->setStatus(201);
            $this->app->response->setBody($session);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    public function DestroySession($sessionId) {
        try {
            $siteConn = new MgSiteConnection();
            $userInfo = new MgUserInformation($sessionId);
            $siteConn->Open($userInfo);
            $site = $siteConn->GetSite();
            $site->DestroySession($sessionId);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }
}

?>