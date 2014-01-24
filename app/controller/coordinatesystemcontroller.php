<?php

require_once "controller.php";

class MgCoordinateSystemController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function EnumerateCategories($format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt) {
            $param->AddParameter("OPERATION", "CS.ENUMERATECATEGORIES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function EnumerateCoordinateSystemsByCategory($category, $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $category) {
            $param->AddParameter("OPERATION", "CS.ENUMERATECOORDINATESYSTEMS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
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
        $wkt = $factory->ConvertEpsgCodeToWkt($wkt);

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