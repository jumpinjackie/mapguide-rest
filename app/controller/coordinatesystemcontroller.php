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

require_once dirname(__FILE__)."/../util/boxedvalue.php";
require_once "controller.php";

class MgCoordinateSystemController extends MgBaseController {
    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    public function GetBaseLibrary(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $mimeType) {
            $param->AddParameter("OPERATION", "CS.GETBASELIBRARY");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("FORMAT", $mimeType); //Not used internally, but needed to return exceptions in the proper format
            if ($fmt == "json") {
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function ValidateWkt(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        $sessionId = $this->app->GetRequestParameter("session");
        $wkt = $this->app->GetRequestParameter("wkt");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $wkt, $fmt, $mimeType) {
            $param->AddParameter("OPERATION", "CS.ISVALID");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("CSWKT", $wkt);
            $param->AddParameter("FORMAT", $mimeType); //Not used internally, but needed to return exceptions in the proper format
            if ($fmt == "json") {
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function WktToEpsg(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        $sessionId = $this->app->GetRequestParameter("session");
        $wkt = $this->app->GetRequestParameter("wkt");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $wkt, $fmt, $mimeType) {
            $param->AddParameter("OPERATION", "CS.CONVERTWKTTOEPSGCODE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("CSWKT", $wkt);
            $param->AddParameter("FORMAT", $mimeType); //Not used internally, but needed to return exceptions in the proper format
            if ($fmt == "json") {
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function WktToMentor(/*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $mimeType = $this->GetMimeTypeForFormat($format);
        $sessionId = $this->app->GetRequestParameter("session");
        $wkt = $this->app->GetRequestParameter("wkt");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $wkt, $fmt, $mimeType) {
            $param->AddParameter("OPERATION", "CS.CONVERTWKTTOCOORDINATESYSTEMCODE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("CSWKT", $wkt);
            $param->AddParameter("FORMAT", $mimeType); //Not used internally, but needed to return exceptions in the proper format
            if ($fmt == "json") {
                $param->AddParameter("X-FORCE-JSON-CONVERSION", "true");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId);
    }

    public function EnumerateCategories(/*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "CS.ENUMERATECATEGORIES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "CoordinateSystemCategoryList.xsl");
            }
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $this->GetMimeTypeForFormat($format));
    }

    public function EnumerateCoordinateSystemsByCategory(/*php_string*/ $category, /*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));
        $sessionId = $this->app->GetRequestParameter("session");

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $category) {
            $param->AddParameter("OPERATION", "CS.ENUMERATECOORDINATESYSTEMS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                //HACK: This API doesn't put XML prolog
                $param->AddParameter("X-PREPEND-XML-PROLOG", "true");
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("X-OVERRIDE-CONTENT-TYPE", MgMimeType::Html);
                $param->AddParameter("XSLSTYLESHEET", "CoordinateSystemList.xsl");
            }
            $param->AddParameter("CSCATEGORY", $category);
            $that->ExecuteHttpRequest($req);
        }, false, "", $sessionId, $this->GetMimeTypeForFormat($format));
    }

    public function ConvertCsCodeToEpsg(/*php_string*/ $cscode, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->CreateFromCode($cscode);

        $body = MgBoxedValue::Int32($cs->GetEpsgCode(), $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function ConvertCsCodeToWkt(/*php_string*/ $cscode, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertCoordinateSystemCodeToWkt($cscode);

        $body = MgBoxedValue::String($wkt, $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function ConvertEpsgToCsCode(/*php_int*/ $epsg, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertEpsgCodeToWkt($epsg);
        $cs = $factory->Create($wkt);

        $body = MgBoxedValue::String($cs->GetCsCode(), $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function ConvertEpsgToWkt(/*php_int*/ $epsg, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertEpsgCodeToWkt($epsg);

        $body = MgBoxedValue::String($wkt, $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function ConvertWktToCsCode(/*php_string*/ $wkt, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->Create($wkt);

        $body = MgBoxedValue::String($cs->GetCsCode(), $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function ConvertWktToEpsg(/*php_string*/ $wkt, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->Create($wkt);

        $body = MgBoxedValue::Int32($cs->GetEpsgCode(), $fmt);
        if ($fmt == "xml") {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        } else {
            $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        }
        $this->app->SetResponseBody($body);
    }

    public function TransformCoordinates() {
        $source = $this->app->GetRequestParameter("from");
        $target = $this->app->GetRequestParameter("to");
        $coordList = $this->app->GetRequestParameter("coords");
        $format = $this->app->GetRequestParameter("format");
        if ($format == null)
            $format = "xml";
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        if ($source == null)
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "from"), $this->GetMimeTypeForFormat($format));
        if ($target == null)
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "to"), $this->GetMimeTypeForFormat($format));
        if ($coordList == null)
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "coords"), $this->GetMimeTypeForFormat($format));

        try {
            $factory = new MgCoordinateSystemFactory();
            $sourceCs = $factory->CreateFromCode($source);
            $targetCs = $factory->CreateFromCode($target);

            $trans = $factory->GetTransform($sourceCs, $targetCs);
            $coords = explode(",",$coordList);

            $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><CoordinateCollection>";
            foreach ($coords as $coordPair) {
                $tokens = explode(" ", trim($coordPair));
                $tokenCount = count($tokens);
                if ($tokenCount === 2) {
                    $txCoord = $trans->Transform(floatval($tokens[0]), floatval($tokens[1]));
                    $output .= "<Coordinate><X>".$txCoord->GetX()."</X><Y>".$txCoord->GetY()."</Y></Coordinate>";
                } else {
                    //TODO: We should accept a partial response, but there's currently no way an empty <Coordinate/> tag survives the
                    //XML to JSON conversion, so we have to throw lest we return an inconsisten partial result
                    $this->ServerError($this->app->GetLocalizedText("E_INVALID_COORDINATE_PAIR", $coordPair, $tokenCount), $this->GetMimeTypeForFormat($format));
                }
            }
            $output .= "</CoordinateCollection>";

            if ($fmt === "json") {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
                $json = MgUtils::Xml2Json($output);
                $this->app->WriteResponseContent($json);
            } else {
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
                $this->app->WriteResponseContent($output);
            }
        }
        catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }
}