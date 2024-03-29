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

class MgNullPropertyDefinitionCollection
{
    public function GetCount() { return 0; }
}

class MgNullClassDefinition
{
    public function GetName() { return ""; }
    public function GetProperties() { return new MgNullPropertyDefinitionCollection(); }
    public function GetIdentityProperties() { return new MgNullPropertyDefinitionCollection(); }
}

class MgNullFeatureReader
{
    public function GetClassDefinition() {
        return new MgNullClassDefinition();
    }
    public function GetPropertyCount() { return 0; }
    public function ReadNext() { return false; }
    public function Close() {}
}

abstract class MgChunkWriter
{
    public abstract function SetHeader(/*php_string*/ $name, /*php_string*/ $value);

    public abstract function WriteChunk(/*php_string*/ $chunk);

    public abstract function StartChunking();

    public abstract function EndChunking();
}

class MgSlimChunkWriter extends MgChunkWriter
{
    private $app;

    public function __construct(IAppServices $app) {
        $this->app = $app;
    }

    public function SetHeader(/*php_string*/ $name, /*php_string*/ $value) {
        $this->app->SetResponseHeader($name, $value);
    }

    public function WriteChunk(/*php_string*/ $chunk) {
        $this->app->WriteResponseContent($chunk);
    }

    public function StartChunking() { }

    public function EndChunking() { }
}

class MgHtmlHeaderFooterModel
{
    public $baseUrl;
    public $className;
    public $maxPages;
    public $pageNo;
    public $total;
    public $hasMorePages;
    public $prevPageUrl;
    public $nextPageUrl;

    public function __construct(/*php_string*/ $className) {
        $this->className = $className;
        $this->maxPages = -1;
        $this->pageNo = 1;
        $this->total = -1;
        $this->hasMorePages = true;
    }
}

class MgHtmlBodyModel
{
    private $reader;

    private $agfRw;
    private $wktRw;
    private $transform;

    public $propertyCount;
    private $displayMap;

    public function __construct(/* MgReader | MgPaginatedFeatureReader */ $reader, MgTransform $transform = null, /*php_int*/ $limit = -1, array $displayMap = null) {
        $this->reader = $reader;
        $this->propertyCount = $this->reader->GetPropertyCount();
        $this->agfRw = new MgAgfReaderWriter();
        $this->wktRw = new MgWktReaderWriter();
        $this->transform = $transform;
        $this->displayMap = $displayMap;
    }

    public function read() {
        return $this->reader->ReadNext();
    }

    public function endOfReader() {
        if (is_callable(array($this->reader, "EndOfReader")))
            return $this->reader->EndOfReader();
        else
            return false;
    }

    public function propertyName(/*php_int*/ $index) {
        $name = $this->reader->GetPropertyName($index);
        if (isset($this->displayMap) && array_key_exists($name, $this->displayMap)) {
            $name = $this->displayMap[$name];
        }
        return $name;
    }

