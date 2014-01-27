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

require_once "restadapter.php";

class MgFeatureXmlRestAdapter extends MgFeatureRestAdapter {
    private $agfRw;
    private $wktRw;
    private $transform;

    private $limit;
    private $read;

    public function __construct($app, $siteConn, $resId, $className, $config) {
        $this->transform = null;
        $this->limit = -1;
        $this->read = 0;
        parent::__construct($app, $siteConn, $resId, $className, $config);
    }

    /**
     * Returns true if the given HTTP method is supported. Overridable.
     */
    public function SupportsMethod($method) {
        return strtoupper($method) === "GET" ||
               strtoupper($method) === "POST" ||
               strtoupper($method) === "PUT" ||
               strtoupper($method) === "DELETE";
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (array_key_exists("MaxCount", $config))
            $this->limit = intval($config["MaxCount"]);
    }

    /**
     * Queries the configured feature source and returns a MgReader based on the current GET query parameters and adapter configuration
     */
    protected function CreateQueryOptions($single) {
        $query = new MgFeatureQueryOptions();
        if ($single === true) {
            if ($this->featureId == null) {
                throw new Exception("No feature ID set"); //TODO: Localize
            }
            $tokens = explode(":", $this->className);
            $clsDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $tokens[0], $tokens[1]);
            $idProps = $clsDef->GetIdentityProperties();
            if ($idProps->GetCount() == 0) {
                throw new Exception(sprintf("Cannot query (%s) in %s by ID. Class has no identity properties", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
            } else if ($idProps->GetCount() > 1) {
                throw new Exception(sprintf("Cannot query (%s) in %s by ID. Class has more than one identity property", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
            } else {
                $idProp = $idProps->GetItem(0);
                if ($idProp->GetDataType() == MgPropertyType::String) {
                    $query->SetFilter($idProp->GetName()." = '".$this->featureId."'");
                } else {
                    $query->SetFilter($idProp->GetName()." = ".$this->featureId);
                }
            }
        }
        return $query;
    }

    /**
     * Writes the GET response header based on content of the given MgReader
     */
    protected function GetResponseBegin($reader) {
        $this->agfRw = new MgAgfReaderWriter();
        $this->wktRw = new MgWktReaderWriter();

        $this->app->response->header("Content-Type", MgMimeType::Xml);

        $schemas = new MgFeatureSchemaCollection();
        $schema = new MgFeatureSchema("TempSchema", "");
        $schemas->Add($schema);
        $classes = $schema->GetClasses();
        $clsDef = $reader->GetClassDefinition();
        $classes->Add($clsDef);

        $output = "<FeatureSet>";
        $classXml = $this->featSvc->SchemaToXml($schemas);
        $classXml = substr($classXml, strpos($classXml, "<xs:schema"));

        $output .= $classXml;
        $output .= "<Features>";

        $this->app->response->write($output);
    }

    /**
     * Returns true if the current reader iteration loop should continue, otherwise the loop is broken
     */
    protected function GetResponseShouldContinue($reader) {
        $this->read++;
        $result = !($this->limit > 0 && $this->read > $this->limit);
        //$this->app->response->write('<!-- $this->limit == '.$this->limit.' -->');
        //$this->app->response->write('<!-- $this->read == '.$this->read.' -->');
        //$this->app->response->write('<!-- !($this->limit > 0 && $this->read > $this->limit) == '.$result.' -->');
        return $result;
    }

    /**
     * Writes the GET response body based on the current record of the given MgReader. The caller must not advance to the next record
     * in the reader while inside this method
     */
    protected function GetResponseBodyRecord($reader) {
        $output = "<Feature>";
        $propCount = $reader->GetPropertyCount();
        for ($i = 0; $i < $propCount; $i++) {
            $name = $reader->GetPropertyName($i);
            $propType = $reader->GetPropertyType($i);
            
            $output .= "<Property><Name>$name</Name>";
            if (!$reader->IsNull($i)) {
                $output .= "<Value>";
                switch($propType) {
                    case MgPropertyType::Boolean:
                        $output .= $reader->GetBoolean($i);
                        break;
                    case MgPropertyType::Byte:
                        $output .= $reader->GetByte($i);
                        break;
                    case MgPropertyType::DateTime:
                        $dt = $reader->GetDateTime($i);
                        $output .= $dt->ToString();
                        break;
                    case MgPropertyType::Decimal:
                    case MgPropertyType::Double:
                        $output .= $reader->GetDouble($i);
                        break;
                    case MgPropertyType::Geometry:
                        {
                            try {
                                $agf = $reader->GetGeometry($i);
                                $geom = ($this->transform != null) ? $this->agfRw->Read($agf, $this->transform) : $this->agfRw->Read($agf);
                                $output .= $this->wktRw->Write($geom);
                            } catch (MgException $ex) {

                            }
                        }
                        break;
                    case MgPropertyType::Int16:
                        $output .= $reader->GetInt16($i);
                        break;
                    case MgPropertyType::Int32:
                        $output .= $reader->GetInt32($i);
                        break;
                    case MgPropertyType::Int64:
                        $output .= $reader->GetInt64($i);
                        break;
                    case MgPropertyType::Single:
                        $output .= $reader->GetSingle($i);
                        break;
                    case MgPropertyType::String:
                        $output .= MgUtils::EscapeXmlChars($reader->GetString($i));
                        break;
                }
                $output .= "</Value>";
            }
            $output .= "</Property>";
            
        }

        $output .= "</Feature>";

        $this->app->response->write($output);
    }

    /**
     * Writes the GET response ending based on content of the given MgReader
     */
    protected function GetResponseEnd($reader) {
        $this->app->response->write("</Features></FeatureSet>");
    }

    /**
     * Handles POST requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePost($single) {
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $commands = new MgFeatureCommandCollection();
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $batchProps = MgUtils::ParseMultiFeatureXml($classDef, $this->app->request->getBody());
            $insertCmd = new MgInsertFeatures("$schemaName:$className", $batchProps);
            $commands->Add($insertCmd);

            $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    /**
     * Handles PUT requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePut($single) {
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $doc = new DOMDocument();
            $doc->loadXML($this->app->request->getBody());

            $commands = new MgFeatureCommandCollection();

            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $filter = "";
            //If single-record, infer filter from URI
            if ($single === true) {
                $idProps = $classDef->GetIdentityProperties();
                if ($idProps->GetCount() != 1) {
                    $app->halt(400, "Cannot apply update. The value (".$this->featureId.") is not enough to uniquely identify the feature to be updated. Class definition has ".$idProps->GetCount()." identity properties"); //TODO: Localize
                } else {
                    $idProp = $idProps->GetItem(0);
                    if ($idProp->GetDataType() == MgPropertyType::String) {
                        $filter = $idProp->GetName()." = '".$this->featureId."'";
                    } else {
                        $filter = $idProp->GetName()." = ".$this->featureId;
                    }
                }
            } else { //Otherwise, use the filter from the request envelope (if specified)
                $filterNode = $doc->getElementsByTagName("Filter");
                if ($filterNode->length == 1)
                    $filter = $filterNode->item(0)->nodeValue;
            }
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $props = MgUtils::ParseSingleFeatureDocument($classDef, $doc, "UpdateProperties");
            $updateCmd = new MgUpdateFeatures("$schemaName:$className", $props, $filter);
            $commands->Add($updateCmd);

            $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }

    /**
     * Handles DELETE requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleDelete($single) {
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $commands = new MgFeatureCommandCollection();
            $filter = $this->app->request->params("filter");
            if ($filter == null)
                $filter = "";
            $deleteCmd = new MgDeleteFeatures("$schemaName:$className", $filter);
            $commands->Add($deleteCmd);

            $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            $this->OnException($ex);
        }
    }
}

?>