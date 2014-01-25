<?php

/**
 * The base class of all REST adapters
 */
abstract class MgRestAdapter
{
    protected $app;
    protected $featureSourceId;
    protected $siteConn;
    protected $className;

    protected $featSvc;

    protected function __construct($app, $siteConn, $resId, $className, $config) {
        $this->app = $app;
        $this->featureSourceId = $resId;
        $this->siteConn = $siteConn;
        $this->className = $className;
        $this->InitAdapterConfig($config);
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
    public function HandleGet() {

    }

    /**
     * Handles POST requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePost() {

    }

    /**
     * Handles PUT requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePut() {

    }

    /**
     * Handles DELETE requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleDelete() {

    }

    /**
     * Returns true if the given HTTP method is supported. Overridable.
     */
    public function SupportsMethod($method) {
        return strtoupper($method) === "GET";
    }

    public function HandleMethod($method) {
        if (!$this->SupportsMethod($method)) {
            $this->app->halt(405, "Method not supported: ".$method); //TODO: Localize
        } else {
            $mth = strtoupper($method);
            switch ($mth) {
                case "GET":
                    $this->HandleGet();
                    break;
                case "POST":
                    $this->HandlePost();
                    break;
                case "PUT":
                    $this->HandlePut();
                    break;
                case "DELETE":
                    $this->HandleDelete();
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

    protected function CreateReader() {
        $query = $this->CreateQueryOptions();
        $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
        return $reader;
    }

    /**
     * Queries the configured feature source and returns a MgReader based on the current GET query parameters and adapter configuration
     */
    protected abstract function CreateQueryOptions();

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
    public function HandleGet() {
        $reader = $this->CreateReader();
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