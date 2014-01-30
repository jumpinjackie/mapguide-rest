<?php

require_once "restadapter.php";

/**
 * A set of lazy-loaded geometry formatters
 */
class MgGeometryFormatterSet
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
                throw new Exception("No Geometry Formatter named ".$formatterName." registered"); //TODO: Localize
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
                        $dt = $this->reader->GetDateTime($idx);
                        $this->data[$name] = $dt->ToString();
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
                } else {
                    $this->data[$name] = "";
                }
            }
            if ($ptype == MgPropertyType::Geometry) {
                return $this->data[$name]["GeomWKT"];
            } else {
                return $this->data[$name];
            }
        } else {
            return "";
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
}

class MgTemplateRestAdapter extends MgRestAdapter
{
    private $transform;
    private $limit;
    private $read;

    private $singleViewPath;
    private $manyViewPath;
    private $noneViewPath;
    private $errorViewPath;
    private $mimeType;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath) {
        $this->transform = null;
        $this->limit = -1;
        $this->read = 0;
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath);
    }

    /**
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (array_key_exists("MaxCount", $config))
            $this->limit = intval($config["MaxCount"]);

        if (array_key_exists("TransformTo", $config)) {
            $tokens = explode(":", $this->className);
            $schemaName = $tokens[0];
            $className = $tokens[1];
            $this->transform = MgUtils::GetTransform($this->featSvc, $this->featureSourceId, $schemaName, $className, $config["TransformTo"]);
        }

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
    }

    /**
     * Handles GET requests for this adapter. Overridable. Does nothing if not overridden.
     */
    public function HandleGet($single) {
        $reader = null;
        $smarty = new Smarty();
        //$smarty->setCaching(false);
        try {
            $output = "";
            $query = $this->CreateQueryOptions($single);
            $reader = $this->featSvc->SelectFeatures($this->featureSourceId, $this->className, $query);
            if ($single === true) {
                //Have to advance the read to initialize the record
                if ($reader->ReadNext()) {
                    $smarty->assign("model", new MgFeatureModel(new MgGeometryFormatterSet($this->app), $reader, $this->transform));
                    $output = $smarty->fetch($this->singleViewPath);    
                } else {
                    $this->app->response->setStatus(404);
                    $smarty->assign("ID", $this->featureId);
                    $output = $smarty->fetch($this->noneViewPath);
                }
            } else {
                $smarty->assign("model", new MgFeatureReaderModel(new MgGeometryFormatterSet($this->app), $reader, $this->limit, $this->read, $this->transform));
                $output = $smarty->fetch($this->manyViewPath);
            }
            $this->app->response->header("Content-Type", $this->mimeType);
            $this->app->response->write($output);
        } catch (MgException $ex) {
            $smarty->assign("error", $ex);
            $this->app->response->write($smarty->fetch($this->errorViewPath));
        }
        if ($reader != null)
            $reader->Close();
    }
}

?>