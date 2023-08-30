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
require_once dirname(__FILE__)."/../util/readerchunkedresult.php";
require_once dirname(__FILE__)."/interfaces.php";
require_once dirname(__FILE__)."/bytereaderstreamadapter.php";
require_once dirname(__FILE__)."/stringcontentadapter.php";
require_once dirname(__FILE__)."/aggregatecontentadapter.php";

abstract class MgResponseHandler
{
    protected $app;

    protected function __construct(IAppServices $app) {
        $this->app = $app;
    }

    protected function CollectXslParameters(MgHttpRequestParam $param) {
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

    public function ExecuteHttpRequest(MgHttpRequest $req, /*php_callable*/ $ovHandler = NULL) {
        $param = $req->GetRequestParam();
        $origMimeType = $param->GetParameterValue("FORMAT");
        try {
            //If JSON format specified, replace it with XML response and use our
            //own XML to JSON converter. This is to allow for "cleaner" JSON output
            //that only our converter can do
            if ($origMimeType == MgMimeType::Json)
            {
                $param->SetParameterValue("FORMAT", MgMimeType::Xml);
                if (!$param->ContainsParameter("X-FORCE-JSON-CONVERSION"))
                    $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
                else
                    $param->SetParameterValue("X-FORCE-JSON-CONVERSION", "true");
            }

            $response = $req->Execute();
            $result = $response->GetResult();
            $status = $result->GetStatusCode();

            //If there's a custom handler passed in, it takes responsibility
            if ($ovHandler != null && is_callable($ovHandler))
            {
                call_user_func_array($ovHandler, array($result, $status));
                return $status;
            }
            else
            {
                $bDownload = false;
                if ($param->GetParameterValue("X-DOWNLOAD-ATTACHMENT") === "true") {
                    $bDownload = true;
                }

                if ($status == 200) {
                    $resultObj = $result->GetResultObject();
                    if ($resultObj != null) {
                        $mimeType = $result->GetResultContentType();
                        $this->app->SetResponseHeader("Content-Type", $mimeType);
                        //Set download response headers if specified
                        if ($bDownload === true) {
                            $filebasename = "download";
                            if ($param->ContainsParameter("X-DOWNLOAD-ATTACHMENT-NAME")) {
                                $filebasename = $param->GetParameterValue("X-DOWNLOAD-ATTACHMENT-NAME");
                            }
                            $this->app->SetResponseHeader("Content-Disposition", "attachment; filename=".MgUtils::GetFileNameFromMimeType($filebasename, $mimeType));
                        }
                        if ($resultObj instanceof MgByteReader) {
                            if ($param->GetParameterValue("X-FORCE-JSON-CONVERSION") === "true") {
                                if ($result->GetResultContentType() === MgMimeType::Xml && $param->ContainsParameter("XSLSTYLESHEET")) {
                                    $body = MgUtils::XslTransformByteReader($this->app, $resultObj, $param->GetParameterValue("XSLSTYLESHEET"), $this->CollectXslParameters($param));
                                    $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
                                    $this->app->SetResponseBody(MgUtils::Xml2Json($body));
                                } else {
                                    $this->OutputXmlByteReaderAsJson($resultObj);
                                }
                            } else {
                                if ($result->GetResultContentType() === MgMimeType::Xml && $param->ContainsParameter("XSLSTYLESHEET")) {
                                    if ($param->ContainsParameter("X-OVERRIDE-CONTENT-TYPE"))
                                        $this->app->SetResponseHeader("Content-Type", $param->GetParameterValue("X-OVERRIDE-CONTENT-TYPE"));
                                    $this->app->SetResponseBody(MgUtils::XslTransformByteReader($this->app, $resultObj, $param->GetParameterValue("XSLSTYLESHEET"), $this->CollectXslParameters($param)));
                                } else {
                                    $this->OutputByteReader($resultObj, ($param->GetParameterValue("X-CHUNK-RESPONSE") === "true"), ($param->GetParameterValue("X-PREPEND-XML-PROLOG") === "true"));
                                }
                            }
                        } else if ($resultObj instanceof MgStringCollection) {
                            $this->OutputMgStringCollection($resultObj, $param->GetParameterValue("FORMAT"));
                        } else if ($resultObj instanceof MgHttpPrimitiveValue) {
                            $fmt = "xml";
                            if ($param->GetParameterValue("X-FORCE-JSON-CONVERSION") === "true") {
                                $fmt = "json";
                                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
                            } else {
                                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
                            }
                            $body = null;
                            switch ($resultObj->GetType())
                            {
                                case 1:
                                    $body = MgBoxedValue::Boolean($resultObj->GetBoolValue(), $fmt);
                                    break;
                                case 2:
                                    $body = MgBoxedValue::Int32($resultObj->GetIntegerValue(), $fmt);
                                    break;
                                case 3:
                                    $body = MgBoxedValue::String($resultObj->GetStringValue(), $fmt);
                                    break;
                            }
                            if ($body != null) {
                                $this->app->SetResponseBody($body);
                            }
                        } else if (method_exists($resultObj, "ToXml")) {
                            $byteReader = $resultObj->ToXml();
                            if ($param->GetParameterValue("X-FORCE-JSON-CONVERSION") === "true") {
                                $this->OutputXmlByteReaderAsJson($byteReader);
                            } else {
                                $this->OutputByteReader($byteReader, ($param->GetParameterValue("X-CHUNK-RESPONSE") === "true"));
                            }
                        } else {
                            $this->ServerError($this->app->GetLocalizedText("E_DONT_KNOW_HOW_TO_OUTPUT", $resultObj->ToString()));
                        }
                    }
                } else {
                    $format = $origMimeType;
                    if ($param->ContainsParameter("XSLSTYLESHEET"))
                        $format = MgMimeType::Html;
                    else
                        $format = $this->GetMimeTypeForFormat($format);

                    if ($format != "") {
                        $this->OutputError($result, $format);
                    } else {
                        $this->OutputError($result);
                    }
                }
            }
            return $status;
        } catch (MgException $ex) {
            $this->OnException($ex, $origMimeType);
        }
    }

    public function GetMimeTypeForFormat(/*php_string*/ $format) {
        return MgUtils::GetMimeTypeForFormat($format);
    }

    private function OutputError(MgHttpResult $result, /*php_string*/ $mimeType = MgMimeType::Html) {
        $statusMessage = $result->GetHttpStatusMessage();
        $e = new Exception();
        if ($statusMessage === "MgAuthenticationFailedException" || $statusMessage === "MgUnauthorizedAccessException" || $statusMessage == "MgPermissionDeniedException") {
            $this->Unauthorized($mimeType);
        } else {
            $this->app->SetResponseHeader("Content-Type", $mimeType);
            //Amend error code for certain classes of errors
            $status = 500;
            if ($statusMessage === "MgResourceNotFoundException" || $statusMessage === "MgResourceDataNotFoundException") {
                $status = 404;
            } else if ($statusMessage === "MgConnectionFailedException") {
                $status = 503;
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

    public function GetBooleanRequestParameter($name, $defaultValue) {
        $val = strtolower($this->app->GetRequestParameter($name, $defaultValue));
        if ($val == "true")
            $val = "1";
        else if ($val == "false")
            $val = "0";
        return $val;
    }

    protected function GetFileUploadPath(/*php_string*/ $paramName) {
        if (!array_key_exists($paramName, $_FILES))
            return null;
        $err = $_FILES[$paramName]["error"];
        $fileName = null;
        if ($err == 0) {
            $fileName = $_FILES[$paramName]["tmp_name"];
        } else {
            $this->app->SetResponseStatus(500);
            $this->app->SetResponseBody($this->app->GetLocalizedText("E_PHP_FILE_UPLOAD_ERROR", $err));
        }
        return $fileName;
    }

    protected function OutputUpdateFeaturesResult(MgFeatureCommandCollection $commands, MgPropertyCollection $result, MgClassDefinition $classDef, /*php_bool*/ $convertToJson = false) {
        $bHasError = false;
        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><UpdateFeaturesResult>";
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
                            $bHasError = true;
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
                            $bHasError = true;
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
                            $bHasError = true;
                        } else if ($prop->GetPropertyType() == MgPropertyType::Int32) {
                            $output .= "<ResultsAffected>".$prop->GetValue()."</ResultsAffected>";
                        }
                        $output .= "</DeleteResult>";
                    }
                    break;
            }
        }
        $output .= "</UpdateFeaturesResult>";
        if ($bHasError === true) {
            $this->app->SetResponseStatus(500);
        }
        if ($convertToJson) {
            $output = MgUtils::Xml2Json($output);
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
            $this->app->WriteResponseContent($output);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
            $this->app->WriteResponseContent($output);
        }
    }

