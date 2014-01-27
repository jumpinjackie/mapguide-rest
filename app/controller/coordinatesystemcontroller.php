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

class MgCoordinateSystemController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function EnumerateCategories($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

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
                $param->AddParameter("XSLSTYLESHEET", "CoordinateSystemCategoryList.xsl");
            }
            $that->ExecuteHttpRequest($req);
        });
    }

    public function EnumerateCoordinateSystemsByCategory($category, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json", "html"));

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $category) {
            $param->AddParameter("OPERATION", "CS.ENUMERATECOORDINATESYSTEMS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json") {
                $param->AddParameter("FORMAT", MgMimeType::Json);
            } else if ($fmt === "xml") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            } else if ($fmt === "html") {
                $param->AddParameter("FORMAT", MgMimeType::Xml);
                $param->AddParameter("XSLSTYLESHEET", "CoordinateSystemList.xsl");
            }
            $param->AddParameter("CSCATEGORY", $category);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function ConvertCsCodeToEpsg($cscode) {
        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->CreateFromCode($cscode);

        $this->app->response->setBody($cs->GetEpsgCode()."");
    }

    public function ConvertCsCodeToWkt($cscode) {
        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertCoordinateSystemCodeToWkt($cscode);

        $this->app->response->setBody($wkt);
    }

    public function ConvertEpsgToCsCode($epsg) {
        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertEpsgCodeToWkt($epsg);
        $cs = $factory->Create($wkt);

        $this->app->response->setBody($cs->GetCsCode());
    }

    public function ConvertEpsgToWkt($epsg) {
        $factory = new MgCoordinateSystemFactory();
        $wkt = $factory->ConvertEpsgCodeToWkt($epsg);

        $this->app->response->setBody($wkt);
    }

    public function ConvertWktToCsCode($wkt) {
        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->Create($wkt);

        $this->app->response->setBody($cs->GetCsCode());
    }

    public function ConvertWktToEpsg($wkt) {
        $factory = new MgCoordinateSystemFactory();
        $cs = $factory->Create($wkt);

        $this->app->response->setBody($cs->GetEpsgCode()."");
    }
}

?>