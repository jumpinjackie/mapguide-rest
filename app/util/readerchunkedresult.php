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

require_once "geojsonwriter.php";
require_once "utils.php";

abstract class MgChunkWriter
{
    public abstract function SetHeader($name, $value);

    public abstract function WriteChunk($chunk);

    public abstract function StartChunking();

    public abstract function EndChunking();
}

class MgSlimChunkWriter extends MgChunkWriter
{
    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function SetHeader($name, $value) {
        $this->app->response->header($name, $value);
    }

    public function WriteChunk($chunk) {
        $this->app->response->write($chunk);
    }

    public function StartChunking() { }

    public function EndChunking() { }
}

class MgHttpChunkWriter extends MgChunkWriter
{
    private $headers;

    public function __construct() {
        $this->headers = array();
    }

    public function SetHeader($name, $value) {
        $this->headers["$name"] = $value;
    }

    public function WriteChunk($chunk) {
        echo sprintf("%x\r\n", strlen($chunk));
        echo $chunk;
        echo "\r\n";
        flush();
        ob_flush();
    }

    public function StartChunking() {
        //Fix for Apache. Have to turn off compression
        if(function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }

        //Remove PHP time limit
        if(!ini_get('safe_mode')) {
            @set_time_limit(0);
        }

        while (ob_get_level()) {
            ob_end_flush();
        }
        if (ob_get_length() === false) {
            ob_start();
        }
        $this->headers["Transfer-Encoding"] = "chunked";
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        flush();
    }

    public function EndChunking() {
        $this->WriteChunk("");
    }
}

class MgReaderChunkedResult
{
    private $featSvc;
    private $reader;
    private $limit;
    private $transform;
    private $writer;

    public function __construct($featSvc, $reader, $limit, $writer = NULL) {
        $this->featSvc = $featSvc;
        $this->reader = $reader;
        $this->limit = $limit;
        $this->transform = null;
        if ($writer != null)
            $this->writer = $writer;
        else
            $this->writer = new MgHttpChunkWriter();
    }

    public function CheckAndSetDownloadHeaders($app, $format) {
        $downloadFlag = $app->request->params("download");
        if ($downloadFlag === "1" || $downloadFlag === "true") {
            $fn = "download";
            if ($app->request->params("downloadname"))
                $fn = $app->request->params("downloadname");
            $ext = $format;
            if ($format == "geojson")
                $ext = "json";
            $this->writer->SetHeader("Content-Disposition", "attachment; filename=".$fn.".".$ext);
        }
    }

    public function SetTransform($tx) {
        $this->transform = $tx;
    }

    private function OutputGeoJson($schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();

        $this->writer->SetHeader("Content-Type", MgMimeType::Json);
        $this->writer->StartChunking();

        $output = '{ "type": "FeatureCollection", "features": ['."\n";

        $clsDef = $this->reader->GetClassDefinition();
        $clsIdProps = $clsDef->GetIdentityProperties();
        $idProp = NULL;
        if ($clsIdProps->GetCount() == 1) {
            $idProp = $clsIdProps->GetItem(0);
        }
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
            $output .= MgGeoJsonWriter::FeatureToGeoJson($this->reader, $agfRw, $this->transform, ($idProp != NULL ? $idProp->GetName() : NULL));
            $this->writer->WriteChunk($output);
            $output = "";

            $firstFeature = false;
        }

        $output .= "]}";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
        $this->reader->Close();
    }

    private function OutputXml($schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $wktRw = new MgWktReaderWriter();

        $this->writer->SetHeader("Content-Type", MgMimeType::Xml);
        $this->writer->StartChunking();

        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><FeatureSet>";
        $classXml = $this->featSvc->SchemaToXml($schemas);
        $classXml = substr($classXml, strpos($classXml, "<xs:schema"));

        $output .= $classXml;
        $output .= "<Features>";

        $this->writer->WriteChunk($output);
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
                            //NOTE: It appears PHP booleans are not string-able
                            $output .= ($this->reader->GetBoolean($i) ? "true" : "false");
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

            $this->writer->WriteChunk($output);
            $output = "";
        }

        $output .= "</Features></FeatureSet>";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
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