    protected function OutputXmlByteReaderAsJson(MgByteReader $byteReader) {
        $content = MgUtils::Xml2Json($byteReader->ToString());
        $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        $this->app->WriteResponseContent($content);
    }

    const XML_PROLOG = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

    protected function OutputByteReader(MgByteReader $byteReader, /*php_bool*/ $bChunkResult = false, /*php_bool*/ $bPrependXmlProlog = false) {
        $mimeType = $byteReader->GetMimeType();
        $this->app->SetResponseHeader("Content-Type", $mimeType);
        $bNeedXmlProlog = $mimeType == MgMimeType::Xml && $bPrependXmlProlog;
        if (!$bChunkResult) {
            $rdrLen = $byteReader->GetLength();
            if ($bNeedXmlProlog) {
                $rdrLen += strlen(self::XML_PROLOG);
            }
            $this->app->SetResponseHeader("Content-Length", $rdrLen);
        }
        if ($bNeedXmlProlog) {
            $this->app->SetResponseBody(new AggregateContentAdapter([
                new StringContentAdapter(self::XML_PROLOG),
                new MgByteReaderStreamAdapter($byteReader)
            ]));
        } else {
            $this->app->SetResponseBody(new MgByteReaderStreamAdapter($byteReader));
        }
        /*
        $writer = null;
        if ($bChunkResult)
            $writer = new MgHttpChunkWriter();
        else
            $writer = new MgSlimChunkWriter($this->app);

        $writer->SetHeader("Content-Type", $mimeType);
        if (!$bChunkResult) {
            $rdrLen = $byteReader->GetLength();
            $writer->SetHeader("Content-Length", $rdrLen);
        }
        $writer->StartChunking();
        if ($mimeType == MgMimeType::Xml && $bPrependXmlProlog) {
            $writer->WriteChunk("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
        }
        do
        {
            $data = str_pad("\0", 50000, "\0");
            $len = $byteReader->Read($data, 50000);
            if ($len > 0)
            {
                $str = substr($data, 0, $len);
                $writer->WriteChunk($str);
            }
        } while ($len > 0);
        $writer->EndChunking();
        */
    }

