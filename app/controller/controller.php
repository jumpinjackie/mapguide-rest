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

    protected function __construct($app) {
        parent::__construct($app);
        $this->userInfo = null;
    }

    private function CollectXslParameters($param) {
        $names = $param->GetParameterNames();
        if ($names == null || $names->GetCount() == 0)
            return array();

        $values = array();
        for ($i = 0; $i < $names->GetCount(); $i++) {
            $name = $names->GetItem($i);
            if (MgUtils::StringStartsWith($name, "XSLPARAM.")) {
                $val = $param->GetParameterValue($name);
                $values[substr($name, strlen("XSLPARAM."))] = $val;
            }
        }
        return $values;
    }

    public function ExecuteHttpRequest($req, $chunkResult = false) {
        $param = $req->GetRequestParam();
        $response = $req->Execute();
        $result = $response->GetResult();

        $status = $result->GetStatusCode();
        if ($status == 200) {
            $resultObj = $result->GetResultObject();
            if ($resultObj != null) {
                $this->app->response->headers->set("Content-Type", $result->GetResultContentType());
                if ($resultObj instanceof MgByteReader) {
                    if ($param->GetParameterValue("X-FORCE-JSON-CONVERSION") === "true") {
                        $this->OutputXmlByteReaderAsJson($resultObj);
                    } else {
                        if ($result->GetResultContentType() === MgMimeType::Xml && $param->ContainsParameter("XSLSTYLESHEET")) {
                            $this->app->response->header("Content-Type", MgMimeType::Html);
                            $this->app->response->setBody(MgUtils::XslTransformByteReader($resultObj, $param->GetParameterValue("XSLSTYLESHEET"), $this->CollectXslParameters($param)));
                        } else {
                            $this->OutputByteReader($resultObj, $chunkResult);
                        }
                    }
                } else if ($resultObj instanceof MgStringCollection) {
                    $this->OutputMgStringCollection($resultObj, $param->GetParameterValue("FORMAT"));
                } else if ($resultObj instanceof MgHttpPrimitiveValue) {
                    $this->app->response->setBody($resultObj->ToString());
                } else if (method_exists($resultObj, "ToXml")) {
                    $byteReader = $resultObj->ToXml();
                    $this->OutputByteReader($byteReader, $chunkResult);
                } else {
                    throw new Exception("Could not determine how to output: ".$resultObj->ToString()); //TODO: Localize
                }
            }
        } else {
            $format = $param->GetParameterValue("FORMAT");
            if ($param->ContainsParameter("XSLSTYLESHEET"))
                $format = MgMimeType::Html;

            if ($format != "") {
                $this->OutputError($result, $format);
            } else {
                $this->OutputError($result);
            }
            //throw new Exception("Error executing operation: ".$param->GetParameterValue("OPERATION").". The status code is: $status"); //TODO: Localize
        }
        return $status;
    }

    private function OutputError($result, $mimeType = MgMimeType::Html) {
        $statusMessage = $result->GetHttpStatusMessage();
        $e = new Exception();
        if ($statusMessage === "MgAuthenticationFailedException" || $statusMessage === "MgUnauthorizedAccessException") {
            $this->Unauthorized();
        } else {
            $this->app->response->header("Content-Type", $mimeType);
            //Amend error code for certain classes of errors
            $status = 500;
            if ($statusMessage === "MgResourceNotFoundException" || $statusMessage === "MgResourceDataNotFoundException") {
                $status = 404;
            }
            $this->OutputException($statusMessage, $result->GetErrorMessage(), $result->GetDetailedErrorMessage(), $e->getTraceAsString(), $status, $mimeType);
        }
    }

    protected function EnsureAuthenticationForHttp($callback, $allowAnonymous = false, $agentUri = "") {
        //agent URI is only required if responses must contain a reference
        //back to the mapagent. This is not the case for most, if not all
        //our scenarios so the passed URI can be assumed to be empty most of the
        //time
        $req = new MgHttpRequest($agentUri);
        $param = $req->GetRequestParam();
        //Try session id first
        $session = $this->app->request->params("session");
        if ($session != null) {
            $param->AddParameter("SESSION", $session);
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
                if ($allowAnonymous) {
                    $username = "Anonymous";
                } else {
                    $this->Unauthorized();
                }
            }
        }
        //All good if we get here. Set up common request parameters so upstream callers don't have to
        $param->AddParameter("LOCALE", $this->app->config("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $callback($req, $param);
    }

    protected function EnsureAuthenticationForSite() {
        if ($this->userInfo == null) {
            $this->userInfo = new MgUserInformation();
            $this->userInfo->SetClientAgent("MapGuide REST Extension");
            //Try session id first
            $session = $this->app->request->params("session");
            if ($session != null) {
                $this->userInfo->SetMgSessionId($session);
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

                //If we have everything we need, put it into the MgUserInformation
                if ($username != null) {
                    $this->userInfo->SetMgUsernamePassword($username, $password);
                } else {
                    $this->Unauthorized();
                }
            }
        }
    }
}

?>