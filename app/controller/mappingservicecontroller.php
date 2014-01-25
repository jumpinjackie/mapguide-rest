<?php

require_once "controller.php";

class MgMappingServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function GenerateLegendImage($resId, $scale, $geomtype, $themecat, $format) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $scale, $geomtype, $themecat, $format) {
            $param->AddParameter("OPERATION", "GETLEGENDIMAGE");
            $param->AddParameter("VERSION", "1.0.0");
            $param->AddParameter("LAYERDEFINITION", $resIdStr);
            $param->AddParameter("SCALE", $scale);
            $param->AddParameter("TYPE", $geomtype);
            $param->AddParameter("FORMAT", strtoupper($format));
            $param->AddParameter("THEMECATEGORY", $themecat);
            $that->ExecuteHttpRequest($req);
        });
    }
}

?>