    protected function ValidateValueInDomain(/*php_string*/ $value, array $allowedValues = null, /*php_string*/ $mimeType = MgMimeType::Html) {
        if ($allowedValues == null) {
            return $value;
        } else {
            $fmt = strtolower($value);
            foreach ($allowedValues as $vr) {
                $rep = strtolower($vr);
                if ($rep === $fmt)
                    return $fmt;
            }
        }
        $msg = $this->app->GetLocalizedText("E_UNRECOGNIZED_VALUE_IN_DOMAIN", $value, implode(", ", $allowedValues));
        $this->BadRequest($msg, $mimeType);
    }

    protected function ValidateRepresentation(/*php_string*/ $format, array $validRepresentations = null) {
        if ($validRepresentations == null) {
            $this->app->requestedMimeType = $this->GetMimeTypeForFormat($format);
            return $format;
        } else {
            $fmt = strtolower($format);
            foreach ($validRepresentations as $vr) {
                $rep = strtolower($vr);
                if ($rep === $fmt) {
                    $this->app->requestedMimeType = $this->GetMimeTypeForFormat($format);
                    return $fmt;
                }
            }
        }
        //Since we dont recognize the representation, we don't exactly know the ideal output format of this error. So default to HTML
        $msg = $this->app->GetLocalizedText("E_UNSUPPORTED_REPRESENTATION", $format);
        $this->BadRequest($msg, MgMimeType::Html);
    }

    protected function OutputMgPropertyCollection(MgPropertyCollection $props, /*php_string*/ $mimeType = MgMimeType::Xml) {
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PropertyCollection />";
        $count = $props->GetCount();
        $agfRw = null;
        $wktRw = null;
        $this->app->SetResponseHeader("Content-Type", $mimeType);
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
                        $value = MgUtils::DateTimeToString($dt);
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
        $this->app->SetResponseHeader("Content-Type", $mimeType);
        $this->app->SetResponseBody($content);
    }

    protected function OutputMgStringCollection(MgStringCollection $strCol, /*php_string*/ $mimeType = MgMimeType::Xml) {
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?><StringCollection />";
        if ($strCol != null) {
            // MgStringCollection::ToXml() doesn't seem to be reliable in PHP (bug?), so do this manually
            $count = $strCol->GetCount();
            $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?><StringCollection>";
            for ($i = 0; $i < $count; $i++) {
                $value = MgUtils::EscapeXmlChars($strCol->GetItem($i));
                $content .= "<Item>$value</Item>";
            }
            $content .= "</StringCollection>";
        }
        if ($mimeType === MgMimeType::Json) {
            $content = MgUtils::Xml2Json($content);
        }
        $this->app->SetResponseHeader("Content-Type", $mimeType);
        $this->app->SetResponseBody($content);
    }

