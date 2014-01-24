<?php

require_once "controller.php";
require_once dirname(__FILE__)."/../util/readerchunkedresult.php";

class MgFeatureServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function GetSpatialContexts($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETSPATIALCONTEXTS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("ACTIVEONLY", "0");
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetSchemaNames($resId, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $resIdStr) {
            $param->AddParameter("OPERATION", "GETSCHEMAS");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function DescribeSchema($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr) {
            $param->AddParameter("OPERATION", "DESCRIBEFEATURESCHEMA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetClassNames($resId, $schemaName, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $resIdStr) {
            $param->AddParameter("OPERATION", "GETCLASSES");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function GetClassDefinition($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));

        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $fmt, $schemaName, $className, $resIdStr) {
            $param->AddParameter("OPERATION", "DESCRIBEFEATURESCHEMA");
            $param->AddParameter("VERSION", "1.0.0");
            if ($fmt === "json")
                $param->AddParameter("FORMAT", MgMimeType::Json);
            else
                $param->AddParameter("FORMAT", MgMimeType::Xml);
            $param->AddParameter("RESOURCEID", $resIdStr);
            $param->AddParameter("SCHEMA", $schemaName);
            $param->AddParameter("CLASSNAMES", $className);
            $that->ExecuteHttpRequest($req);
        });
    }

    public function SelectFeatures($resId, $schemaName, $className, $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "geojson"));

        $that = $this;
        $this->EnsureAuthenticationForSite();
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
        $query = new MgFeatureQueryOptions();

        $filter = $this->GetRequestParameter("filter", "");
        $propList = $this->GetRequestParameter("properties", "");
        //$orderby = $this->GetRequestParameter("orderby", "");
        //$orderOptiosn = $this->GetRequestParameter("orderoption", "");
        $spatialFilter = $this->GetRequestParameter("spatialfilter", "");
        $maxFeatures = $this->GetRequestParameter("maxfeatures", "");
        $transformto = $this->GetRequestParameter("transformto", "");

        $finalFilter = "";
        if ($filter !== "") {
            $finalFilter = $filter;
        }
        if ($spatialFilter !== "") {
            if ($finalFilter !== "") {
                $finalFilter .= " AND " . $spatialFilter;
            } else {
                $finalFilter = $spatialFilter;
            }
        }
        if ($finalFilter !== "") {
            $query->SetFilter($finalFilter);
        }
        $limit = -1;
        if ($maxFeatures !== "") {
            $limit = intval($maxFeatures);
        }
        if ($propList !== "") {
            $propNames = explode(",", $propList); //If you have a comma in your property names, it's your own fault :)
            foreach ($propNames as $propName) {
                $query->AddFeatureProperty($propName);
            }
        }
        $transform = null;
        if ($transformto !== "") {
            $factory = new MgCoordinateSystemFactory();
            $targetCs = $factory->CreateFromCode($transformto);
            $clsDef = $featSvc->GetClassDefinition($resId, $schemaName, $className);
            //Has a designated geometry property, use it's spatial context
            if ($clsDef->GetDefaultGeometryPropertyName() !== "") {
                $props = $clsDef->GetProperties();
                $idx = $props->IndexOf($clsDef->GetDefaultGeometryPropertyName());
                if ($idx >= 0) {
                    $geomProp = $props->GetItem($idx);
                    $scName = $geomProp->GetSpatialContextAssociation();
                    $scReader = $featSvc->GetSpatialContexts($resId, false);
                    while ($scReader->ReadNext()) {
                        if ($scReader->GetName() === $scName) {
                            $sourceCs = $factory->Create($scReader->GetCoordinateSystemWkt());
                            $transform = $factory->GetTransform($sourceCs, $targetCs);
                        }
                    }
                    $scReader->Close();
                }
            }
        }

        $reader = $featSvc->SelectFeatures($resId, "$schemaName:$className", $query);
        $result = new MgReaderChunkedResult($this->app, $featSvc, $reader, $limit);
        if ($transform != null)
            $result->SetTransform($transform);
        $result->Output($format);
    }
}

?>