<?php

require_once "restadapter.php";

/**
 * A set of lazy-loaded geometry formatters
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
                throw new Exception("No formatter named ".$formatterName." registered"); //TODO: Localize
            }
            $this->formatters[$formatterName] = $this->app->container->$formatterName;
        }
        return $this->formatters[$formatterName];
    }
}

/**
 * Template model for "single" result templates
 */
class MgFeatureModel
{
    private $reader;
    private $data;
    private $formatters;
    private $transform;

    public function __construct($formatters, $reader, $transform = null) {
        $this->reader = $reader;
        $this->data = array();
        $this->formatters = $formatters;
        $this->transform = $transform;
    }

    public function DateTimeAsType($name, $formatterName) {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = array();
        }
        if (!array_key_exists($formatterName, $this->data[$name])) {
            $fmt = $this->formatters->GetFormatter($formatterName);
            if ($fmt == null)
                throw new Exception("No DateTime Formatter named ".$formatterName." registered"); //TODO: Localize
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
                throw new Exception("No Geometry Formatter named ".$formatterName." registered"); //TODO: Localize
            $this->data[$name][$formatterName] = $fmt->Output($this->reader, $name, $this->transform);
        }
        return $this->data[$name][$formatterName];
    }

    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $idx = $this->reader->GetPropertyIndex($name);
        if ($idx >= 0) {
            $ptype = $this->reader->GetPropertyType($idx);
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
}

class MgNullFeatureModel
{
    public function GeometryAsType($name, $formatterName) {
        return "";
    }

    public function __get($name) {
        return "";
    }
}

class MgNullFeatureReaderModel
{
    public function Next() { return false; }

    public function Current() { return new MgNullFeatureModel(); }

    public function Done() { }
}

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

/** * Template model for "many" result templates
 */
class MgFeatureReaderModel
{
    private $reader;
    private $current;
    private $read;
    private $limit;
    private $formatters;
    private $transform;

    public function __construct($formatters, $reader, $limit, $read, $transform = null) {
        $this->current = null;
        $this->reader = $reader;
        $this->read = $read;
        $this->limit = $limit;
        $this->formatters = $formatters;
        $this->transform = $transform;
    }

    public function Next() {
        $this->current = null;
        $result = $this->reader->ReadNext();
        $this->read++;
        $bWithinLimit = !($this->limit > 0 && $this->read > $this->limit);
        if (!$bWithinLimit)
            return false;
        return $result;
    }

    public function Current() {
        if ($this->current == null)
            $this->current = new MgFeatureModel($this->formatters, $this->reader, $this->transform);
        return $this->current;
    }

    public function Done() {
        $this->reader->Close();
    }
}

class MgTemplateRestAdapter extends MgRestAdapter
{
    private $read;

    private $singleViewPath;
    private $manyViewPath;
    private $noneViewPath;
    private $errorViewPath;
    private $mimeType;

    private $relations;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        $this->read = 0;
        $this->relations = array();
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (!array_key_exists("Templates", $config))
            throw new Exception("Missing required property 'Templates' in adapter configuration"); //TODO: Localize

        if (!array_key_exists("MimeType", $config))
            throw new Exception("Missing required property 'MimeType' in adapter configuration"); //TODO: Localize

        $this->mimeType = $config["MimeType"];

        $tplConfig = $config["Templates"];
        if (!array_key_exists("Single", $tplConfig))
            throw new Exception("Missing definition of 'Single' template"); //TODO: Localize
        if (!array_key_exists("Many", $tplConfig))
            throw new Exception("Missing definition of 'Many' template"); //TODO: Localize
        if (!array_key_exists("None", $tplConfig))
            throw new Exception("Missing definition of 'None' template"); //TODO: Localize
        if (!array_key_exists("Error", $tplConfig))
            throw new Exception("Missing definition of 'Error' template"); //TODO: Localize

        $this->singleViewPath   = "file:".$this->configPath."/".$tplConfig["Single"];
        $this->manyViewPath     = "file:".$this->configPath."/".$tplConfig["Many"];
        $this->noneViewPath     = "file:".$this->configPath."/".$tplConfig["None"];
        $this->errorViewPath    = "file:".$this->configPath."/".$tplConfig["Error"];

        if (array_key_exists("Relations", $config)) {
            $cfgRelations = $config["Relations"];
            foreach ($cfgRelations as $relName => $relCfg) {
                //Make our lives easier, don't put spaces in relation names
                if (strpos($relName, ' ') !== FALSE)
                    throw new Exception("Relation '$relName' cannot have spaces"); //TODO: Localize
                if (!array_key_exists("Source", $relCfg)) {
                    throw new Exception("Configuration for relation $relName is missing 'Source' property"); //TODO: Localize
                }
                if (!array_key_exists("KeyMap", $relCfg)) {
                    throw new Exception("Configuration for relation $relName is missing 'KeyMap' property"); //TODO: Localize   
                }
                $cfgSource = $relCfg["Source"];
                $cfgKeyMap = $relCfg["KeyMap"];
                if (!array_key_exists("FeatureSource", $cfgSource)) {
                    throw new Exception("Source configuration for relation $relName is missing 'FeatureSource' property"); //TODO: Localize
                }
                if (!array_key_exists("FeatureClass", $cfgSource)) {
                    throw new Exception("Source configuration for relation $relName is missing 'FeatureSource' property"); //TODO: Localize
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

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {
        $reader = null;
        $related = new MgRelatedFeaturesSet();
        $smarty = new Smarty();
        //$smarty->setCaching(false);
        try {
            $output = "";
            $query = $this->CreateQueryOptions($single);
            $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
            if ($single === true) {
                //Have to advance the read to initialize the record
                if ($reader->ReadNext()) {
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
                            throw new Exception("Error setting up related query. Filter was: $relFilter, Details:".$ex->GetDetails()); //TODO: Localize
                        }
                    }
                    $smarty->assign("model", new MgFeatureModel(new MgFormatterSet($this->app), $reader, $this->transform));
                    $smarty->assign("related", $related);
                    $output = $smarty->fetch($this->singleViewPath);
                } else {
                    $this->app->response->setStatus(404);
                    $smarty->assign("ID", $this->featureId);
                    $output = $smarty->fetch($this->noneViewPath);
                }
            } else {
                $smarty->assign("model", new MgFeatureReaderModel(new MgFormatterSet($this->app), $reader, $this->limit, $this->read, $this->transform));
                $output = $smarty->fetch($this->manyViewPath);
            }
            $this->app->response->header("Content-Type", $this->mimeType);
            $this->app->response->write($output);
        } catch (MgException $ex) {
            $smarty->assign("error", $ex);
            $this->app->response->write($smarty->fetch($this->errorViewPath));
        }
        $related->Cleanup();
        if ($reader != null)
            $reader->Close();
    }
}

?>