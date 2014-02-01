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
require_once dirname(__FILE__)."/../util/geojsonwriter.php";
require_once dirname(__FILE__)."/../util/utils.php";

class MgGeoJsonRestAdapter extends MgFeatureRestAdapter {
    private $agfRw;
    private $transform;

    private $limit;
    private $read;
    private $firstFeature;

    public function __construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp) {
        $this->transform = null;
        $this->limit = -1;
        $this->read = 0;
        parent::__construct($app, $siteConn, $resId, $className, $config, $configPath, $featureIdProp);
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
    }

    /**
     * Writes the GET response header based on content of the given MgReader
     */
    protected function GetResponseBegin($reader) {
        $this->agfRw = new MgAgfReaderWriter();

        $this->app->response->header("Content-Type", MgMimeType::Json);
        $this->app->response->write('{ "type": "FeatureCollection", "features": ['."\n");
        $this->firstFeature = true;
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
        $output = "";
        if (!$this->firstFeature) {
            $output .= ",";
        }
        $propVals = array();
        $geomJson = "";
        $propCount = $reader->GetPropertyCount();
        for ($i = 0; $i < $propCount; $i++) {
            $name = $reader->GetPropertyName($i);
            $propType = $reader->GetPropertyType($i);

            if (!$reader->IsNull($i)) {
                switch($propType) {
                    case MgPropertyType::Boolean:
                        array_push($propVals, '"'.$name.'": '.$reader->GetBoolean($i));
                        break;
                    case MgPropertyType::Byte:
                        array_push($propVals, '"'.$name.'": '.$reader->GetByte($i));
                        break;
                    case MgPropertyType::DateTime:
                        $dt = $reader->GetDateTime($i);
                        array_push($propVals, '"'.$name.'": "'.$dt->ToString().'"');
                        break;
                    case MgPropertyType::Decimal:
                    case MgPropertyType::Double:
                        array_push($propVals, '"'.$name.'": '.$reader->GetDouble($i));
                        break;
                    case MgPropertyType::Geometry:
                        {
                            try {
                                $agf = $reader->GetGeometry($i);
                                $geom = ($this->transform != null) ? $this->agfRw->Read($agf, $this->transform) : $this->agfRw->Read($agf);
                                $geomJson = MgGeoJsonWriter::ToGeoJson($geom);
                            } catch (MgException $ex) {
                                $geomJson = '"geometry": null';
                            }
                        }
                        break;
                    case MgPropertyType::Int16:
                        array_push($propVals, '"'.$name.'": '.$reader->GetInt16($i));
                        break;
                    case MgPropertyType::Int32:
                        array_push($propVals, '"'.$name.'": '.$reader->GetInt32($i));
                        break;
                    case MgPropertyType::Int64:
                        array_push($propVals, '"'.$name.'": '.$reader->GetInt64($i));
                        break;
                    case MgPropertyType::Single:
                        array_push($propVals, '"'.$name.'": '.$reader->GetSingle($i));
                        break;
                    case MgPropertyType::String:
                        array_push($propVals, '"'.$name.'": "'.MgUtils::EscapeJsonString($reader->GetString($i)).'"');
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

        $this->firstFeature = false;
    }

    /**
     * Writes the GET response ending based on content of the given MgReader
     */
    protected function GetResponseEnd($reader) {
        $this->app->response->write("]}");
    }
}

?>