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
require_once "czmlwriter.php";

class CzmlStyle
{
    public $point;
    public $line;
    public $area;
    public $filter;
}

class MgCzmlResult
{
    private $featSvc;
    private $limit;
    private $transform;
    private $writer;
    private $fsId;
    private $className;
    private $query;
    private $vlNode;
    private $baseFilter;

    public function __construct($featSvc, $fsId, $className, $query, $limit, $baseFilter, $vlNode, $writer = NULL) {
        $this->featSvc = $featSvc;
        $this->limit = $limit;
        $this->fsId = $fsId;
        $this->className = $className;
        $this->query = $query;
        $this->vlNode = $vlNode;
        $this->baseFilter = $baseFilter;
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

    private static function CreateDefaultPointStyle() {
        $style = new stdClass();
        $style->color = array(0, 255, 0, 255);
        $style->size = 3.0;
        return $style;
    }

    private static function CreateDefaultLineStyle() {
        $style = new stdClass();
        $style->color = array(0, 255, 255, 255);
        return $style;
    }

    private static function CreateDefaultAreaStyle() {
        $style = new stdClass();
        $style->outline = true;
        $style->outlineColor = array(0, 0, 0, 255);
        $style->fillColor = array(255, 127, 127, 153);
        return $style;
    }

    private static function CreatePointStyle($ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("PointSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $sizeXNodes = $ruleNode->getElementsByTagName("SizeX");
        $sizeYNodes = $sym2DNode->getElementsByTagName("SizeY");
        $colorNodes = $sym2DNode->getElementsByTagName("ForegroundColor");
        if ($sizeXNodes->length == 1 && $sizeYNodes->length == 1) {
            $style->size = (floatval($sizeXNodes->item(0)->nodeValue) + floatval($sizeYNodes->item(0)->nodeValue)) / 2.0;
        } else {
            $style->size = 3.0;
        }
        if ($colorNodes->length == 1) {
            $style->color = MgUtils::HtmlToRgba($colorNodes->item(0)->nodeValue);
        } else {
            $style->color = array(0, 255, 255, 255);
        }
        return $style;
    }

    private static function CreateLineStyle($ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("LineSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $labelNodes = $ruleNode->getElementsByTagName("Label");
        $colorNodes = $sym2DNode->getElementsByTagName("Color");
        if ($labelNodes->length == 1) {

        }
        if ($colorNodes->length == 1) {
            $style->color = MgUtils::HtmlToRgba($colorNodes->item(0)->nodeValue);
        } else {
            $style->color = array(0, 255, 255, 255);
        }
        return $style;
    }

    private static function CreateAreaStyle($ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("AreaSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $labelNodes = $ruleNode->getElementsByTagName("Label");
        $fillNodes = $sym2DNode->getElementsByTagName("Fill");
        $strokeNodes = $sym2DNode->getElementsByTagName("Stroke");
        if ($labelNodes->length == 1) {

        }
        if ($fillNodes->length == 1) {
            $style->fillColor = MgUtils::HtmlToRgba($fillNodes->item(0)->getElementsByTagName("ForegroundColor")->item(0)->nodeValue);
        }
        if ($strokeNodes->length == 1) {
            $style->outline = true;
            $style->outlineColor = MgUtils::HtmlToRgba($strokeNodes->item(0)->getElementsByTagName("Color")->item(0)->nodeValue);
        }
        return $style;
    }

    public function Output() {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $this->writer->SetHeader("Content-Type", MgMimeType::Json);
        $this->writer->StartChunking();
        $output = '['."\n";
        $output .= '{ "id": "document", "version": "1.0" }';

        $atsNodes = $this->vlNode->getElementsByTagName("AreaTypeStyle");
        $ltsNodes = $this->vlNode->getElementsByTagName("LineTypeStyle");
        $ptsNodes = $this->vlNode->getElementsByTagName("PointTypeStyle");

        $defaultStyleKey = uniqid();

        //Parse the rules to construct our CZML style objects
        $styles = array();
        if ($atsNodes->length > 0) {
            $atsNode = $atsNodes->item(0);
            $ruleNodes = $atsNode->getElementsByTagName("AreaRule");
            if ($ruleNodes->length == 1) {
                $ruleNode = $ruleNodes->item(0);
                $areaStyle = self::CreateAreaStyle($ruleNode);
                $filterNodes = $ruleNode->getElementsByTagName("Filter");
                if ($filterNodes->length == 1) {
                    $flt = $filterNodes->item(0)->nodeValue;
                    if (!array_key_exists($flt, $styles)) {
                        $styles[$flt] = new CzmlStyle();
                    }
                    $styles[$flt]->area = $areaStyle;
                } else { //Default rule
                    if (!array_key_exists($defaultStyleKey, $styles)) {
                        $cs = new CzmlStyle();
                        $cs->point = self::CreateDefaultPointStyle();
                        $cs->line = self::CreateDefaultLineStyle();
                        $cs->area = self::CreateDefaultAreaStyle();
                        $styles[$defaultStyleKey] = $cs;
                    }
                    $styles[$defaultStyleKey]->area = $areaStyle;
                }
            } else { //This is a themed style
                for ($i = 0; $i < $ruleNodes->length; $i++) {
                    $ruleNode = $ruleNodes->item($i);
                    $areaStyle = self::CreateAreaStyle($ruleNode);
                    $filterNodes = $ruleNode->getElementsByTagName("Filter");
                    if ($filterNodes->length == 1) {
                        $flt = $filterNodes->item(0)->nodeValue;
                        if (!array_key_exists($flt, $styles)) {
                            $styles[$flt] = new CzmlStyle();
                        }
                        $styles[$flt]->area = $areaStyle;
                    } /*
                    else { //Default rule
                        if (!array_key_exists($defaultStyleKey, $styles)) {
                            $cs = new CzmlStyle();
                            $cs->point = self::CreateDefaultPointStyle();
                            $cs->line = self::CreateDefaultLineStyle();
                            $cs->area = self::CreateDefaultAreaStyle();
                            $styles[$defaultStyleKey] = $cs;
                        }
                        $styles[$defaultStyleKey]->area = $areaStyle;
                    } */
                }
            }
        }
        if ($ltsNodes->length > 0) {
            $ltsNode = $ltsNodes->item(0);
            $ruleNodes = $ltsNode->getElementsByTagName("LineRule");
            if ($ruleNodes->length == 1) {
                $ruleNode = $ruleNodes->item(0);
                $lineStyle = self::CreateLineStyle($ruleNode);
                $filterNodes = $ruleNode->getElementsByTagName("Filter");
                if ($filterNodes->length == 1) {
                    $flt = $filterNodes->item(0)->nodeValue;
                    if (!array_key_exists($flt, $styles)) {
                        $styles[$flt] = new CzmlStyle();
                    }
                    $styles[$flt]->line = $lineStyle;
                } else { //Default rule
                    if (!array_key_exists($defaultStyleKey, $styles)) {
                        $cs = new CzmlStyle();
                        $cs->point = self::CreateDefaultPointStyle();
                        $cs->line = self::CreateDefaultLineStyle();
                        $cs->area = self::CreateDefaultAreaStyle();
                        $styles[$defaultStyleKey] = $cs;
                    }
                    $styles[$defaultStyleKey]->line = $lineStyle;
                }
            } else { //This is a themed style
                for ($i = 0; $i < $ruleNodes->length; $i++) {
                    $ruleNode = $ruleNodes->item($i);
                    $lineStyle = self::CreateLineStyle($ruleNode);
                    $filterNodes = $ruleNode->getElementsByTagName("Filter");
                    if ($filterNodes->length == 1) {
                        $flt = $filterNodes->item(0)->nodeValue;
                        if (!array_key_exists($flt, $styles)) {
                            $styles[$flt] = new CzmlStyle();
                        }
                        $styles[$flt]->line = $lineStyle;
                    } /*
                    else { //Default rule
                        if (!array_key_exists($defaultStyleKey, $styles)) {
                            $cs = new CzmlStyle();
                            $cs->point = self::CreateDefaultPointStyle();
                            $cs->line = self::CreateDefaultLineStyle();
                            $cs->area = self::CreateDefaultAreaStyle();
                            $styles[$defaultStyleKey] = $cs;
                        }
                        $styles[$defaultStyleKey]->line = $lineStyle;
                    } */
                }
            }
        }
        if ($ptsNodes->length > 0) {
            $ptsNode = $ptsNodes->item(0);
            $ruleNodes = $ptsNode->getElementsByTagName("PointRule");
            if ($ruleNodes->length == 1) {
                $ruleNode = $ruleNodes->item(0);
                $pointStyle = self::CreatePointStyle($ruleNode);
                $filterNodes = $ruleNode->getElementsByTagName("Filter");
                if ($filterNodes->length == 1) {
                    $flt = $filterNodes->item(0)->nodeValue;
                    if (!array_key_exists($flt, $styles)) {
                        $styles[$flt] = new CzmlStyle();
                    }
                    $styles[$flt]->point = $pointStyle;
                } else { //Default rule
                    if (!array_key_exists($defaultStyleKey, $styles)) {
                        $cs = new CzmlStyle();
                        $cs->point = self::CreateDefaultPointStyle();
                        $cs->line = self::CreateDefaultLineStyle();
                        $cs->area = self::CreateDefaultAreaStyle();
                        $styles[$defaultStyleKey] = $cs;
                    }
                    $styles[$defaultStyleKey]->point = $pointStyle;
                }
            } else { //This is a themed style
                for ($i = 0; $i < $ruleNodes->length; $i++) {
                    $ruleNode = $ruleNodes->item($i);
                    $pointStyle = self::CreatePointStyle($ruleNode);
                    $filterNodes = $ruleNode->getElementsByTagName("Filter");
                    if ($filterNodes->length == 1) {
                        $flt = $filterNodes->item(0)->nodeValue;
                        if (!array_key_exists($flt, $styles)) {
                            $styles[$flt] = new CzmlStyle();
                        }
                        $styles[$flt]->point = $pointStyle;
                    } /* else { //Default rule
                        if (!array_key_exists($defaultStyleKey, $styles)) {
                            $cs = new CzmlStyle();
                            $cs->point = self::CreateDefaultPointStyle();
                            $cs->line = self::CreateDefaultLineStyle();
                            $cs->area = self::CreateDefaultAreaStyle();
                            $styles[$defaultStyleKey] = $cs;
                        }
                        $styles[$defaultStyleKey]->point = $pointStyle;
                    } */
                }
            }
        }
        foreach ($styles as $filter => $style) {
            if ($filter === $defaultStyleKey) {
                if ($this->baseFilter != NULL)
                    $this->query->SetFilter("(".$this->baseFilter.")");
                else
                    $this->query->SetFilter("");
            } else {
                if ($this->baseFilter != NULL)
                    $this->query->SetFilter("(".$this->baseFilter.") AND (".$filter.")");
                else
                    $this->query->SetFilter($filter);
            }
            $reader = $this->featSvc->SelectFeatures($this->fsId, $this->className, $this->query);
            $clsDef = $reader->GetClassDefinition();
            $clsIdProps = $clsDef->GetIdentityProperties();
            $idProp = NULL;
            if ($clsIdProps->GetCount() == 1) {
                $idProp = $clsIdProps->GetItem(0);
            }
            $propCount = $reader->GetPropertyCount();
            while ($reader->ReadNext()) {
                $read++;
                if ($this->limit > 0 && $read > $this->limit) {
                    break;
                }
                $featCzml = MgCzmlWriter::FeatureToCzml($reader, $agfRw, $this->transform, $clsDef->GetDefaultGeometryPropertyName(), $style, ($idProp != NULL ? $idProp->GetName() : NULL));
                if (strlen($featCzml) > 0) {
                    $output .= ",";
                    $output .= $featCzml;
                    $this->writer->WriteChunk($output);
                    $output = "";
                }
            }
            $reader->Close();
        }

        $output .= "]";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
    }
}

?>