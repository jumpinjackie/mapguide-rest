<?php

require_once "geojsonwriter.php";
require_once "utils.php";

class MgReaderChunkedResult
{
    private $featSvc;
    private $app;
    private $reader;
    private $limit;
    private $transform;

    public function __construct($app, $featSvc, $reader, $limit) {
        $this->app = $app;
        $this->featSvc = $featSvc;
        $this->reader = $reader;
        $this->limit = $limit;
        $this->transform = null;
    }

    public function SetTransform($tx) {
        $this->transform = $tx;
    }

    private function OutputGeoJson($schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();

        $this->app->response->header("Content-Type", MgMimeType::Json);

        $output = '{ "type": "FeatureCollection", "features": ['."\n";

        $propCount = $this->reader->GetPropertyCount();
        $firstFeature = true;
        while ($this->reader->ReadNext()) {
            $read++;
            if ($this->limit > 0 && $read > $this->limit) {
                break;
            }
            if (!$firstFeature) {
                $output .= ",";
            }
            $propVals = array();
            $geomJson = "";
            for ($i = 0; $i < $propCount; $i++) {
                $name = $this->reader->GetPropertyName($i);
                $propType = $this->reader->GetPropertyType($i);

                if (!$this->reader->IsNull($i)) {
                    switch($propType) {
                        case MgPropertyType::Boolean:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetBoolean($i));
                            break;
                        case MgPropertyType::Byte:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetByte($i));
                            break;
                        case MgPropertyType::DateTime:
                            $dt = $this->reader->GetDateTime($i);
                            array_push($propVals, '"'.$name.'": "'.$dt->ToString().'"');
                            break;
                        case MgPropertyType::Decimal:
                        case MgPropertyType::Double:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetDouble($i));
                            break;
                        case MgPropertyType::Geometry:
                            {
                                try {
                                    $agf = $this->reader->GetGeometry($i);
                                    $geom = ($this->transform != null) ? $agfRw->Read($agf, $this->transform) : $agfRw->Read($agf);
                                    $geomJson = MgGeoJsonWriter::ToGeoJson($geom);
                                } catch (MgException $ex) {
                                    $geomJson = '"geometry": null';
                                }
                            }
                            break;
                        case MgPropertyType::Int16:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetInt16($i));
                            break;
                        case MgPropertyType::Int32:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetInt32($i));
                            break;
                        case MgPropertyType::Int64:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetInt64($i));
                            break;
                        case MgPropertyType::Single:
                            array_push($propVals, '"'.$name.'": '.$this->reader->GetSingle($i));
                            break;
                        case MgPropertyType::String:
                            array_push($propVals, '"'.$name.'": "'.MgUtils::EscapeJsonString($this->reader->GetString($i)).'"');
                            break;
                    }
                } else {
                    array_push($propVals, '"'.$name.'": null');
                }
            }
            if ($geomJson !== "") {
                $output .= '{ "type": "Feature", '.$geomJson.', "properties": {'.implode(",", $propVals)."} }\n";
            } else {
                $output .= '{ "type": "Feature", "properties": {'.implode(",", $propVals)."} }\n";;
            }

            $this->app->response->write($output);
            $output = "";

            $firstFeature = false;
        }

        $output .= "]}";
        $this->app->response->write($output);
        $this->reader->Close();
    }

    private function OutputXml($schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $wktRw = new MgWktReaderWriter();

        $this->app->response->header("Content-Type", MgMimeType::Xml);

        $output = "<FeatureSet>";
        $classXml = $this->featSvc->SchemaToXml($schemas);
        $classXml = substr($classXml, strpos($classXml, "<xs:schema"));

        $output .= $classXml;
        $output .= "<Features>";

        $this->app->response->write($output);
        $output = "";

        $propCount = $this->reader->GetPropertyCount();
        while ($this->reader->ReadNext()) {
            $read++;
            if ($this->limit > 0 && $read > $this->limit) {
                break;
            }

            $output = "<Feature>";
            for ($i = 0; $i < $propCount; $i++) {
                $name = $this->reader->GetPropertyName($i);
                $propType = $this->reader->GetPropertyType($i);
                
                $output .= "<Property><Name>$name</Name>";
                if (!$this->reader->IsNull($i)) {
                    $output .= "<Value>";
                    switch($propType) {
                        case MgPropertyType::Boolean:
                            $output .= $this->reader->GetBoolean($i);
                            break;
                        case MgPropertyType::Byte:
                            $output .= $this->reader->GetByte($i);
                            break;
                        case MgPropertyType::DateTime:
                            $dt = $this->reader->GetDateTime($i);
                            $output .= $dt->ToString();
                            break;
                        case MgPropertyType::Decimal:
                        case MgPropertyType::Double:
                            $output .= $this->reader->GetDouble($i);
                            break;
                        case MgPropertyType::Geometry:
                            {
                                try {
                                    $agf = $this->reader->GetGeometry($i);
                                    $geom = ($this->transform != null) ? $agfRw->Read($agf, $this->transform) : $agfRw->Read($agf);
                                    $output .= $wktRw->Write($geom);
                                } catch (MgException $ex) {

                                }
                            }
                            break;
                        case MgPropertyType::Int16:
                            $output .= $this->reader->GetInt16($i);
                            break;
                        case MgPropertyType::Int32:
                            $output .= $this->reader->GetInt32($i);
                            break;
                        case MgPropertyType::Int64:
                            $output .= $this->reader->GetInt64($i);
                            break;
                        case MgPropertyType::Single:
                            $output .= $this->reader->GetSingle($i);
                            break;
                        case MgPropertyType::String:
                            $output .= MgUtils::EscapeXmlChars($this->reader->GetString($i));
                            break;
                    }
                    $output .= "</Value>";
                }
                $output .= "</Property>";
                
            }

            $output .= "</Feature>";

            $this->app->response->write($output);
            $output = "";
        }

        $output .= "</Features></FeatureSet>";
        $this->app->response->write($output);
        $this->reader->Close();
    }

    public function Output($format = "xml") {
        $schemas = new MgFeatureSchemaCollection();
        $schema = new MgFeatureSchema("TempSchema", "");
        $schemas->Add($schema);
        $classes = $schema->GetClasses();
        $clsDef = $this->reader->GetClassDefinition();
        $classes->Add($clsDef);
        
        if ($format === "geojson") {
            $this->OutputGeoJson($schemas);
        } else {
            $this->OutputXml($schemas);
        }
    }
}

?>