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

require_once dirname(__FILE__)."/../core/responsehandler.php";

/**
 * The base class of all REST adapters
 */
abstract class MgRestAdapter extends MgResponseHandler
{
    protected $featureSourceId;
    protected $siteConn;
    protected $className;

    protected $featSvc;
    protected $featureId;
    protected $configPath;

    protected $featureIdProp;

    protected $propertyList;
    protected $computedPropertyList;
    protected $pageSize;
    protected $limit;
    protected $transform;
    protected $useTransaction;

    protected function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp = null) {
        parent::__construct($app);
        $this->useTransaction = false;
        $this->limit = -1;
        $this->pageSize = -1;
        $this->transform = null;
        $this->configPath = $configPath;
        $this->featureId = null;
        $this->featureIdProp = $featureIdProp;
        $this->featureSourceId = $resId;
        $this->siteConn = $siteConn;
        $this->featSvc = $this->siteConn->CreateService(MgServiceType::FeatureService);
        $this->className = $className;
        $this->propertyList = array();
        $this->computedPropertyList = array();

        $this->EnsureQualifiedClassName();
        
        if (array_key_exists("Properties", $config)) {
            $cfgProps = $config["Properties"];
            $this->propertyList = $cfgProps;
        }
        if (array_key_exists("ComputedProperties", $config)) {
            $cfgComputedProps = $config["ComputedProperties"];
            $this->computedPropertyList = $cfgComputedProps;
        }
        if (array_key_exists("PageSize", $config)) {
            $this->pageSize = intval($config["PageSize"]);
        }
        if (array_key_exists("MaxCount", $config)) {
            $this->limit = intval($config["MaxCount"]);
        }
        if (array_key_exists("TransformTo", $config)) {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $this->transform = MgUtils::GetTransform($this->featSvc, $this->featureSourceId, $schemaName, $className, $config["TransformTo"]);
        }
        if (array_key_exists("UseTransaction", $config)) {
            $this->useTransaction = ($config["UseTransaction"] === true || $config["UseTransaction"] === "true");
        }

        $this->InitAdapterConfig($config);
    }

    public function SetFeatureId($id) {
        $this->featureId = $id;
    }

    /**
     * Gets the resource ID of the feature source this adapter is configured for
     */
    public function GetFeatureSource() {
        return $this->featureSourceId;
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected abstract function InitAdapterConfig($config);

    /**
     * Queries the configured feature source and returns a MgReader based on the current GET query parameters and adapter configuration
     */
    protected function CreateQueryOptions($single) {
        $query = new MgFeatureQueryOptions();
        $this->EnsureQualifiedClassName();
        $tokens = explode(":", $this->className);
        $clsDef = null;
        $clsDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $tokens[0], $tokens[1]);
        if ($single === true) {
            if ($this->featureId == null) {
                throw new Exception($this->app->localizer->getText("E_NO_FEATURE_ID_SET"));
            }
            $idType = MgPropertyType::String;
            if ($this->featureIdProp == null) {
                $idProps = $clsDef->GetIdentityProperties();
                if ($idProps->GetCount() == 0) {
                    throw new Exception($this->app->localizer->getText("E_CANNOT_QUERY_NO_ID_PROPS", $this->className, $this->featureSourceId->ToString()));
                } else if ($idProps->GetCount() > 1) {
                    throw new Exception($this->app->localizer->getText("E_CANNOT_QUERY_MULTIPLE_ID_PROPS", $this->className, $this->featureSourceId->ToString()));
                } else {
                    $idProp = $idProps->GetItem(0);
                    $this->featureIdProp = $idProp->GetName();
                    $idType = $idProp->GetDataType();
                }
            } else {
                $props = $clsDef->GetProperties();
                $iidx = $props->IndexOf($this->featureIdProp);
                if ($iidx >= 0) {
                    $propDef = $props->GetItem($iidx);
                    if ($propDef->GetPropertyType() != MgFeaturePropertyType::DataProperty)
                        throw new Exception($this->app->localizer->getText("E_ID_PROP_NOT_DATA", $this->featureIdProp));
                } else {
                    throw new Exception($this->app->localizer->getText("E_ID_PROP_NOT_FOUND", $this->featureIdProp));
                }
            }
            if ($idType == MgPropertyType::String)
                $query->SetFilter($this->featureIdProp." = '".$this->featureId."'");
            else
                $query->SetFilter($this->featureIdProp." = ".$this->featureId);
        } else {
            $flt = $this->app->request->get("filter");
            if ($flt != null)
                $query->SetFilter($flt);
            $bbox = $this->app->request->get("bbox");
            if ($bbox != null) {
                $parts = explode(",", $bbox);
                if (count($parts) == 4) {
                    $wktRw = new MgWktReaderWriter();
                    $geom = $wktRw->Read(MgUtils::MakeWktPolygon($parts[0], $parts[1], $parts[2], $parts[3]));
                    $query->SetSpatialFilter($clsDef->GetDefaultGeometryPropertyName(), $geom, MgFeatureSpatialOperations::EnvelopeIntersects);
                }
            }
        }
        if (isset($this->app->ComputedProperties)) {
            $compProps = $this->app->ComputedProperties;
            foreach ($compProps as $alias => $expression) {
                $this->computedPropertyList[$alias] = $expression;
            }
        }
        $bAppliedComputedProperties = false;
        if (count($this->computedPropertyList) > 0) {
            foreach ($this->computedPropertyList as $alias => $expression) {
                $query->AddComputedProperty($alias, $expression);
                $bAppliedComputedProperties = true;
            }
        }
        //If computed properties were applied, add all properties from the class definition if no
        //explicit property list supplied
        if ($bAppliedComputedProperties && count($this->propertyList) == 0) {
            $clsProps = $clsDef->GetProperties();
            for ($i = 0; $i < $clsProps->GetCount(); $i++) {
                $propDef = $clsProps->GetItem($i);
                $query->AddFeatureProperty($propDef->GetName());
            }
        } else {
            if (count($this->propertyList) > 0) {
                foreach($this->propertyList as $propName) {
                    $query->AddFeatureProperty($propName);
                }
            }
        }

        $orderby = $this->app->request->get("orderby");
        $orderOptions = $this->app->request->get("orderoption");
        if ($orderby != null) {
            if ($orderOptions == null)
                $orderOptions = "asc";
            $orderPropNames = explode(",", $orderby); //If you have a comma in your property names, it's your own fault :)
            $orderProps = new MgStringCollection();
            foreach ($orderPropNames as $propName) {
                $orderProps->Add($propName);
            }
            $orderOpt = MgOrderingOption::Ascending;
            if (strtolower($orderOptions) === "desc")
                $orderOpt = MgOrderingOption::Descending;
            $query->SetOrderingFilter($orderProps, $orderOpt);
        }

        return $query;
    }

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {

    }

    /**
     * Handles POST requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePost($single) {

    }

    /**
     * Handles PUT requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePut($single) {

    }

    /**
     * Handles DELETE requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleDelete($single) {

    }

    /**
     * Returns true if the given HTTP method is supported. Overridable.
     */
    public function SupportsMethod($method) {
        return strtoupper($method) === "GET";
    }

    public function GetMimeType() {
        return MgMimeType::Html;   
    }

    private function EnsureQualifiedClassName() {
        $tokens = explode(":", $this->className);
        if (count($tokens) != 2) {
            $schemaNames = $this->featSvc->GetSchemas($this->featureSourceId);
            $schemaName = $schemaNames->GetItem(0);
            $className = $this->className;
            $this->className = "$schemaName:$className";
        }
    }

    public function HandleMethod($method, $single = false) {
        if (!$this->SupportsMethod($method)) {
            $this->MethodNotSupported($method, $this->GetMimeType());
        } else {
            $mth = strtoupper($method);
            switch ($mth) {
                case "GET":
                    $this->HandleGet($single);
                    break;
                case "POST":
                    $this->HandlePost($single);
                    break;
                case "PUT":
                    $this->HandlePut($single);
                    break;
                case "DELETE":
                    $this->HandleDelete($single);
                    break;
                default:
                    $this->MethodNotSupported($method, $this->GetMimeType());
                    break;
            }
        }
    }
}

