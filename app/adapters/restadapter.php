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

    protected function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp = null) {
        parent::__construct($app);
        $this->configPath = $configPath;
        $this->featureId = null;
        $this->featureIdProp = $featureIdProp;
        $this->featureSourceId = $resId;
        $this->siteConn = $siteConn;
        $this->featSvc = $this->siteConn->CreateService(MgServiceType::FeatureService);
        $this->className = $className;
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
        if ($single === true) {
            if ($this->featureId == null) {
                throw new Exception("No feature ID set"); //TODO: Localize
            }
            $idType = MgPropertyType::String;
            $tokens = explode(":", $this->className);
            $clsDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $tokens[0], $tokens[1]);
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

    /**
     * Handles GET requests for this adapter. Overridden.
     */
    public function HandleGet($single) {
        $reader = $this->CreateReader($single);
        $this->GetResponseBegin($reader);
        while ($reader->ReadNext()) {
            if (!$this->GetResponseShouldContinue($reader))
                break;
            $this->GetResponseBodyRecord($reader);
        }
        $this->GetResponseEnd($reader);
        $reader->Close();
    }
}

?>