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

require_once dirname(__FILE__)."/../util/utils.php";
require_once dirname(__FILE__)."/../core/responsehandler.php";

class MgBaseController extends MgResponseHandler
{
    protected $userInfo;
    protected $userName;
    protected $sessionId;

    protected function __construct($app) {
        parent::__construct($app);
        $this->userInfo = null;
        $this->userName = null;
        $this->sessionId = null;
    }

    public function GetBooleanRequestParameter($name, $defaultValue) {
        $val = strtolower($this->GetRequestParameter($name, $defaultValue));
        if ($val == "true")
            $val = "1";
        else if ($val == "false")
            $val = "0";
        return $val;
    }
    
    protected function TrySetCredentialsFromRequest() {
        //HACK-ish: We must allow username/password request parameters for this
        //operation instead of the normal base64 encoded Basic authentication header
        //
        //So if we find such parameters, stuff them in the PHP_AUTH_USER and PHP_AUTH_PW
        //$_SERVER vars before calling EnsureAuthenticationForSite()
        $user = $this->GetRequestParameter("username");
        $pwd = $this->GetRequestParameter("password");
        if ($user != null) {
            $_SERVER['PHP_AUTH_USER'] = $user;
            if ($pwd != null)
                $_SERVER['PHP_AUTH_PW'] = $pwd;
        }
    }

