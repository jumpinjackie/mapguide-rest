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

    private $pointStyleNo;
    private $lineStyleNo;
    private $areaStyleNo;

    public function __construct(MgFeatureService $featSvc, MgResourceIdentifier $fsId, /*php_string*/ $className, MgFeatureQueryOptions $query, /*php_int*/ $limit, /*php_string*/ $baseFilter, DOMNode $vlNode, MgChunkWriter $writer = NULL) {
        $this->featSvc = $featSvc;
        $this->limit = $limit;
        $this->fsId = $fsId;
        $this->className = $className;
        $this->query = $query;
        $this->vlNode = $vlNode;
        $this->baseFilter = $baseFilter;
        $this->transform = null;
        $this->pointStyleNo = 1;
        $this->lineStyleNo = 1;
        $this->areaStyleNo = 1;
        $this->writer = $writer;
    }

    public function CheckAndSetDownloadHeaders(IAppServices $app, /*php_string*/ $format) {
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

    public function SetTransform(MgTransform $tx) {
        $this->transform = $tx;
    }

    private static function CreateDefaultPointStyle() {
        $style = new stdClass();
        $style->color = function($reader) { return array(0, 255, 0, 255); };
        $style->size = function($reader) { return 3.0; };
        return $style;
    }

    private static function CreateDefaultLineStyle() {
        $style = new stdClass();
        $style->color = function($reader) { return array(0, 255, 255, 255); };
        return $style;
    }

    private static function CreateDefaultAreaStyle() {
        $style = new stdClass();
        $style->outline = true;
        $style->outlineColor = function($reader) { return array(0, 0, 0, 255); };
        $style->fillColor = function($reader) { array(255, 127, 127, 153); };
        return $style;
    }

    private function CreatePointStyle(MgFeatureQueryOptions $query, DOMNode $ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("PointSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $sizeXNodes = $sym2DNode->getElementsByTagName("SizeX");
        $sizeYNodes = $sym2DNode->getElementsByTagName("SizeY");
        $colorNodes = $sym2DNode->getElementsByTagName("ForegroundColor");
        if ($sizeXNodes->length == 1 && $sizeYNodes->length == 1) {
            $xExpr = $sizeXNodes->item(0)->nodeValue;
            $yExpr = $sizeYNodes->item(0)->nodeValue;
            if (is_numeric($xExpr) && is_numeric($yExpr)) {
                $style->size = function($reader) use ($xExpr, $yExpr) {
                    return ($xExpr + $yExpr) / 2.0;
                };
            } else {
                $xAlias = "EXPR_POINT_X_SIZE_".($this->pointStyleNo++);
                $yAlias = "EXPR_POINT_Y_SIZE_".($this->pointStyleNo++);
                $style->size = function($reader) use ($xAlias, $yAlias) {
                    $xVal = floatval(MgUtils::GetBasicValueFromReader($reader, $xAlias));
                    $yVal = floatval(MgUtils::GetBasicValueFromReader($reader, $yAlias));
                    return ($xVal + $yVal) / 2.0;
                };
            }
        } else {
            $style->size = function($reader) { return 3.0; };
        }
        if ($colorNodes->length == 1) {
            $colorExpr = $colorNodes->item(0)->nodeValue;
            $color = MgUtils::HtmlToRgba($colorExpr);
            if ($color === FALSE) { //Does not parse into a color. Assume FDO expression
                $colorAlias = "EXPR_POINT_COLOR_".($this->pointStyleNo++);
                $query->AddComputedProperty($colorAlias, $colorExpr);
                $style->color = function($reader) use ($colorAlias) {
                    $colorStr = MgUtils::GetBasicValueFromReader($reader, $colorAlias);
                    return MgUtils::HtmlToRgba($colorStr);
                };
            } else {
                $style->color = function($reader) use ($color) { return $color; };
            }
        } else {
            $style->color = function($reader) { return array(0, 255, 255, 255); };
        }
        return $style;
    }

    private function CreateLineStyle(MgFeatureQueryOptions $query, DOMNode $ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("LineSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $labelNodes = $ruleNode->getElementsByTagName("Label");
        $colorNodes = $sym2DNode->getElementsByTagName("Color");
        if ($labelNodes->length == 1) {

        }
        if ($colorNodes->length == 1) {
            $colorExpr = $colorNodes->item(0)->nodeValue;
            $color = MgUtils::HtmlToRgba($colorExpr);
            if ($color === FALSE) { //Does not parse into a color. Assume FDO expression
                $colorAlias = "EXPR_LINE_COLOR_".($this->lineStyleNo++);
                $query->AddComputedProperty($colorAlias, $colorExpr);
                $style->color = function($reader) use ($colorAlias) {
                    $colorStr = MgUtils::GetBasicValueFromReader($reader, $colorAlias);
                    return MgUtils::HtmlToRgba($colorStr);
                };
            } else {
                $style->color = function($reader) use ($color) { return $color; };
            }
        } else {
            $style->color = function($reader) { return array(0, 255, 255, 255); };
        }
        return $style;
    }

    private function CreateAreaStyle(MgFeatureQueryOptions $query, DOMNode $ruleNode) {
        $style = new stdClass();
        $sym2DNodes = $ruleNode->getElementsByTagName("AreaSymbolization2D");
        $sym2DNode = $sym2DNodes->item(0);
        $labelNodes = $ruleNode->getElementsByTagName("Label");
        $fillNodes = $sym2DNode->getElementsByTagName("Fill");
        $strokeNodes = $sym2DNode->getElementsByTagName("Stroke");
        if ($labelNodes->length == 1) {

        }
        if ($fillNodes->length == 1) {
            $colorExpr = $fillNodes->item(0)->getElementsByTagName("ForegroundColor")->item(0)->nodeValue;
            $color = MgUtils::HtmlToRgba($colorExpr);
            if ($color === FALSE) { //Does not parse into a color. Assume FDO expression
                $colorAlias = "EXPR_AREA_FILL_COLOR_".($this->areaStyleNo++);
                $query->AddComputedProperty($colorAlias, $colorExpr);
                $style->fillColor = function($reader) use ($colorAlias) {
                    $colorStr = MgUtils::GetBasicValueFromReader($reader, $colorAlias);
                    return MgUtils::HtmlToRgba($colorStr);
                };
            } else {
                $style->fillColor = function($reader) use ($color) { return $color; };
            }
        }
        if ($strokeNodes->length == 1) {
            $style->outline = true;
            $colorExpr = $strokeNodes->item(0)->getElementsByTagName("Color")->item(0)->nodeValue;
            $color = MgUtils::HtmlToRgba($colorExpr);
            if ($color === FALSE) { //Does not parse into a color. Assume FDO expression
                $colorAlias = "EXPR_AREA_OUTLINE_COLOR_".($this->areaStyleNo++);
                $query->AddComputedProperty($colorAlias, $colorExpr);
                $style->outlineColor = function($reader) use ($colorAlias) {
                    $colorStr = MgUtils::GetBasicValueFromReader($reader, $colorAlias);
                    return MgUtils::HtmlToRgba($colorStr);
                };
            } else {
                $style->outlineColor = function($reader) use ($color) { return $color; };
            }
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
                $areaStyle = $this->CreateAreaStyle($this->query, $ruleNode);
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
                    $areaStyle = $this->CreateAreaStyle($this->query, $ruleNode);
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
                $lineStyle = $this->CreateLineStyle($this->query, $ruleNode);
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
                    $lineStyle = $this->CreateLineStyle($this->query, $ruleNode);
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
                $pointStyle = $this->CreatePointStyle($this->query, $ruleNode);
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
                    $pointStyle = $this->CreatePointStyle($this->query, $ruleNode);
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