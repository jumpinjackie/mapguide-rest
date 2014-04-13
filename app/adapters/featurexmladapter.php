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
require_once dirname(__FILE__)."/../util/utils.php";

class MgFeatureXmlRestAdapterDocumentor extends MgFeatureRestAdapterDocumentor {
    protected function GetAdditionalParameters($bSingle, $method) {
        $params = parent::GetAdditionalParameters($bSingle, $method);
        if ($method == "POST") {
            $pPostBody = new stdClass();
            $pPostBody->paramType = "body";
            $pPostBody->name = "body";
            $pPostBody->type = "string";
            $pPostBody->required = true;
            $pPostBody->description = "The XML envelope describing the features to be inserted";

            array_push($params, $pPostBody);
        } else if ($method == "PUT") {
            $pPutBody = new stdClass();
            $pPutBody->paramType = "body";
            $pPutBody->name = "body";
            $pPutBody->type = "string";
            $pPutBody->required = true;
            $pPutBody->description = "The XML envelope describing the features to be updated";

            array_push($params, $pPutBody);
        } else if ($method == "DELETE") {
            $pFilter = new stdClass();
            $pFilter->paramType = "form";
            $pFilter->name = "filter";
            $pFilter->type = "string";
            $pFilter->required = false;
            $pFilter->description = "The FDO Filter string that will determine what features are deleted";

            array_push($params, $pFilter);
        }
        return $params;
    }
}

class MgFeatureXmlSessionIDExtractor extends MgSessionIDExtractor {
    /**
     * Tries to return the session id based on the given method. This is for methods that could accept a session id in places
     * other than the query string, url path or form parameter. If no session id is found, null is returned.
     */
    public function TryGetSessionId($app, $method) {
        if ($method == "POST" || $method == "PUT") {
            $doc = new DOMDocument();
            $doc->loadXML($app->request->getBody());

            //Stash for adapter to grab
            $app->REQUEST_BODY_DOCUMENT = $doc;

            $sesNodes = $doc->getElementsByTagName("SessionID");
            if ($sesNodes->length == 1)
                return $sesNodes->item(0)->nodeValue;
        }
        return null;
    }
}

class MgFeatureXmlRestAdapter extends MgFeatureRestAdapter {
    private $agfRw;
    private $wktRw;
    private $requestDoc;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
        $this->requestDoc = null;
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
        return true;
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
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $commands = new MgFeatureCommandCollection();
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            if ($this->app->REQUEST_BODY_DOCUMENT != null)
                $batchProps = MgUtils::ParseMultiFeatureDocument($classDef, $this->app->REQUEST_BODY_DOCUMENT);
            else    
                $batchProps = MgUtils::ParseMultiFeatureXml($classDef, $this->app->request->getBody());
            $insertCmd = new MgInsertFeatures("$schemaName:$className", $batchProps);
            $commands->Add($insertCmd);

            if ($this->useTransaction)
                $trans = $this->featSvc->BeginTransaction($this->featureSourceId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            else
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex);
        }
    }

    /**
     * Handles PUT requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePut($single) {
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];

            if ($this->app->REQUEST_BODY_DOCUMENT == null) {
                $doc = new DOMDocument();
                $doc->loadXML($this->app->request->getBody());
            } else {
                $doc = $this->app->REQUEST_BODY_DOCUMENT;
            }

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

            if ($this->useTransaction)
                $trans = $this->featSvc->BeginTransaction($this->featureSourceId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            else
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex);
        }
    }

    /**
     * Handles DELETE requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleDelete($single) {
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $commands = new MgFeatureCommandCollection();

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
                        throw new Exception(sprintf("Cannot delete (%s) in %s by ID. Class has no identity properties", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
                    } else if ($idProps->GetCount() > 1) {
                        throw new Exception(sprintf("Cannot delete (%s) in %s by ID. Class has more than one identity property", $this->className, $this->featureSourceId->ToString())); //TODO: Localize
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
                    $filter = $this->featureIdProp." = '".$this->featureId."'";
                else
                    $filter = $this->featureIdProp." = ".$this->featureId;
            } else {
                $filter = $this->app->request->params("filter");
                if ($filter == null)
                    $filter = "";
            }
            
            $deleteCmd = new MgDeleteFeatures("$schemaName:$className", $filter);
            $commands->Add($deleteCmd);

            if ($this->useTransaction)
                $trans = $this->featSvc->BeginTransaction($this->featureSourceId);

            //HACK: Due to #2252, we can't call UpdateFeatures() with NULL MgTransaction, so to workaround
            //that we call the original UpdateFeatures() overload with useTransaction = false if we find a
            //NULL MgTransaction
            if ($trans == null)
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, false);
            else
                $result = $this->featSvc->UpdateFeatures($this->featureSourceId, $commands, $trans);
            if ($trans != null)
                $trans->Commit();
            $this->OutputUpdateFeaturesResult($commands, $result, $classDef);
        } catch (MgException $ex) {
            if ($trans != null)
                $trans->Rollback();
            $this->OnException($ex);
        }
    }

    /**
     * Returns the documentor for this adapter
     */
    public static function GetDocumentor() {
        return new MgFeatureXmlRestAdapterDocumentor();
    }
}

?>