/**
 * The base class of all feature-based REST adapters. MgFeatureRestAdapter plumbs the feature query aspects
 * allowing for subclasses to handle the MgReader output logic
 */
abstract class MgFeatureRestAdapter extends MgRestAdapter { 
    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
    }

    protected function CreateReader($single) {
        $query = $this->CreateQueryOptions($single);
        $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
        return $reader;
    }

    /**
     * Writes the GET response header based on content of the given MgReader
     */
    protected abstract function GetResponseBegin($reader);

    /**
     * Returns true if the current reader iteration loop should continue, otherwise the loop is broken
     */
    protected abstract function GetResponseShouldContinue($reader);

    /**
     * Writes the GET response body based on the current record of the given MgReader. The caller must not advance to the next record
     * in the reader while inside this method
     */
    protected abstract function GetResponseBodyRecord($reader);

    /**
     * Writes the GET response ending based on content of the given MgReader
     */
    protected abstract function GetResponseEnd($reader);

    protected abstract function GetFileExtension();

    /**
     * Handles GET requests for this adapter. Overridden.
     */
    public function HandleGet($single) {
        $reader = $this->CreateReader($single);
        //Apply download headers
        $downloadFlag = $this->app->request->params("download");
        if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
            $name = $this->app->request->params("downloadname");
            if (!$name) {
                $name = "download";
            }
            $name .= ".";
            $name .= $this->GetFileExtension();
            $this->app->response->header("Content-Disposition", "attachment; filename=".$name);
        }
        $this->GetResponseBegin($reader);

        $start = -1;
        $end = -1;
        $read = 0;

        $pageNo = $this->app->request->get("page");
        if ($pageNo == null)
            $pageNo = 1;
        else
            $pageNo = intval($pageNo);

        if ($this->pageSize > 0) {
            $start = ($this->pageSize * ($pageNo - 1)) + 1;
            $end = ($this->pageSize * $pageNo);
        }
        //echo "PageNo: $pageNo<br/>";
        //echo "PageSize: ".$this->pageSize."<br/>";
        //echo "$start - $end<br/>";
        while ($reader->ReadNext()) {
            $read++;
            if ($this->limit > 0 && $read > $this->limit) {
                //echo "At limit<br/>";
                break;
            }
            if (!$this->GetResponseShouldContinue($reader))
                break;
            
            if ($start >= 0 && $end > $start) {
                if ($read < $start) {
                    //echo "Skip $read<br/>";
                    continue;
                }
                if ($read > $end) {
                    //echo "End $read<br/>";
                    break;
                }
            }
            $this->GetResponseBodyRecord($reader);
            //echo "$read<br/>";
        }
        $this->GetResponseEnd($reader);
        $reader->Close();
        //die;
    }
}

