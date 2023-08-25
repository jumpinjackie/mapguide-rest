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
    protected function GetAdditionalParameters(IAppServices $handler, $bSingle, $method) {
        $params = parent::GetAdditionalParameters($handler, $bSingle, $method);
        if ($method == "POST") {
            $pPostBody = new stdClass();
            $pPostBody->in = "body";
            $pPostBody->name = "body";
            $pPostBody->type = "string";
            $pPostBody->required = true;
            $pPostBody->description = $handler->GetLocalizedText("L_REST_POST_BODY_DESC");

            array_push($params, $pPostBody);
        } else if ($method == "PUT") {
            $pPutBody = new stdClass();
            $pPutBody->in = "body";
            $pPutBody->name = "body";
            $pPutBody->type = "string";
            $pPutBody->required = true;
            $pPutBody->description = $handler->GetLocalizedText("L_REST_PUT_BODY_DESC");

            array_push($params, $pPutBody);
        } else if ($method == "DELETE") {
            $pFilter = new stdClass();
            $pFilter->in = "form";
            $pFilter->name = "filter";
            $pFilter->type = "string";
            $pFilter->required = false;
            $pFilter->description = $handler->GetLocalizedText("L_REST_DELETE_FILTER_DESC");

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
    public function TryGetSessionId(IAppServices $handler, $method) {
        if ($method == "POST" || $method == "PUT") {
            $doc = new DOMDocument();
            $doc->loadXML($handler->GetRequestBody());

            //Stash for adapter to grab
            $handler->RegisterDependency("REQUEST_BODY_DOCUMENT", $doc);

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

    public function __construct(IAppServices $handler, MgSiteConnection $siteConn, MgResourceIdentifier $resId, /*php_string*/ $className, array $config, /*php_string*/ $configPath, /*php_string*/ $featureIdProp) {
        parent::__construct($handler, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
        $this->requestDoc = null;
    }

    /**
     * Returns true if the given HTTP method is supported. Overridable.
     */
    public function SupportsMethod(/*php_string*/ $method) {
        return strtoupper($method) === "GET" ||
               strtoupper($method) === "POST" ||
               strtoupper($method) === "PUT" ||
               strtoupper($method) === "DELETE";
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig(array $config) {
        
    }

    protected function GetFileExtension() { return "xml"; }

    public function GetMimeType() { return MgMimeType::Xml; }
    
    /**
     * Writes the GET response header based on content of the given MgReader
     */
    protected function GetResponseBegin(MgReader $reader) {
        $this->agfRw = new MgAgfReaderWriter();
        $this->wktRw = new MgWktReaderWriter();

        $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);

        $schemas = new MgFeatureSchemaCollection();
        $schema = new MgFeatureSchema("TempSchema", "");
        $schemas->Add($schema);
        $classes = $schema->GetClasses();
        $clsDef = $reader->GetClassDefinition();
        $classes->Add($clsDef);

        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><FeatureSet>";
        $classXml = $this->featSvc->SchemaToXml($schemas);
        $classXml = substr($classXml, strpos($classXml, "<xs:schema"));

        $output .= $classXml;
        $output .= "<Features>";

        $this->app->WriteResponseContent($output);
    }

    /**
     * Returns true if the current reader iteration loop should continue, otherwise the loop is broken
     */
    protected function GetResponseShouldContinue(MgReader $reader) {
        return true;
    }

    /**
     * Writes the GET response body based on the current record of the given MgReader. The caller must not advance to the next record
     * in the reader while inside this method
     */
    protected function GetResponseBodyRecord(MgReader $reader) {
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
                        $output .= MgUtils::DateTimeToString($dt);
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

        $this->app->WriteResponseContent($output);
    }

    /**
     * Writes the GET response ending based on content of the given MgReader
     */
    protected function GetResponseEnd(MgReader $reader) {
        $this->app->WriteResponseContent("</Features></FeatureSet>");
    }

    /**
     * Handles POST requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandlePost(/*php_bool*/ $single) {
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $commands = new MgFeatureCommandCollection();
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $rdoc = $this->app->GetDependency("REQUEST_BODY_DOCUMENT");
            if ($rdoc != null)
                $batchProps = MgUtils::ParseMultiFeatureDocument($this->app, $classDef, $rdoc);
            else    
                $batchProps = MgUtils::ParseMultiFeatureXml($this->app, $classDef, $this->app->GetRequestBody());
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
    public function HandlePut(/*php_bool*/ $single) {
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];

            $rdoc = $this->app->GetDependency("REQUEST_BODY_DOCUMENT");
            if ($rdoc == null) {
                $doc = new DOMDocument();
                $doc->loadXML($this->app->GetRequestBody());
            } else {
                $doc = $rdoc;
            }

            $commands = new MgFeatureCommandCollection();

            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $filter = "";
            //If single-record, infer filter from URI
            if ($single === true) {
                $idProps = $classDef->GetIdentityProperties();
                if ($idProps->GetCount() != 1) {
                    $this->app->Halt(400, $this->app->GetLocalizedText("E_CANNOT_APPLY_UPDATE_CANNOT_UNIQUELY_IDENTIFY", $this->featureId, $idProps->GetCount()));
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
            $props = MgUtils::ParseSingleFeatureDocument($this->app, $classDef, $doc, "UpdateProperties");
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
    public function HandleDelete(/*php_bool*/ $single) {
        $trans = null;
        try {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $classDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $schemaName, $className);
            $commands = new MgFeatureCommandCollection();

            if ($single === true) {
                if ($this->featureId == null) {
                    throw new Exception($this->app->GetLocalizedText("E_NO_FEATURE_ID_SET"));
                }
                $idType = MgPropertyType::String;
                $tokens = explode(":", $this->className);
                $clsDef = $this->featSvc->GetClassDefinition($this->featureSourceId, $tokens[0], $tokens[1]);
                if ($this->featureIdProp == null) {
                    $idProps = $clsDef->GetIdentityProperties();
                    if ($idProps->GetCount() == 0) {
                        throw new Exception($this->app->GetLocalizedText("E_CANNOT_DELETE_NO_ID_PROPS", $this->className, $this->featureSourceId->ToString()));
                    } else if ($idProps->GetCount() > 1) {
                        throw new Exception($this->app->GetLocalizedText("E_CANNOT_DELETE_MULTIPLE_ID_PROPS", $this->className, $this->featureSourceId->ToString()));
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
                            throw new Exception($this->app->GetLocalizedText("E_ID_PROP_NOT_DATA", $this->featureIdProp));
                    } else {
                        throw new Exception($this->app->GetLocalizedText("E_ID_PROP_NOT_FOUND", $this->featureIdProp));
                    }
                }
                if ($idType == MgPropertyType::String)
                    $filter = $this->featureIdProp." = '".$this->featureId."'";
                else
                    $filter = $this->featureIdProp." = ".$this->featureId;
            } else {
                $filter = $this->app->GetRequestParameter("filter");
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