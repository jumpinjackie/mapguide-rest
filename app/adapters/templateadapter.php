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

class MgTemplateRestAdapterDocumentor extends MgFeatureRestAdapterDocumentor {
    
}

/**
 * A set of lazy-loaded geometry and datetime formatters
 */
class MgFormatterSet
{
    private $app;
    private $formatters;

    public function __construct($app) {
        $this->app = $app;
        $this->formatters = array();
    }
    
    public function GetFormatter($formatterName) {
        if (!array_key_exists($formatterName, $this->formatters)) {
            if (!$this->app->container->has($formatterName)) {
                throw new Exception($this->GetLocalizedText("E_UNKNOWN_FORMATTER", $formatterName));
            }
            $this->formatters[$formatterName] = $this->app->container->$formatterName;
        }
        return $this->formatters[$formatterName];
    }
}

/**
 * Internal use only
 */
class MgFeatureModelReaderFacade
{
    private $data;
    private $meta;
    private $bRead;
    
    private $ordNameMap;
    private $nameOrdMap;
    
    public function __construct($data, $meta) {
        $this->data = $data;
        $this->meta = $meta;
        
        $this->ordNameMap = array();
        $this->nameOrdMap = array();
        $bRead = false;
        if (count($this->data) != count($this->meta)) {
            throw new Exception("data <-> meta count mismatch");
        }
        $i = 0;
        foreach ($this->meta as $key => $value) {
            $this->nameOrdMap[$key] = $i;
            $this->ordNameMap[$i] = $key;
            $i++;
        }
    }
    
    public function ReadNext() {
        if (!$bRead) {
            $bRead = true;
        }
        return $bRead;
    }
    
    public function Close() {}
    
    public function IsNull($indexOrName) {
        return !array_key_exists($indexOrName, $this->data);
    }
    
    public function GetPropertyCount() {
        return count($this->meta);
    }
    
    public function GetPropertyName($index) {
        return $ordNameMap[$index];
    }
    
    public function GetPropertyIndex($name) {
        return $nameOrdMap[$name];
    }
    
    public function GetPropertyType($indexOrName) {
        return $this->meta[$indexOrName];
    }
    
    public function GetBoolean($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
        
        return MgUtils::StringToBool($this->data[$key]);
    }
    
    public function GetDouble($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
        
        return doubleval($this->data[$key]);
    }
    
    public function GetInt16($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
            
        return intval($this->data[$key]);
    }
    
    public function GetInt32($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
            
        return intval($this->data[$key]);
    }
    
    public function GetInt64($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
            
        return intval($this->data[$key]);
    }
    
    public function GetSingle($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
            
        return floatval($this->data[$key]);
    }
    
    public function GetString($indexOrName) {
        $key = $indexOrName;
        if (is_int($key))
            $key = $this->GetPropertyName($key);
            
        return $this->data[$key];
    }
}

/**
 * Template model for "single" result templates
 */
class MgFeatureModel
{
    private $reader;
    private $data;
    private $meta;
    private $formatters;
    private $transform;

    public function __construct($formatters, $reader, $transform = null) {
        $this->reader = $reader;
        $this->data = array();
        $this->meta = array();
        $this->formatters = $formatters;
        $this->transform = $transform;
    }
    
    /*
    public function Dump() {
        $this->Prefill();
        var_dump($this->data);
        die;
    }
    */