    protected function EnsureAuthenticationForHttp($callback, $allowAnonymous = false, $agentUri = "", $nominatedSessionId = "", $mimeType = MgMimeType::Html) {
        //agent URI is only required if responses must contain a reference
        //back to the mapagent. This is not the case for most, if not all
        //our scenarios so the passed URI can be assumed to be empty most of the
        //time
        $req = new MgHttpRequest($agentUri);
        $param = $req->GetRequestParam();
        //Try session id first
        $session = $this->GetRequestParameter("session");
        if ($session != null) {
            $param->AddParameter("SESSION", $session);
        } else {
            if ($nominatedSessionId !== "" && $nominatedSessionId !== null) {
                $param->AddParameter("SESSION", $nominatedSessionId);
            } else {
                $username = null;
                $password = "";

                // Username/password extraction logic ripped from PHP implementation of the MapGuide AJAX viewer

                //TODO: Ripped from AJAX viewer. Use the abstractions provided by Slim

                // No session, no credentials explicitely passed. Check for HTTP Auth user/passwd.  Under Apache CGI, the
                // PHP_AUTH_USER and PHP_AUTH_PW are not set.  However, the Apache admin may
                // have rewritten the authentication information to REMOTE_USER.  This is a
                // suggested approach from the Php.net website.

                // Has REMOTE_USER been rewritten?
                if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['REMOTE_USER']) &&
                preg_match('/Basic +(.*)$/i', $_SERVER['REMOTE_USER'], $matches))
                {
                    list($name, $password) = explode(':', base64_decode($matches[1]));
                    $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                    $_SERVER['PHP_AUTH_PW']    = strip_tags($password);
                }


                // REMOTE_USER may also appear as REDIRECT_REMOTE_USER depending on CGI setup.
                //  Check for this as well.
                if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['REDIRECT_REMOTE_USER']) &&
                preg_match('/Basic (.*)$/i', $_SERVER['REDIRECT_REMOTE_USER'], $matches))
                {
                    list($name, $password) = explode(':', base64_decode($matches[1]));
                    $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                    $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
                }

                // Finally, PHP_AUTH_USER may actually be defined correctly.  If it is set, or
                // has been pulled from REMOTE_USER rewriting then set our USERNAME and PASSWORD
                // parameters.
                if (isset($_SERVER['PHP_AUTH_USER']) && strlen($_SERVER['PHP_AUTH_USER']) > 0)
                {
                    $username = $_SERVER['PHP_AUTH_USER'];
                    if (isset($_SERVER['PHP_AUTH_PW']) && strlen($_SERVER['PHP_AUTH_PW']) > 0)
                        $password = $_SERVER['PHP_AUTH_PW'];
                }

                //If we have everything we need, put it into the MgHttpRequestParam
                if ($username != null) {
                    $param->AddParameter("USERNAME", $username);
                    if ($password !== "") {
                        $param->AddParameter("PASSWORD", $password);
                    }
                } else {
                    if ($allowAnonymous === true) {
                        $username = "Anonymous";
                        $param->AddParameter("USERNAME", $username);
                    } else {
                        $this->Unauthorized($mimeType);
                    }
                }
            }
        }
        //All good if we get here. Set up common request parameters so upstream callers don't have to
        $param->AddParameter("LOCALE", $this->GetConfig("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());

        //Apply file download parameters if specified
        if ($this->GetRequestParameter("download") === "1" || $this->GetRequestParameter("download") === "true") {
            $param->AddParameter("X-DOWNLOAD-ATTACHMENT", "true");
            if ($this->GetRequestParameter("downloadname")) {
                $param->AddParameter("X-DOWNLOAD-ATTACHMENT-NAME", $this->GetRequestParameter("downloadname"));
            }
        }
        $callback($req, $param);
    }

    protected function EnsureAuthenticationForSite($nominatedSessionId = "", $allowAnonymous = false, $mimeType = MgMimeType::Html) {
        if ($this->userInfo == null) {
            $this->userInfo = new MgUserInformation();
            $this->userInfo->SetClientAgent("MapGuide REST Extension");
            $this->userInfo->SetClientIp($this->GetClientIp());
            //Try session id first
            $session = $this->GetRequestParameter("session");
            if ($session != null) {
                $this->userInfo->SetMgSessionId($session);
                $this->sessionId = $session;
            } else {
                if ($nominatedSessionId != null && $nominatedSessionId !== "") {
                    $this->userInfo->SetMgSessionId($nominatedSessionId);
                    $this->sessionId = $nominatedSessionId;
                } else {
                    //One last fallback, check request header from session id
                    $session = $this->GetRequestHeader("X-MG-SESSION-ID");
                    if ($session != null && $session !== "") {
                        $this->userInfo->SetMgSessionId($session);
                        $this->sessionId = $session;
                    } else {
                        $this->userName = null;
                        $password = "";

                        // Username/password extraction logic ripped from PHP implementation of the MapGuide AJAX viewer

                        //TODO: Ripped from AJAX viewer. Use the abstractions provided by Slim

                        // No session, no credentials explicitely passed. Check for HTTP Auth user/passwd.  Under Apache CGI, the
                        // PHP_AUTH_USER and PHP_AUTH_PW are not set.  However, the Apache admin may
                        // have rewritten the authentication information to REMOTE_USER.  This is a
                        // suggested approach from the Php.net website.

                        // Has REMOTE_USER been rewritten?
                        if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['REMOTE_USER']) &&
                        preg_match('/Basic +(.*)$/i', $_SERVER['REMOTE_USER'], $matches))
                        {
                            list($name, $password) = explode(':', base64_decode($matches[1]));
                            $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                            $_SERVER['PHP_AUTH_PW']    = strip_tags($password);
                        }


                        // REMOTE_USER may also appear as REDIRECT_REMOTE_USER depending on CGI setup.
                        //  Check for this as well.
                        if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['REDIRECT_REMOTE_USER']) &&
                        preg_match('/Basic (.*)$/i', $_SERVER['REDIRECT_REMOTE_USER'], $matches))
                        {
                            list($name, $password) = explode(':', base64_decode($matches[1]));
                            $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                            $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
                        }

                        // Finally, PHP_AUTH_USER may actually be defined correctly.  If it is set, or
                        // has been pulled from REMOTE_USER rewriting then set our USERNAME and PASSWORD
                        // parameters.
                        if (isset($_SERVER['PHP_AUTH_USER']) && strlen($_SERVER['PHP_AUTH_USER']) > 0)
                        {
                            $this->userName = $_SERVER['PHP_AUTH_USER'];
                            if (isset($_SERVER['PHP_AUTH_PW']) && strlen($_SERVER['PHP_AUTH_PW']) > 0)
                                $password = $_SERVER['PHP_AUTH_PW'];
                        }

                        //If we have everything we need, put it into the MgUserInformation
                        if ($this->userName != null) {
                            $this->userInfo->SetMgUsernamePassword($this->userName, $password);
                        } else {
                            if ($allowAnonymous === true) {
                                $this->userInfo->SetMgUsernamePassword("Anonymous", "");
                                $this->userName = "Anonymous";
                            } else {
                                $this->Unauthorized($mimeType);
                            }
                        }
                    }
                }
            }
        }
    }
}