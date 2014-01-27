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

    protected function __construct($app, $siteConn, $resId, $className, $config) {
        parent::__construct($app);
        $this->featureId = null;
        $this->featureSourceId = $resId;
        $this->siteConn = $siteConn;
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
    public function __construct($app, $siteConn, $resId, $className, $config) {
        parent::__construct($app, $siteConn, $resId, $className, $config);
        $this->featSvc = $this->siteConn->CreateService(MgServiceType::FeatureService);
    }

    protected function CreateReader($single) {
        $query = $this->CreateQueryOptions($single);
        $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
        return $reader;
    }

    /**
     * Queries the configured feature source and returns a MgReader based on the current GET query parameters and adapter configuration
     */
    protected abstract function CreateQueryOptions($single);

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