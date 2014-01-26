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

class MgBaseController
{
    protected $app;
    protected $userInfo;

    protected function __construct($app) {
        $this->app = $app;
        $this->userInfo = null;
    }

    protected function OutputXmlByteReaderAsJson($byteReader) {
        $content = MgUtils::Xml2Json($byteReader->ToString());
        $this->app->response->header("Content-Type", MgMimeType::Json);
        $this->app->response->write($content);
    }

    protected function OutputByteReader($byteReader, $chunkResult = false) {
        $rdrLen = $byteReader->GetLength();
        do
        {
            $data = str_pad("\0", 50000, "\0");
            $len = $byteReader->Read($data, 50000);
            if ($len > 0)
            {
                $this->app->response->write(substr($data, 0, $len));
            }
        } while ($len > 0);
        /*
        $buffer = '';
        $contentLen = 0;
        do
        {
            $data = str_pad("\0", 50000, "\0");
            $len = $byteReader->Read($data, 50000);
            if ($len > 0)
            {
                $contentLen = $contentLen + $len;
                $buffer = $buffer . substr($data, 0, $len);
            }
        } while ($len > 0);
        $this->app->response->setBody($buffer);
        $this->app->response->headers->set("Content-Length", $contentLen);
        */
    }

    protected function ValidateRepresentation($format, $validRepresentations = null) {
        if ($validRepresentations == null) {
            return $format;
        } else {
            $fmt = strtolower($format);
            foreach ($validRepresentations as $vr) {
                $rep = strtolower($vr);
                if ($rep === $fmt)
                    return $fmt;
            }
        }
        $this->app->halt(400, "Unsupported representation: ".$format); //TODO: Localize
    }

    protected function OutputMgPropertyCollection($props, $mimeType = MgMimeType::Xml) {
        $content = "<PropertyCollection />";
        $count = $props->GetCount();
        $agfRw = null;
        $wktRw = null;
        $this->app->response->header("Content-Type", $mimeType);
        if ($count > 0) {
            $content = "<PropertyCollection>";
            for ($i = 0; $i < $count; $i++) {
                $prop = $props->GetItem($i);
                $name = $prop->GetName();
                $type = null;
                $propType = $prop->GetPropertyType();
                switch ($propType) {
                    case MgPropertyType::Boolean:
                        $type = "boolean";
                        break;
                    case MgPropertyType::Byte:
                        $type = "byte";
                        break;
                    case MgPropertyType::DateTime:
                        $type = "datetime";
                        break;
                    case MgPropertyType::Decimal:
                    case MgPropertyType::Double:
                        $type = "double";
                        break;
                    case MgPropertyType::Geometry:
                        $type = "geometry";
                        break;
                    case MgPropertyType::Int16:
                        $type = "int16";
                        break;
                    case MgPropertyType::Int32:
                        $type = "int32";
                        break;
                    case MgPropertyType::Int64:
                        $type = "int64";
                        break;
                    case MgPropertyType::Single:
                        $type = "single";
                        break;
                    case MgPropertyType::String:
                        $type = "string";
                        break;
                }

                if ($prop->IsNull()) {
                    $content .= "<Property><Name>$name</Name><Type>$type</Type></Property>";
                } else {
                    $value = "";
                    if ($propType === MgPropertyType::DateTime) {
                        $dt = $prop->GetValue();
                        $value = $dt->ToString();
                    } else if ($propType === MgPropertyType::Geometry) {
                        if ($wktRw == null)
                            $wktRw = new MgWktReaderWriter();
                        if ($agfRw == null)
                            $agfRw = new MgAgfReaderWriter();

                        try {
                            $agf = $prop->GetValue();
                            $geom = $agfRw->Read($agf);
                            if ($geom != null) {
                                $value = $wktRw->Write($geom);
                            }
                        } catch (MgException $ex) {
                            $value = "";
                        }
                    } else {
                        $value = $prop->GetValue();
                    }
                    $content .= "<Property><Name>$name</Name><Type>$type</Type><Value>$value</Value></Property>";
                }
            }
            $content .= "</PropertyCollection>";
        }
        if ($mimeType === MgMimeType::Json) {
            $content = MgUtils::Xml2Json($content);
        }
        $this->app->response->header("Content-Type", $mimeType);
        $this->app->response->setBody($content);
    }

    protected function OutputMgStringCollection($strCol, $mimeType = MgMimeType::Xml) {
        $content = "<StringCollection />";
        if ($strCol != null) {
            // MgStringCollection::ToXml() doesn't seem to be reliable in PHP (bug?), so do this manually
            $count = $strCol->GetCount();
            $content = "<StringCollection>";
            for ($i = 0; $i < $count; $i++) {
                $value = MgUtils::EscapeXmlChars($strCol->GetItem($i));
                $content .= "<Item>$value</Item>";
            }
            $content .= "</StringCollection>";
        }
        if ($mimeType === MgMimeType::Json) {
            $content = MgUtils::Xml2Json($content);
        }
        $this->app->response->header("Content-Type", $mimeType);
        $this->app->response->setBody($content);
    }