interface IAdapterDocumentor {
    public function DocumentOperation($app, $method, $extension, $bSingle);
}

interface ISessionIDExtractor {
    /**
     * Tries to return the session id based on the given method. This is for methods that could accept a session id in places
     * other than the query string, url path or form parameter. If no session id is found, null is returned.
     */
    public function TryGetSessionId($app, $method);
}

class MgSessionIDExtractor implements ISessionIDExtractor {
    /**
     * Tries to return the session id based on the given method. This is for methods that could accept a session id in places
     * other than the query string, url path or form parameter. If no session id is found, null is returned.
     */
    public function TryGetSessionId($app, $method) {
        return null;
    }
}

abstract class MgRestAdapterDocumentor implements IAdapterDocumentor {

    private function DescribeMethodSummary($app, $method, $extension) {
        return $app->localizer->getText("L_RETURNS_DATA_AS_TYPE", $extension);
    }

    private function GetMethodNickname($method, $extension) {
        $str = ucwords(strtolower($method)." Features $extension");
        return implode("", explode(" ", $str));
    }

    protected function GetAdditionalParameters($app, $bSingle, $method) {
        return array();
    }

    public function DocumentOperation($app, $method, $extension, $bSingle) {
        $op = new stdClass();
        $op->method = $method;
        $op->summary = $this->DescribeMethodSummary($app, $method, $extension);
        $op->nickname = $this->GetMethodNickname($method, $extension);
        $op->parameters = array();
        if ($bSingle) {

        } else {
            if ($method == "GET") {
                //filter
                $pFilter = new stdClass();
                $pFilter->paramType = "query";
                $pFilter->name = "filter";
                $pFilter->type = "string";
                $pFilter->required = false;
                $pFilter->description = $app->localizer->getText("L_REST_GET_FILTER_DESC");
                
                //bbox
                $pbbox = new stdClass();
                $pbbox->paramType = "query";
                $pbbox->name = "bbox";
                $pbbox->type = "string";
                $pbbox->required = false;
                $pbbox->description = $app->localizer->getText("L_REST_GET_BBOX_DESC");

                array_push($op->parameters, $pFilter);
                array_push($op->parameters, $pbbox);
            }
        }
        $extraParams = $this->GetAdditionalParameters($app, $bSingle, $method);
        foreach ($extraParams as $p) {
            array_push($op->parameters, $p);
        }
        
        return $op;
    }
}

abstract class MgFeatureRestAdapterDocumentor extends MgRestAdapterDocumentor {
    protected function GetAdditionalParameters($app, $bSingle, $method) {
        $params = parent::GetAdditionalParameters($app, $bSingle, $method);
        if (!$bSingle) {
            if ($method == "GET") {
                //page
                $pPage = new stdClass();
                $pPage->paramType = "query";
                $pPage->name = "page";
                $pPage->type = "integer";
                $pPage->required = false;
                $pPage->description = $app->localizer->getText("L_REST_PAGE_NO_DESC");

                array_push($params, $pPage);
            }
        }
        return $params;
    }
}

?>