    public function getValue(/*php_int*/ $i) {
        $output = "";
        $propType = $this->reader->GetPropertyType($i);
        if (!$this->reader->IsNull($i)) {
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
                    $output .= MgUtils::DateTimeToString($dt);
                    break;
                case MgPropertyType::Decimal:
                case MgPropertyType::Double:
                    $output .= $this->reader->GetDouble($i);
                    break;
                case MgPropertyType::Geometry:
                    {
                        try {
                            $agf = $this->reader->GetGeometry($i);
                            $geom = ($this->transform != null) ? $this->agfRw->Read($agf, $this->transform) : $this->agfRw->Read($agf);
                            $output .= $this->wktRw->Write($geom);
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
        } else {
            $output .= "(null)";
        }
        return $output;
    }
}

class MgReaderChunkedResult
{
    private $featSvc;
    private $reader;
    private $limit;
    private $transform;
    private $writer;

    private $displayMap;

    //For HTML output
    private $baseUrl;
    private $thisUrl;
    private $thisReqParams;
    private $orientation;
    private $templateRootDir;

    public function __construct(MgFeatureService $featSvc, /* MgReader | MgPaginatedFeatureReader */ $reader, /*php_int*/ $limit, MgChunkWriter $writer) {
        $this->featSvc = $featSvc;
        $this->reader = $reader;
        $this->limit = $limit;
        $this->baseUrl = null;
        $this->transform = null;
        $this->orientation = "h";
        $this->thisReqParams = array();
        $this->writer = $writer;
    }

    public function SetDisplayMappings($displayMap) {
        $this->displayMap = $displayMap;
    }

    public function CheckAndSetDownloadHeaders(IAppServices $app, $format) {
        $downloadFlag = $app->GetRequestParameter("download");
        if ($downloadFlag === "1" || $downloadFlag === "true") {
            $fn = "download";
            if ($app->GetRequestParameter("downloadname"))
                $fn = $app->GetRequestParameter("downloadname");
            $ext = $format;
            if ($format == "geojson")
                $ext = "json";
            $this->writer->SetHeader("Content-Disposition", "attachment; filename=".$fn.".".$ext);
        }
    }

    public function SetAttributeDisplayOrientation(/*php_string*/ $orientation) {
        $this->orientation = $orientation;
    }

    public function SetTransform(MgTransform $tx) {
        $this->transform = $tx;
    }

    public function SetHtmlParams(IAppServices $app) {
        $this->baseUrl = $app->GetConfig("SelfUrl");
        $this->thisUrl = $app->GetConfig("SelfUrl").$app->GetRequestPathInfo();
        $this->thisReqParams = $app->GetAllRequestParams();
        $this->templateRootDir = $app->GetConfig("Cache.RootDir")."/templates_c";
        $this->locale = $app->GetConfig("Locale");
    }

    private function OutputGeoJson(MgFeatureSchemaCollection $schemas) {
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
            $output .= MgReaderToGeoJsonWriter::FeatureToGeoJson($this->reader, $agfRw, $this->transform, ($idProp != NULL ? $idProp->GetName() : NULL), $this->displayMap);
            $this->writer->WriteChunk($output);
            $output = "";

            $firstFeature = false;
        }

        $output .= "]}";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
        $this->reader->Close();
    }

    private static function WriteFeatureAttributeCell(MgReader $reader, /*php_int*/ $i, MgAgfReaderWriter $agfRw, MgWktReaderWriter $wktRw, MgTransform $transform = null) {
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

    private function OutputHtml(MgFeatureSchemaCollection $schemas) {
        $read = 0;

        $paginated = (is_callable(array($this->reader, "GetPageSize")) && is_callable(array($this->reader, "GetPageNo")));

        $this->writer->SetHeader("Content-Type", MgMimeType::Html);
        $this->writer->StartChunking();

        $tplHead = new Smarty();
        $tplBody = new Smarty();
        $tplFoot = new Smarty();
        $tplHead->setCompileDir($this->templateRootDir);
        $tplBody->setCompileDir($this->templateRootDir);
        $tplFoot->setCompileDir($this->templateRootDir);

        $clsDef = $this->reader->GetClassDefinition();
        $hfModel = new MgHtmlHeaderFooterModel($clsDef->GetName());
        $bodyModel = new MgHtmlBodyModel($this->reader, $this->transform, $this->limit, $this->displayMap);

        $hfModel->baseUrl = $this->baseUrl;

        if (array_key_exists("page", $this->thisReqParams)) {
            $this->thisReqParams["page"] = $this->reader->GetPageNo();
        }
        $hfModel->isPaginated = $paginated;
        //Write pagination HTML if this reader is paginated
        if ($paginated === TRUE) {
            $hfModel->pageSize = $this->reader->GetPageSize();
            $hfModel->pageNo = $this->reader->GetPageNo();

            $nextUrl = $this->thisUrl;
            $firstPart = true;
            foreach ($this->thisReqParams as $key => $value) {
                if ($firstPart === true) {
                    $nextUrl .= "?";
                    $firstPart = false;
                } else {
                    $nextUrl .= "&";
                }

                if (strtoupper($key) == "PAGE") {
                    $nextUrl .= "$key=".($hfModel->pageNo + 1);
                } else {
                    $nextUrl .= "$key=$value";
                }
            }
            $prevUrl = $this->thisUrl;
            $firstPart = true;
            foreach ($this->thisReqParams as $key => $value) {
                if ($firstPart === true) {
                    $prevUrl .= "?";
                    $firstPart = false;
                } else {
                    $prevUrl .= "&";
                }

                if (strtoupper($key) == "PAGE") {
                    $prevUrl .= "$key=".($hfModel->pageNo - 1);
                } else {
                    $prevUrl .= "$key=$value";
                }
            }

            $hfModel->nextPageUrl = $nextUrl;
            $hfModel->prevPageUrl = $prevUrl;
            $hfModel->hasMorePages = $this->reader->HasMorePages();
            $hfModel->maxPages = $this->reader->GetMaxPages();
            $hfModel->total = $this->reader->GetTotal();
        }

        $locale = $this->locale;
        $tplHead->assign("model", $hfModel);
        $tplBody->assign("model", $bodyModel);
        $tplFoot->assign("model", $hfModel);

        if ($this->orientation === "h") {
            $this->writer->WriteChunk($tplHead->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_horizontal_head.tpl"));
            $this->writer->WriteChunk($tplBody->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_horizontal_body.tpl"));
            $this->writer->WriteChunk($tplFoot->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_horizontal_foot.tpl"));
        } else { //vertical
            $this->writer->WriteChunk($tplHead->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_vertical_head.tpl"));
            $this->writer->WriteChunk($tplBody->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_vertical_body.tpl"));
            $this->writer->WriteChunk($tplFoot->fetch(dirname(__FILE__)."/../res/templates/$locale/feature_html_vertical_foot.tpl"));
        }

        $this->writer->EndChunking();
        $this->reader->Close();
    }

    private function IsEmpty(MgFeatureSchemaCollection $schemas) {
        $count = 0;
        for ($i = 0; $i < $schemas->GetCount(); $i++) {
            $schema = $schemas->GetItem($i);
            $classes = $schema->GetClasses();
            $count += $classes->GetCount();
        }
        return $count == 0;
    }

    private function OutputXml(MgFeatureSchemaCollection $schemas) {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $wktRw = new MgWktReaderWriter();

        $this->writer->SetHeader("Content-Type", MgMimeType::Xml);
        $this->writer->StartChunking();

        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?><FeatureSet>";
        if (!$this->IsEmpty($schemas)) {
            $classXml = $this->featSvc->SchemaToXml($schemas);
            $classXml = substr($classXml, strpos($classXml, "<xs:schema"));

            $output .= $classXml;
        }
        $hasMoreFeatures = $this->reader->ReadNext();
        $writeXmlFooter = false;
        if ($hasMoreFeatures) {
            $output .= "<Features>";
            $this->writer->WriteChunk($output);
            $output = "";
            $writeXmlFooter = true;
        }
        $propCount = $this->reader->GetPropertyCount();
        while ($hasMoreFeatures) {
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
            $hasMoreFeatures = $this->reader->ReadNext();
        }

        if ($writeXmlFooter) {
            $output .= "</Features>";
        }
        $output .= "</FeatureSet>";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
        $this->reader->Close();
    }

    public function Output(/*php_string*/ $format = "xml") {
        $schemas = new MgFeatureSchemaCollection();
        $schema = new MgFeatureSchema("TempSchema", "");
        $schemas->Add($schema);
        $classes = $schema->GetClasses();
        $clsDef = $this->reader->GetClassDefinition();

        //We may be plugging in a MgNullFeatureReader here, which may quack
        //like a duck, but is no duck.
        if ($clsDef instanceof MgClassDefinition)
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