    public function DateTimeAsType($name, $formatterName) {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = array();
        }
        if (!array_key_exists($formatterName, $this->data[$name])) {
            $fmt = $this->formatters->GetFormatter($formatterName);
            if ($fmt == null)
                throw new Exception($this->GetLocalizedText("E_UNKNOWN_DATETIME_FORMATTER", $formatterName));
            $this->data[$name][$formatterName] = $fmt->Output($this->reader, $name);
        }
        return $this->data[$name][$formatterName];
    }

    public function GeometryAsType($name, $formatterName) {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = array();
        }
        if (!array_key_exists($formatterName, $this->data[$name])) {
            $fmt = $this->formatters->GetFormatter($formatterName);
            if ($fmt == null)
                throw new Exception($this->GetLocalizedText("E_UNKNOWN_GEOMETRY_FORMATTER", $formatterName));
            $this->data[$name][$formatterName] = $fmt->Output($this->reader, $name, $this->transform);
        }
        return $this->data[$name][$formatterName];
    }
    
    /**
     * Returns a MgFeatureReader-like facade for this feature
     */
    public function ReaderFacade() {
        $this->Prefill();
        return new MgFeatureModelReaderFacade($this->data, $this->meta);
    }
    
    /**
     * Reads all feature property values in advance, ensuring all such values are cached up-front
     */
    public function Prefill() {
        for ($i = 0; $i < $this->reader->GetPropertyCount(); $i++) {
            $name = $this->reader->GetPropertyName($i);
            $this->GetValue($name);
        }
    }
    
    private function GetValue($name) {
        if (array_key_exists($name, $this->data)) {
            if (array_key_exists($name, $this->meta)) {
                $pt = $this->meta[$name];
                switch ($pt) {
                    case MgPropertyType::Geometry:
                    {
                        if (array_key_exists("GeomWKT", $this->data[$name]))
                            return $this->data[$name]["GeomWKT"];
                        else
                            return "";
                    }
                    case MgPropertyType::DateTime:
                    {
                        if (array_key_exists("DateDefault", $this->data[$name]))
                            return $this->data[$name]["DateDefault"];
                        else
                            return "";
                    }
                }
            }
            return $this->data[$name];
        }

        $idx = $this->reader->GetPropertyIndex($name);
        if ($idx >= 0) {
            $ptype = $this->reader->GetPropertyType($idx);
            $this->meta[$name] = $ptype;
            if (!$this->reader->IsNull($idx)) {
                switch ($ptype) {
                    case MgPropertyType::Boolean:
                        $this->data[$name] = $this->reader->GetBoolean($idx)."";
                        break;
                    case MgPropertyType::Byte:
                        $this->data[$name] = $this->reader->GetByte($idx)."";
                        break;
                    case MgPropertyType::DateTime:
                        {
                            $this->DateTimeAsType($name, "DateDefault");
                        }
                        break;
                    case MgPropertyType::Decimal:
                    case MgPropertyType::Double:
                        $this->data[$name] = $this->reader->GetDouble($idx)."";
                        break;
                    case MgPropertyType::Geometry:
                        {
                            $this->GeometryAsType($name, "GeomWKT");
                        }
                        break;
                    case MgPropertyType::Int16:
                        $this->data[$name] = $this->reader->GetInt16($idx)."";
                        break;
                    case MgPropertyType::Int32:
                        $this->data[$name] = $this->reader->GetInt32($idx)."";
                        break;
                    case MgPropertyType::Int64:
                        $this->data[$name] = $this->reader->GetInt64($idx)."";
                        break;
                    case MgPropertyType::Single:
                        $this->data[$name] = $this->reader->GetSingle($idx)."";
                        break;
                    case MgPropertyType::String:
                        $this->data[$name] = $this->reader->GetString($idx);
                        break;
                }
            } else {
                if ($ptype == MgPropertyType::Geometry) {
                    $this->data[$name] = array();
                } else if ($ptype == MgPropertyType::DateTime) {
                    $this->data[$name] = array();
                } else {
                    $this->data[$name] = "";
                }
            }
            if ($ptype == MgPropertyType::Geometry) {
                if (array_key_exists("GeomWKT", $this->data[$name]))
                    return $this->data[$name]["GeomWKT"];
                else
                    return "";
            } else if ($ptype == MgPropertyType::DateTime) {
                if (array_key_exists("DateDefault", $this->data[$name]))
                    return $this->data[$name]["DateDefault"];
                else
                    return "";
            } else {
                return $this->data[$name];
            }
        } else {
            return "";
        }
    }

    public function __get($name) {
        return $this->GetValue($name);
    }
}

/** 
 * A feature model where any property access returns an empty string
 */