    protected function OnException(MgException $ex, /*php_string*/ $mimeType = MgMimeType::Html) {
        $status = 500;
        if ($ex instanceof MgAuthenticationFailedException || $ex instanceof MgUnauthorizedAccessException || $ex instanceof MgPermissionDeniedException) {
            $status = 401;
        } else if ($ex instanceof MgResourceNotFoundException || $ex instanceof MgResourceDataNotFoundException) {
            $status = 404;
        }
        $this->app->SetResponseHeader("Content-Type", $mimeType);
        $this->OutputException(get_class($ex), $ex->GetExceptionMessage(), $ex->GetDetails(), $ex->getTraceAsString(), $status, $mimeType);
    }

    protected function FormatException(/*php_string*/ $type, /*php_string*/ $errorMessage, /*php_string*/ $details, /*php_string*/ $phpTrace, /*php_int*/ $status = 500, /*php_string*/ $mimeType = MgMimeType::Html) {
        return MgUtils::FormatException($this->app, $type, $errorMessage, $details, $phpTrace, $status, $mimeType);
    }

    protected function OutputException(/*php_string*/ $statusMessage, /*php_string*/ $errorMessage, /*php_string*/ $details, /*php_string*/ $phpTrace, /*php_int*/ $status = 500, /*php_string*/ $mimeType = MgMimeType::Html) {
        $errResponse = $this->FormatException($statusMessage, $errorMessage, $details, $phpTrace, $status, $mimeType);
        $this->app->Halt($status, $errResponse, $mimeType);
    }

    public function BadRequest(/*php_string*/ $msg, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $errResponse = $this->FormatException("BadRequest", $this->app->GetLocalizedText("E_BAD_REQUEST"), $msg, $e->getTraceAsString(), 400, $mimeType);
        $this->app->Halt(400, $errResponse, $mimeType);
    }

    public function MethodNotSupported(/*php_string*/ $method, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $msg = $this->app->GetLocalizedText("E_METHOD_NOT_SUPPORTED_DESC", $method);
        $errResponse = $this->FormatException("MethodNotSupported", $this->app->GetLocalizedText("E_METHOD_NOT_SUPPORTED"), $msg, $e->getTraceAsString(), 405, $mimeType);
        $this->app->Halt(405, $errResponse, $mimeType);
    }

    public function NotFound(/*php_string*/ $msg, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $errResponse = $this->FormatException("NotFound", $this->app->GetLocalizedText("E_NOT_FOUND"), $msg, $e->getTraceAsString(), 404, $mimeType);
        $this->app->Halt(404, $errResponse, $mimeType);
    }

    public function Forbidden(/*php_string*/ $msg, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $errResponse = $this->FormatException("Forbidden", $this->app->GetLocalizedText("E_FORBIDDEN"), $msg, $e->getTraceAsString(), 403, $mimeType);
        $this->app->Halt(403, $errResponse, $mimeType);
    }

    public function ServerError(/*php_string*/ $msg, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $errResponse = $this->FormatException("ServerError", $this->app->GetLocalizedText("E_SERVER_ERROR"), $msg, $e->getTraceAsString(), 500, $mimeType);
        $this->app->Halt(500, $errResponse, $mimeType);
    }

    public function ServiceUnavailable(/*php_string*/ $msg, /*php_string*/ $mimeType = MgMimeType::Html) {
        $e = new Exception();
        $errResponse = $this->FormatException("ServiceUnavailable", $this->app->GetLocalizedText("E_SERVICE_UNAVAILABLE"), $msg, $e->getTraceAsString(), 503, $mimeType);
        $this->app->Halt(503, $errResponse, $mimeType);
    }

    public function Unauthorized(/*php_string*/ $mimeType = MgMimeType::Html) {
        //Send back 401
        //HACK: But don't put the WWW-Authenticate header so the test harness doesn't trip up
        $fromTestHarness = $this->app->GetRequestHeader("x-mapguide-test-harness");
        if ($fromTestHarness == null || strtoupper($fromTestHarness) !== "TRUE")
            $this->app->SetResponseHeader('WWW-Authenticate', 'Basic realm="MapGuide REST Extension"');
        $e = new Exception();
        $title = $this->app->GetLocalizedText("E_UNAUTHORIZED");
        $message = $this->app->GetLocalizedText("E_UNAUTHORIZED_DESC");
        $errResponse = $this->FormatException("Unauthorized", $title, $message, $e->getTraceAsString(), 401, $mimeType);

        $this->app->Halt(401, $errResponse, $mimeType);
    }
}