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

abstract class MgResponseHandler
{
    protected $app;

    protected function __construct($app) {
        $this->app = $app;
    }

    protected function CollectXslParameters($param) {
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

    protected function OutputUpdateFeaturesResult($commands, $result, $classDef) {
        $output = "<UpdateFeaturesResult>";
        $ccount = $commands->GetCount();
        $rcount = $result->GetCount();
        //Should be equal, but just in case ...
        $count = min($ccount, $rcount);
        for ($i = 0; $i < $count; $i++) {
            $cmd = $commands->GetItem($i);
            $cmdType = $cmd->GetCommandType();
            $prop = $result->GetItem($i);
            switch($cmdType) {
                case MgFeatureCommandType::InsertFeatures:
                    {
                        $output .= "<InsertResult>";
                        if ($prop->GetPropertyType() == MgPropertyType::String) {
                            $output .= "<Error>".$prop->GetValue()."</Error>";
                        } else if ($prop->GetPropertyType() == MgPropertyType::Feature) {
                            $output .= "<FeatureSet><Features>";
                            $reader = $prop->GetValue();
                            $idProps = $classDef->GetIdentityProperties();
                            $propCount = $idProps->GetCount();
                            while ($reader->ReadNext()) {
                                $output .= "<Feature>";
                                //HACK: There is a bug that prevents us from inferring the structure of the MgFeatureReader 
                                //that's put into the UpdateFeatures result, so we workaround this by using the already fetched
                                //MgClassDefinition to extract the relevant identity property values
                                for ($i = 0; $i < $propCount; $i++) {
                                    $idProp = $idProps->GetItem($i);
                                    $name = $idProp->GetName();
                                    $propType = $idProp->GetDataType();
                                    $output .= "<Property><Name>$name</Name>";
                                    if (!$reader->IsNull($i)) {
                                        $output .= "<Value>";
                                        switch($propType) {
                                            case MgPropertyType::Boolean:
                                                $output .= $reader->GetBoolean($name);
                                                break;
                                            case MgPropertyType::Byte:
                                                $output .= $reader->GetByte($name);
                                                break;
                                            case MgPropertyType::Decimal:
                                            case MgPropertyType::Double:
                                                $output .= $reader->GetDouble($name);
                                                break;
                                            case MgPropertyType::Int16:
                                                $output .= $reader->GetInt16($name);
                                                break;
                                            case MgPropertyType::Int32:
                                                $output .= $reader->GetInt32($name);
                                                break;
                                            case MgPropertyType::Int64:
                                                $output .= $reader->GetInt64($name);
                                                break;
                                            case MgPropertyType::Single:
                                                $output .= $reader->GetSingle($name);
                                                break;
                                            case MgPropertyType::String:
                                                $output .= MgUtils::EscapeXmlChars($reader->GetString($name));
                                                break;
                                        }
                                        $output .= "</Value>";
                                    }
                                    $output .= "</Property>";
                                }
                                $output .= "</Feature>";
                            }
                            $reader->Close();
                            $output .= "</Features></FeatureSet>";
                        }
                        $output .= "</InsertResult>";
                    }
                    break;
                case MgFeatureCommandType::UpdateFeatures:
                    {
                        $output .= "<UpdateResult>";
                        if ($prop->GetPropertyType() == MgPropertyType::String) {
                            $output .= "<Error>".$prop->GetValue()."</Error>";
                        } else if ($prop->GetPropertyType() == MgPropertyType::Int32) {
                            $output .= "<ResultsAffected>".$prop->GetValue()."</ResultsAffected>";
                        }
                        $output .= "</UpdateResult>";
                    }
                    break;
                case MgFeatureCommandType::DeleteFeatures:
                    {
                        $output .= "<DeleteResult>";
                        if ($prop->GetPropertyType() == MgPropertyType::String) {
                            $output .= "<Error>".$prop->GetValue()."</Error>";
                        } else if ($prop->GetPropertyType() == MgPropertyType::Int32) {
                            $output .= "<ResultsAffected>".$prop->GetValue()."</ResultsAffected>";
                        }
                        $output .= "</DeleteResult>";
                    }
                    break;
            }
        }
        $output .= "</UpdateFeaturesResult>";
        $this->app->response->header("Content-Type", MgMimeType::Xml);
        $this->app->response->write($output);
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
        //$e = new Exception();
        //$this->app->halt(400, "Unsupported representation: ".$format."<pre>".$e->getTraceAsString()."</pre>"); //TODO: Localize
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

    protected function OnException($ex, $mimeType = MgMimeType::Html) {
        $status = 500;
        if ($ex instanceof MgAuthenticationFailedException || $ex instanceof MgUnauthorizedAccessException) {
            $status = 401;
        } else if ($ex instanceof MgResourceNotFoundException || $ex instanceof MgResourceDataNotFoundException) {
            $status = 404;
        }
        $e = new Exception();
        $this->app->response->header("Content-Type", $mimeType);
        $this->OutputException(get_class($ex), $ex->GetExceptionMessage(), $ex->GetDetails(), $e->getTraceAsString(), $status, $mimeType);
    }

    protected function OutputException($statusMessage, $errorMessage, $details, $phpTrace, $status = 500, $mimeType = MgMimeType::Html) {
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

    protected function Unauthorized() {
        //Send back 401
        //HACK: But don't put the WWW-Authenticate header so the test harness doesn't trip up
        $fromTestHarness = $this->app->request->headers->get("x-mapguide-test-harness");
        if ($fromTestHarness == null || strtoupper($fromTestHarness) !== "TRUE")
            $this->app->response->header('WWW-Authenticate', 'Basic realm="MapGuide REST Extension"');
        $this->app->halt(401, "You must enter a valid login ID and password to access this site"); //TODO: Localize
        //$e = new Exception();
        //$this->app->halt(401, "You must enter a valid login ID and password to access this site<br/>".$e->getTraceAsString()); //TODO: Localize
    }
}

?>