class MgNullFeatureModel
{
    public function GeometryAsType($name, $formatterName) {
        return "";
    }

    public function __get($name) {
        return "";
    }
}

/** 
 * An iterator model that is empty
 */
class MgNullFeatureReaderModel
{
    public function Next() { return false; }

    public function Current() { return new MgNullFeatureModel(); }

    public function Done() { }
}

/**
 * A collection of related feature iterators
 */
class MgRelatedFeaturesSet
{
    private $relations;

    public function __construct() {
        $this->relations = array();
    }

    public function Add($relName, $relModel) {
        $this->relations[$relName] = $relModel;
    }

    public function GetRelation($relName) {
        if (array_key_exists($relName, $this->relations)) {
            return $this->relations[$relName];
        } else {
            return new MgNullFeatureReaderModel();
        }
    }

    public function Cleanup() {
        foreach ($this->relations as $relName => $relModel) {
            $relModel->Done();
        }
    }
}

/** 
 * Template model for "many" result templates
 */
class MgFeatureReaderModel
{
    private $reader;
    private $current;
    private $read;
    private $limit;
    private $formatters;
    private $transform;

    private $peeked;

    public function __construct($formatters, $reader, $limit, $read, $transform = null) {
        $this->current = null;
        $this->peeked = array();
        $this->reader = $reader;
        $this->read = $read;
        $this->limit = $limit;
        $this->formatters = $formatters;
        $this->transform = $transform;
    }
    
    /**
     * Advances the internal reader and caches the peeked record 
     */
    public function Peek() {
        $this->current = null;
        $result = $this->reader->ReadNext();
        $this->read++;
        if ($result) {
            $feat = new MgFeatureModel($this->formatters, $this->reader, $this->transform);
            $feat->Prefill();
            array_push($this->peeked, $feat);
            $this->current = $feat;
        }
        $bWithinLimit = !($this->limit > 0 && $this->read > $this->limit);
        if (!$bWithinLimit)
            return false;
        return $result;
    }

    /**
     * Advances the internal reader to the next record. If a peek operation was made previously
     * the internal reader is not advanced. Instead the first peeked record is set to the current one
     */
    public function Next() {
        $this->current = null;
        if (count($this->peeked) == 0) { //No peeking, proceed as normal
            $result = $this->reader->ReadNext();
            $this->read++;
            $bWithinLimit = !($this->limit > 0 && $this->read > $this->limit);
            if (!$bWithinLimit)
                return false;
            return $result;
        } else { //A peek operation was made, set that to the current record and clear the peeked record
            $this->current = $this->peeked[0];
            $this->peeked = array_slice($this->peeked, 1);
            return true;
        }
    }

    /**
     * Returns the current record. If a peek operation was made, it returns the peeked record
     */
    public function Current() {
        if ($this->current == null)
            $this->current = new MgFeatureModel($this->formatters, $this->reader, $this->transform);
        return $this->current;
    }

    public function Done() {
        $this->reader->Close();
    }
}

class MgTemplateHelper
{
    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function GetAssetPath($relPath) {
        $thisUrl = ((!array_key_exists("HTTPS", $_SERVER) || ($_SERVER['HTTPS'] === "off")) ? "http://" : "https://") . $_SERVER['HTTP_HOST'] . $_SERVER["SCRIPT_NAME"];
        return MgUtils::RelativeToAbsoluteUrl($thisUrl, "assets/$relPath");
    }

    public function EscapeForXml($str) {
        return MgUtils::EscapeXmlChars($str);
    }

    public function EscapeForJson($str) {
        return MgUtils::EscapeJsonString($str);
    }
}

class MgTemplateRestAdapter extends MgRestAdapter
{
    private $singleViewPath;
    private $manyViewPath;
    private $noneViewPath;
    private $errorViewPath;
    private $mimeType;

