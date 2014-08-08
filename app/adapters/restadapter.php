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
        $tokens = explode(":", $this->className);
        $clsDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $tokens[0], $tokens[1]);
        if ($single === true) {
            if ($this->featureId == null) {
                throw new Exception("No feature ID set"); //TODO: Localize
            }
            $idType = MgPropertyType::String;
            if ($this->featureIdProp == null) {
                $idProps = $clsDef->GetIdentityProperties();
                if ($idProps->GetCount() == 0) {
                    throw new Exception(sprintf("Cannot query (%s) in %s by ID. Class has no identity properties", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
                } else if ($idProps->GetCount() > 1) {
                    throw new Exception(sprintf("Cannot query (%s) in %s by ID. Class has more than one identity property", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
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
                        throw new Exception("Specified identity property ".$this->featureIdProp." is not a data property");
                } else {
                    throw new Exception("Specified identity property ".$this->featureIdProp." not found in class definition");
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
        if (count($this->propertyList) > 0) {
            foreach($this->propertyList as $propName) {
                $query->AddFeatureProperty($propName);
            }
        }
        if (count($this->computedPropertyList) > 0) {
            foreach ($this->computedPropertyList as $alias => $expression) {
                $query->AddComputedProperty($alias, $expression);
            }
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

    public function HandleMethod($method, $single = false) {
        if (!$this->SupportsMethod($method)) {
            $this->app->halt(405, "Method not supported: ".$method); //TODO: Localize
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
                    $this->app->halt(405, "Method not supported: ".$method); //TODO: Localize
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
    public function DocumentOperation($method, $extension, $bSingle);
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

    private function DescribeMethodSummary($method, $extension) {
        return "Returns data as $extension";
    }

    private function GetMethodNickname($method, $extension) {
        $str = ucwords(strtolower($method)." Features $extension");
        return implode("", explode(" ", $str));
    }

    protected function GetAdditionalParameters($bSingle, $method) {
        return array();
    }

    public function DocumentOperation($method, $extension, $bSingle) {
        $op = new stdClass();
        $op->method = $method;
        $op->summary = $this->DescribeMethodSummary($method, $extension);
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
                $pFilter->description = "The url-encoded FDO filter string";
                
                //bbox
                $pbbox = new stdClass();
                $pbbox->paramType = "query";
                $pbbox->name = "bbox";
                $pbbox->type = "string";
                $pbbox->required = false;
                $pbbox->description = "A quartet of x1,y1,x2,y2";

                array_push($op->parameters, $pFilter);
                array_push($op->parameters, $pbbox);
            }
        }
        $extraParams = $this->GetAdditionalParameters($bSingle, $method);
        foreach ($extraParams as $p) {
            array_push($op->parameters, $p);
        }
        
        return $op;
    }
}

abstract class MgFeatureRestAdapterDocumentor extends MgRestAdapterDocumentor {
    protected function GetAdditionalParameters($bSingle, $method) {
        $params = parent::GetAdditionalParameters($bSingle, $method);
        if (!$bSingle) {
            if ($method == "GET") {
                //page
                $pPage = new stdClass();
                $pPage->paramType = "query";
                $pPage->name = "page";
                $pPage->type = "integer";
                $pPage->required = false;
                $pPage->description = "The page number to switch to. Only applies if pagination is configured for the data source";

                array_push($params, $pPage);
            }
        }
        return $params;
    }
}

?>