    private function OutputException($statusMessage, $errorMessage, $details, $phpTrace, $status = 500, $mimeType = MgMimeType::Html) {
        $errResponse = "";
        if ($mimeType === MgMimeType::Xml) {
            $errResponse = sprintf(
                "<?xml version=\"1.0\"?><Error><Type>%s</Type><Message>%s</Message><Details>%s</Details><StackTrace>%s</StackTrace></Error>",
                MgUtils::EscapeXmlChars($statusMessage),
                MgUtils::EscapeXmlChars($errorMessage),
                MgUtils::EscapeXmlChars($details),
                MgUtils::EscapeXmlChars($phpTrace));
        } else if ($mimeType === MgMimeType::Json) {
            $errResponse = sprintf(
                "{ \"Type\": \"%s\", \"Message\": \"%s\", \"Details\": \"%s\", \"StackTrace\": \"%s\" }",
                MgUtils::EscapeJsonString($statusMessage),
                MgUtils::EscapeJsonString($errorMessage),
                MgUtils::EscapeJsonString($details),
                MgUtils::EscapeJsonString($phpTrace));
        } else {
            $errResponse = sprintf(
                "<html><head><title>%s</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body><h2>%s</h2>%s<h2>Stack Trace</h2><pre>%s</pre></body></html>",
                $statusMessage,
                $errorMessage,
                $details,
                $phpTrace);
        }
        $this->app->halt($status, $errResponse);
    }

    private function OutputError($result, $mimeType = MgMimeType::Html) {
        $statusMessage = $result->GetHttpStatusMessage();
        $e = new Exception();
        if ($statusMessage === "MgAuthenticationFailedException" || $statusMessage === "MgUnauthorizedAccessException") {
            //Send back 401
            $this->app->response->header('WWW-Authenticate', 'Basic realm="MapGuide REST Extension"');
            $this->app->halt(401, "You must enter a valid login ID and password to access this site"); //TODO: Localize
        } else {
            $this->app->response->header("Content-Type", $mimeType);
            //Amend error code for certain classes of errors
            $status = 500;
            if ($statusMessage === "MgResourceNotFoundException" || $statusMessage === "MgResourceDataNotFoundException") {
                $status = 404;
            }
            $this->OutputException($statusMessage, $result->GetErrorMessage(), $result->GetDetailedErrorMessage(), $e->getTraceAsString(), $status, $mimeType);
            /*
            $errResponse = "";
            if ($mimeType === MgMimeType::Xml) {
                $errResponse = sprintf(
                    "<?xml version=\"1.0\"?><Error><Type>%s</Type><Message>%s</Message><Details>%s</Details><StackTrace>%s</StackTrace></Error>",
                    MgUtils::EscapeXmlChars($statusMessage),
                    MgUtils::EscapeXmlChars($result->GetErrorMessage()),
                    MgUtils::EscapeXmlChars($result->GetDetailedErrorMessage()),
                    MgUtils::EscapeXmlChars($e->getTraceAsString()));
            } else if ($mimeType === MgMimeType::Json) {
                $errResponse = sprintf(
                    "{ \"Type\": \"%s\", \"Message\": \"%s\", \"Details\": \"%s\", \"StackTrace\": \"%s\" }",
                    MgUtils::EscapeJsonString($statusMessage),
                    MgUtils::EscapeJsonString($result->GetErrorMessage()),
                    MgUtils::EscapeJsonString($result->GetDetailedErrorMessage()),
                    MgUtils::EscapeJsonString($e->getTraceAsString()));
            } else {
                $errResponse = sprintf(
                    "<html><head><title>%s</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body><h2>%s</h2>%s<h2>Stack Trace</h2><pre>%s</pre></body></html>",
                    $statusMessage,
                    $result->GetErrorMessage(),
                    $result->GetDetailedErrorMessage(),
                    $e->getTraceAsString());
            }
            $this->app->halt($status, $errResponse);
            */
        }
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
                        $this->OutputByteReader($resultObj, $chunkResult);
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
            if ($format != "") {
                $this->OutputError($result, $format);
            } else {
                $this->OutputError($result);
            }
            //throw new Exception("Error executing operation: ".$param->GetParameterValue("OPERATION").". The status code is: $status"); //TODO: Localize
        }
        return $status;
    }

    /**
     * Method: GetRequestParameter
     *
     * Convenience method to get a parameter by name. This method tries to get the named parameter:
     *  1. As-is
     *  2. As upper-case
     *  3. As lower-case
     *
     * In that particular order, if none could be found after these attempts, the defaultValue is returned
     * instead, otherwise the matching parameter value is returned
     *
     * Parameters:
     *
     *   String key          - [String/The parameter name]
     *   String defaultValue - [String/The default value]
     *
     * Returns:
     *
     *   String - the matching parameter value or the default value if no matches can be found
     */
    public function GetRequestParameter($key, $defaultValue = "") {
        $value = $this->app->request->params($key);
        if ($value == null)
            $value = $this->app->request->params(strtoupper($key));
        if ($value == null)
            $value = $this->app->request->params(strtolower($key));
        if ($value == null)
            $value = $defaultValue;

        return $value;
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
                    //Send back 401
                    $this->app->response->header('WWW-Authenticate', 'Basic realm="MapGuide REST Extension"');
                    $this->app->halt(401, "You must enter a valid login ID and password to access this site"); //TODO: Localize
                }
            }
        }
        //All good if we get here. Set up common request parameters so upstream callers don't have to
        $param->AddParameter("LOCALE", $this->app->config("Locale"));
        $param->AddParameter("CLIENTAGENT", "MapGuide REST Extension");
        $param->AddParameter("CLIENTIP", $this->GetClientIp());
        $callback($req, $param);
    }

    protected function GetClientIp() {
        //TODO: Ripped from AJAX viewer. Use the abstractions provided by Slim
        $clientIp = '';
        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)
            && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown') != 0)
        {
            $clientIp = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)
            && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown') != 0)
        {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if (array_key_exists('REMOTE_ADDR', $_SERVER))
        {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }
        return $clientIp;
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
                    //Send back 401
                    $this->app->response->header('WWW-Authenticate', 'Basic realm="MapGuide REST Extension"');
                    $this->app->halt(401, "You must enter username/password");
                }
            }
        }
    }
}

?>