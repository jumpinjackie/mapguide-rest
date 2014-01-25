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
     * Initializes the adapater with the given REST configuration
     */
    protected function InitAdapterConfig($config) {
        if (array_key_exists("MaxCount", $config))
            $this->limit = intval($config["MaxCount"]);
    }

    /**
     * Queries the configured feature source and returns a MgReader based on the current GET query parameters and adapter configuration
     */
    protected function CreateQueryOptions() {
        $query = new MgFeatureQueryOptions();
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
}

?>