    private $relations;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        $this->relations = array();
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
    }

    public function GetMimeType() {
        return $this->mimeType;
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (!array_key_exists("Templates", $config))
            throw new Exception($this->GetLocalizedText("E_MISSING_REQUIRED_ADAPTER_PROPERTY", "Templates"));

        if (!array_key_exists("MimeType", $config))
            throw new Exception($this->GetLocalizedText("E_MISSING_REQUIRED_ADAPTER_PROPERTY", "MimeType"));

        $this->mimeType = $config["MimeType"];

        $tplConfig = $config["Templates"];
        if (!array_key_exists("Single", $tplConfig))
            throw new Exception($this->GetLocalizedText("E_TEMPLATE_MISSING_DEFINITION", "Single"));
        if (!array_key_exists("Many", $tplConfig))
            throw new Exception($this->GetLocalizedText("E_TEMPLATE_MISSING_DEFINITION", "Many"));
        if (!array_key_exists("None", $tplConfig))
            throw new Exception($this->GetLocalizedText("E_TEMPLATE_MISSING_DEFINITION", "None"));
        if (!array_key_exists("Error", $tplConfig))
            throw new Exception($this->GetLocalizedText("E_TEMPLATE_MISSING_DEFINITION", "Error"));

        $this->singleViewPath   = "file:".$this->configPath."/".$tplConfig["Single"];
        $this->manyViewPath     = "file:".$this->configPath."/".$tplConfig["Many"];
        $this->noneViewPath     = "file:".$this->configPath."/".$tplConfig["None"];
        $this->errorViewPath    = "file:".$this->configPath."/".$tplConfig["Error"];

        if (array_key_exists("Relations", $config)) {
            $cfgRelations = $config["Relations"];
            foreach ($cfgRelations as $relName => $relCfg) {
                //Make our lives easier, don't put spaces in relation names
                if (strpos($relName, ' ') !== FALSE)
                    throw new Exception($this->GetLocalizedText("E_RELATION_CANNOT_HAVE_SPACES", $relName));
                if (!array_key_exists("Source", $relCfg)) {
                    throw new Exception($this->GetLocalizedText("E_RELATION_MISSING_PROPERTY", $relName, "Source"));
                }
                if (!array_key_exists("KeyMap", $relCfg)) {
                    throw new Exception($this->GetLocalizedText("E_RELATION_MISSING_PROPERTY", $relName, "KeyMap"));
                }
                $cfgSource = $relCfg["Source"];
                $cfgKeyMap = $relCfg["KeyMap"];
                if (!array_key_exists("FeatureSource", $cfgSource)) {
                    throw new Exception($this->GetLocalizedText("E_RELATION_MISSING_SOURCE_PROPERTY", $relName, "FeatureSource"));
                }
                if (!array_key_exists("FeatureClass", $cfgSource)) {
                    throw new Exception($this->GetLocalizedText("E_RELATION_MISSING_SOURCE_PROPERTY", $relName, "FeatureSource"));
                }
                $rel = new stdClass();
                $rel->FeatureSource = new MgResourceIdentifier($cfgSource["FeatureSource"]);
                $rel->FeatureClass = $cfgSource["FeatureClass"];
                $rel->KeyMap = array();
                foreach ($cfgKeyMap as $sourceProp => $targetProp) {
                    $rel->KeyMap[$sourceProp] = $targetProp;
                }
                $this->relations[$relName] = $rel;
            }
        }
    }

    private static function GetPropertyValue($reader, $propName) {
        $type = $reader->GetPropertyType($propName);
        //NOTE: Only querying the subset that are possible candidates for identity properties
        switch ($type) {
            case MgPropertyType::Boolean:
                return $reader->GetBoolean($propName);
            case MgPropertyType::Decimal:
            case MgPropertyType::Double:
                return $reader->GetDouble($propName);
            case MgPropertyType::Int16:
                return $reader->GetInt16($propName);
            case MgPropertyType::Int32:
                return $reader->GetInt32($propName);
            case MgPropertyType::Int64:
                return $reader->GetInt64($propName);
            case MgPropertyType::Single:
                return $reader->GetSingle($propName);
            case MgPropertyType::String:
                return $reader->GetString($propName);
            default:
                return "";
        }
    }

    protected function WriteOutput($output) {
        $this->SetResponseHeader("Content-Type", $this->GetMimeType());

        //Apply download headers
        $downloadFlag = $this->GetRequestParameter("download");
        if ($downloadFlag && ($downloadFlag === "1" || $downloadFlag === "true")) {
            $name = $this->GetRequestParameter("downloadname");
            if (!$name) {
                $name = "download";
            }
            $this->SetResponseHeader("Content-Disposition", "attachment; filename=".MgUtils::GetFileNameFromMimeType($name, $this->GetMimeType()));
        }

        $this->WriteResponseContent($output);
    }

    private function LoadRelatedFeatures($reader, $related) {
        //Set up queries for any relations that are defined
        foreach ($this->relations as $relName => $rel) {
            $relFilterParts = array();
            $bQuery = false;
            //At least one source property must have a value before we continue because finding related
            //records where sourceID is null in target is kind of pointless
            foreach ($rel->KeyMap as $sourceProp => $targetProp) {
                if (!$reader->IsNull($sourceProp)) {
                    $value = self::GetPropertyValue($reader, $sourceProp);
                    if ($value !== "") {
                        $bQuery = true;
                        if ($reader->GetPropertyType($sourceProp) == MgPropertyType::String)
                            array_push($relFilterParts, "\"$targetProp\" = '$value'");
                        else
                            array_push($relFilterParts, "\"$targetProp\" = $value");
                    } else {
                        if ($reader->GetPropertyType($sourceProp) == MgPropertyType::String)
                            array_push($relFilterParts, "\"$targetProp\" = ''");
                        else
                            array_push($relFilterParts, "\"$targetProp\" NULL");
                    }
                } else {
                    array_push($relFilterParts, "\"$targetProp\" NULL");
                }
            }
            if ($bQuery === false) {
                continue;
            }
            //Fire off related query and stash in map for template
            $relQuery = new MgFeatureQueryOptions();
            $relFilter = implode(" AND ", $relFilterParts);
            $relQuery->SetFilter($relFilter);
            try {
                $relReader = $this->featSvc->SelectFeatures($rel->FeatureSource, $rel->FeatureClass, $relQuery);
                $related->Add($relName, new MgFeatureReaderModel(new MgFormatterSet($this->app), $relReader, -1, 0, null));
            } catch (MgException $ex) {
                throw new Exception($this->GetLocalizedText("E_QUERY_SETUP", $relFilter, $ex->GetDetails()));
            }
        }
    }

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {
        $reader = null;
        $related = new MgRelatedFeaturesSet();
        $smarty = new Smarty();
        $smarty->setCompileDir($this->GetConfig("Cache.RootDir")."/templates_c");
        //$smarty->setCaching(false);
        try {
            $output = "";
            $query = $this->CreateQueryOptions($single);
            $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
            if ($single === true) {
                //Have to advance the read to initialize the record
                if ($reader->ReadNext()) {
                    $this->LoadRelatedFeatures($reader, $related);
                    $smarty->assign("model", new MgFeatureModel(new MgFormatterSet($this->app), $reader, $this->transform));
                    $smarty->assign("related", $related);
                    $smarty->assign("helper", new MgTemplateHelper($this->app));
                    $output = $smarty->fetch($this->singleViewPath);
                } else {
                    $this->SetResponseStatus(404);
                    $smarty->assign("single", $single);
                    $smarty->assign("ID", $this->featureId);
                    $smarty->assign("helper", new MgTemplateHelper($this->app));
                    $output = $smarty->fetch($this->noneViewPath);
                }
            } else {
                $start = -1;
                $end = -1;
                $read = 0;
                $limit = $this->limit;

                $pageNo = $this->GetRequestParameter("page");
                if ($pageNo == null)
                    $pageNo = 1;
                else
                    $pageNo = intval($pageNo);

                $bNoResults = false;
                $firstRecord = null;
                $bEndOfReader = false;
                if ($this->pageSize > 0) {
                    if ($pageNo > 1) {
                        $skipThisMany = (($pageNo - 1) * $this->pageSize) - 1;
                        //echo "skip this many: $skipThisMany<br/>";
                        $bEndOfReader = true;
                        while ($reader->ReadNext()) {
                            if ($read == $skipThisMany) {
                                $bEndOfReader = false;
                                $limit = min(($skipThisMany + $this->pageSize), $this->limit - 1);
                                break;
                            }
                            $read++;
                        }
                        $model = new MgFeatureReaderModel(new MgFormatterSet($this->app), $reader, $limit, $read, $this->transform);
                    } else { //first page, set limit to page size
                        $limit = $this->pageSize;
                        
                        $model = new MgFeatureReaderModel(new MgFormatterSet($this->app), $reader, $limit, $read, $this->transform);
                        //See if this only has one result. If so, then re-route to the single view
                        if ($pageNo == 1) {
                            //Peek() lets us advance the reader for previewing purposes without
                            //compromising the behaviour of Next()
                            if ($model->Peek()) {
                                $firstRecord = $model->Current();
                                //There's actually more, so this isn't a single result
                                if ($model->Peek()) {
                                    $firstRecord = null;
                                }
                            } else {
                                $bNoResults = true;
                            }
                        }
                    }
                }
                
                if ($firstRecord != null) {
                    $this->LoadRelatedFeatures($firstRecord->ReaderFacade(), $related);
                    $smarty->assign("model", $firstRecord);
                    $smarty->assign("related", $related);
                    $smarty->assign("helper", new MgTemplateHelper($this->app));
                    $output = $smarty->fetch($this->singleViewPath);
                } else {
                    if ($bNoResults) { //Query produced 0 results
                        $this->SetResponseStatus(404);
                        $smarty->assign("single", $single);
                        $smarty->assign("helper", new MgTemplateHelper($this->app));
                        $output = $smarty->fetch($this->noneViewPath);
                    } else {
                        //echo "read: $read, limit: $limit, pageSize: ".$this->pageSize." result limit: ".$this->limit;
                        //die;
                        $smarty->assign("model", $model);
                        $smarty->assign("currentPage", $pageNo);
                        $smarty->assign("endOfReader", $bEndOfReader ? "true" : "false");
                        if ($this->limit > 0) {
                            if ($bEndOfReader) {
                                $smarty->assign("maxPages", $pageNo);
                            } else {
                                $smarty->assign("maxPages", ceil($this->limit / $this->pageSize));
                            }
                        } else {
                            if ($bEndOfReader) {
                                $smarty->assign("maxPages", $pageNo);
                            } else {
                                if ($this->pageSize > 0)
                                    $smarty->assign("maxPages", -1);
                                else
                                    $smarty->assign("maxPages", 1);
                            }
                        }
                        $smarty->assign("helper", new MgTemplateHelper($this->app));
                        $output = $smarty->fetch($this->manyViewPath);
                    }
                }
            }
            $this->WriteOutput($output);
        } catch (MgException $ex) {
            $err = new stdClass();
            $err->code = get_class($ex);
            $err->message = $ex->GetExceptionMessage();
            $err->stack = sprintf("%s\n======== Native <-> PHP boundary ========\n\n%s", $ex->GetStackTrace(), $ex->getTraceAsString());
            $smarty->assign("error", $err);
            $smarty->assign("helper", new MgTemplateHelper($this->app));
            $this->WriteResponseContent($smarty->fetch($this->errorViewPath));
        } catch (Exception $e) {
            $err = new stdClass();
            $err->code = get_class($e);
            $err->message = $e->getMessage();
            $err->stack = $e->getTraceAsString();
            $smarty->assign("error", $err);
            $smarty->assign("helper", new MgTemplateHelper($this->app));
            $this->WriteResponseContent($smarty->fetch($this->errorViewPath));
        }
        $related->Cleanup();
        if ($reader != null)
            $reader->Close();
    }

    /**
     * Returns the documentor for this adapter
     */
    public static function GetDocumentor() {
        return new MgTemplateRestAdapterDocumentor();
    }
}