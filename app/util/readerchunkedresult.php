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
        
        $this->headers["Transfer-Encoding"] = "chunked";
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        flush();

        while (ob_get_level()) {
            ob_end_flush();
        }
        if (ob_get_length() === false) {
            ob_start();
        }
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

    //For HTML output
    private $baseUrl;
    private $thisUrl;
    private $thisReqParams;
    private $orientation;

    private $localizer;

    public function __construct($featSvc, $reader, $limit, $writer, $localizer) {
        $this->featSvc = $featSvc;
        $this->reader = $reader;
        $this->limit = $limit;
        $this->baseUrl = null;
        $this->transform = null;
        $this->localizer = $localizer;
        $this->orientation = "h";
        $this->thisReqParams = array();
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

    public function SetAttributeDisplayOrientation($orientation) {
        $this->orientation = $orientation;
    }

    public function SetTransform($tx) {
        $this->transform = $tx;
    }

    public function SetBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function SetThisUrl($thisUrl, $reqParams) {
        $this->thisUrl = $thisUrl;
        $this->thisReqParams = $reqParams;
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

    private static function WriteFeatureAttributeCell($reader, $i, $agfRw, $wktRw, $transform = null) {
        $output = "";
        $propType = $reader->GetPropertyType($i);
        if (!$reader->IsNull($i)) {
            switch($propType) {
                case MgPropertyType::Boolean:
                    //NOTE: It appears PHP booleans are not string-able
                    $output .= ($reader->GetBoolean($i) ? "true" : "false");
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
                            $geom = ($transform != null) ? $agfRw->Read($agf, $transform) : $agfRw->Read($agf);
                            $output .= $wktRw->Write($geom);
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
        } else {
            $output .= "(null)";
        }
        return $output;
    }

    private function OutputHtml($schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $wktRw = new MgWktReaderWriter();

        $paginated = (is_callable(array($this->reader, "GetPageSize")) && is_callable(array($this->reader, "GetPageNo")));

        //TODO: This should really be offloaded to a smarty template

        $this->writer->SetHeader("Content-Type", MgMimeType::Html);
        $this->writer->StartChunking();

        $output = "<!DOCTYPE html>";
        $output .= "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
        if ($this->baseUrl != null) {
            $bsUrl = $this->baseUrl . "/assets/common/css/bootstrap.min.css";
            $output .= "<link rel='stylesheet' href='".$bsUrl."' />";
        }
        $output .= "</head><body>";

        $this->writer->WriteChunk($output);
        $output = "";

        $propCount = $this->reader->GetPropertyCount();

        $pageHtml = "";
        $totalHtml = "";
        $pageNoHtml = "";
        //Write pagination HTML if this reader is paginated
        if ($paginated === TRUE) {
            $pageSize = $this->reader->GetPageSize();
            $pageNo = $this->reader->GetPageNo();

            $params = $this->thisReqParams;

            $nextUrl = $this->thisUrl;
            $firstPart = true;
            foreach ($params as $key => $value) {
                if ($firstPart === true) {
                    $nextUrl .= "?";
                    $firstPart = false;
                } else {
                    $nextUrl .= "&";
                }

                if (strtoupper($key) == "PAGE") {
                    $nextUrl .= "$key=".($pageNo + 1);
                } else {
                    $nextUrl .= "$key=$value";
                }
            }
            $prevUrl = $this->thisUrl;
            $firstPart = true;
            foreach ($params as $key => $value) {
                if ($firstPart === true) {
                    $prevUrl .= "?";
                    $firstPart = false;
                } else {
                    $prevUrl .= "&";
                }

                if (strtoupper($key) == "PAGE") {
                    $prevUrl .= "$key=".($pageNo - 1);
                } else {
                    $prevUrl .= "$key=$value";
                }
            }

            if ($pageNo > 1) {
                //Write prev/next page links.
                $pageHtml .= "<a href='".$prevUrl."'>&lt;&lt;&nbsp;".$this->localizer->getText("L_PREV_PAGE")."</a>&nbsp;";
                if ($this->reader->HasMorePages()) {
                    $pageHtml .= "|&nbsp;<a href='".$nextUrl."'>".$this->localizer->getText("L_NEXT_PAGE")."&nbsp;&gt;&gt;</a>";
                }
            } else {
                if ($this->reader->HasMorePages()) {
                    $pageHtml .= "<a href='".$nextUrl."'>".$this->localizer->getText("L_NEXT_PAGE")."&nbsp;&gt;&gt;</a>";
                }
            }
            if ($this->reader->GetTotal() >= 0) {
                $totalHtml = "<span>".$this->localizer->getText("L_TOTAL_FEATURES", $this->reader->GetTotal())."</span>";
            }
            $maxPages = $this->reader->GetMaxPages();
            if ($maxPages >= 0) {
                $pageNoHtml = "<strong>(".$this->localizer->getText("L_PAGE_X_OF_Y", $pageNo, $maxPage).")</strong>";
            } else {
                $pageNoHtml = "<strong>(".$this->localizer->getText("L_PAGE_NO", $pageNo).")</strong>";
            }
        }


        $clsDef = $this->reader->GetClassDefinition();
        $idProps = $clsDef->GetIdentityProperties();
        
        $output .= "<div class='pull-left'><div><strong>".$clsDef->GetName()."</strong> $pageNoHtml</div><div>$totalHtml</div><div>$pageHtml</div></div>";
        if ($this->orientation === "h") {
            $output .= "<table class='table table-bordered table-condensed table-hover'>";
            $output .= "<!-- Table header -->";
            $output .= "<tr>";
            for ($i = 0; $i < $propCount; $i++) {
                $name = $this->reader->GetPropertyName($i);
                if ($idProps->IndexOf($name) >= 0) {
                    $output .= "<th>$name*</th>"; //Denote identity property
                } else {
                    $output .= "<th>$name</th>";
                }
            }
            $output .= "</tr>";
            $this->writer->WriteChunk($output);

            while ($this->reader->ReadNext()) {
                $read++;
                if ($this->limit > 0 && $read > $this->limit) {
                    break;
                }

                $output = "<tr>";
                for ($i = 0; $i < $propCount; $i++) {
                    $name = $this->reader->GetPropertyName($i);

                    $output .= "<td>";
                    $output .= self::WriteFeatureAttributeCell($this->reader, $i, $agfRw, $wktRw, $this->transform);
                    $output .= "</td>";

                }

                $output .= "</tr>";

                $this->writer->WriteChunk($output);
                $output = "";
            }

            $output .= "</table>";
            $output .= "<div class='pull-left'>$pageHtml</div>";
            $output .= "</body></html>";
            $this->writer->WriteChunk($output);
        } else { //vertical
            while ($this->reader->ReadNext()) {
                $read++;
                if ($this->limit > 0 && $read > $this->limit) {
                    break;
                }

                $output .= "<table class='table table-bordered table-condensed table-hover'>";
                $output .= "<!-- Table header -->";
                for ($i = 0; $i < $propCount; $i++) {
                    $output .= "<tr>";
                    $name = $this->reader->GetPropertyName($i);
                    $propType = $this->reader->GetPropertyType($i);
                    $name = $this->reader->GetPropertyName($i);
                    if ($idProps->IndexOf($name) >= 0) {
                        $output .= "<td><strong>$name*</strong></td>"; //Denote identity property
                    } else {
                        $output .= "<td><strong>$name</strong></td>";
                    }
                    $output .= "<td>";
                    $output .= self::WriteFeatureAttributeCell($this->reader, $i, $agfRw, $wktRw, $this->transform);
                    $output .= "</td>";
                    $output .= "</tr>";
                }
                $output .= "</table>";
                $this->writer->WriteChunk($output);
                $output = "";
            }
        }
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
        } else if ($format === "html") {
            $this->OutputHtml($schemas);
        } else {
            $this->OutputXml($schemas);
        }
    }
}

?>
