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
require_once dirname(__FILE__)."/utils.php";

class MgCzmlWriter
{
    private static function SingleFeatureToCzml(/*php_string|php_int*/ $idVal, MgReader $reader, /*php_string*/ $geomCzml) {
        $output = "{";
        $output .= '"id": "'.$idVal.'"';

        $ttIndex = -1;
        $hlIndex = -1;
        try {
            $ttIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_TOOLTIP);
        } catch (MgException $ex) { }
        try {
            $hlIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_HYPERLINK);
        } catch (MgException $ex) { }

        $html = "";
        if ($ttIndex >= 0) {
            $html .= str_replace('\\\n', "<br/>", MgUtils::EscapeJsonString($reader->GetString($ttIndex)));
        }
        /*
        if ($hlIndex >= 0) {
            $html .= '<br/><a href=\"'.MgUtils::EscapeJsonString($reader->GetString($hlIndex)).'\">Click to open link</a>';
        }
        */
        //TODO: Include feature properties as specified in the Layer Definition

        if (strlen($html) > 0)
            $output .= ', "description": "'.$html.'"';

        $output .= ",";
        
        $output .= $geomCzml;

        $output .= "}";
        return $output;
    }

    public static function FeatureToCzml(MgReader $reader, MgAgfReaderWriter $agfRw, MgTransform $transform, /*php_string*/ $geometryName, CzmlStyle $style, /*php_string*/ $idName = NULL) {
        if (!$reader->IsNull($geometryName)) {

            $agf = null;
            $geom = null;

            try {
                $agf = $reader->GetGeometry($geometryName);
                $geom = $agfRw->Read($agf, $transform);
            }
            catch (MgException $ex) { //Bail on bad geometries
                return "";
            }

            $idIndex = -1;
            if ($idName != NULL)
                $idIndex = $reader->GetPropertyIndex($idName);

            //Write ID
            $idVal = "";
            if ($idIndex >= 0 && !$reader->IsNull($idIndex)) {
                $propType = $reader->GetPropertyType($idIndex);
                switch($propType) {
                    case MgPropertyType::DateTime:
                        $dt = $reader->GetDateTime($idIndex);
                        $idVal = '"'.MgUtils::DateTimeToString($dt).'"';
                        break;
                    case MgPropertyType::Double:
                        $idVal = $reader->GetDouble($idIndex);
                        break;
                    case MgPropertyType::Int16:
                        $idVal = $reader->GetInt16($idIndex);
                        break;
                    case MgPropertyType::Int32:
                        $idVal = $reader->GetInt32($idIndex);
                        break;
                    case MgPropertyType::Int64:
                        $idVal = $reader->GetInt64($idIndex);
                        break;
                    case MgPropertyType::Single:
                        $idVal = $reader->GetSingle($idIndex);
                        break;
                    case MgPropertyType::String:
                        $idVal = MgUtils::EscapeJsonString($reader->GetString($idIndex));
                        break;
                }
            } else {
                $idVal = uniqid();
            }

            $elevIndex = -1;
            $extrude = 0.0;
            try {
                $elevIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_Z_EXTRUSION);
                if ($elevIndex >= 0) {
                    switch ($reader->GetPropertyType($elevIndex)) {
                        case MgPropertyType::Int16:
                            $extrude = $reader->GetInt16($elevIndex);
                            break;
                        case MgPropertyType::Int32:
                            $extrude = $reader->GetInt32($elevIndex);
                            break;
                        case MgPropertyType::Int64:
                            $extrude = $reader->GetInt64($elevIndex);
                            break;
                        case MgPropertyType::Double:
                            $extrude = $reader->GetDouble($elevIndex);
                            break;
                        case MgPropertyType::Single:
                            $extrude = $reader->GetSingle($elevIndex);
                            break;
                    }
                }
                //TODO: If units not in meters, convert it to meters
            } catch (MgException $ex) { }

            switch ($geom->GetGeometryType()) {
                case MgGeometryType::Point:
                case MgGeometryType::LineString:
                case MgGeometryType::Polygon:
                    {
                        $geomCzml = self::GeometryToCzml($geom, $reader, $style, $extrude);
                        if ($geomCzml == null)
                            return "";

                        return self::SingleFeatureToCzml($idVal, $reader, $geomCzml);
                    }
                case MgGeometryType::MultiLineString:
                    {
                        //For multi-geometry features, we split this off into separate packets, one packet for
                        //each component geometry
                        $parts = array();
                        $featId = uniqid();
                        for ($i = 0; $i < $geom->GetCount(); $i++) {
                            $idValComp = $idVal."_segment_".$i."_".$featId;
                            $lineStr = $geom->GetLineString($i);
                            $geomCzml = self::GeometryToCzml($lineStr, $reader, $style, $extrude);
                            if ($geomCzml == null)
                                continue;

                            array_push($parts, self::SingleFeatureToCzml($idValComp, $reader, $geomCzml));
                        }
                        return implode(",", $parts);
                    }
                default:
                    return "";
            }
        } else {
            return "";
        }
    }

    private static function GeometryToCzml(MgGeometry $geom, MgReader $reader, CzmlStyle $style, /*php_double*/ $zval = 0.0) {
        $geomType = $geom->GetGeometryType();
        //TODO: Convert all the geometry types.
        //TODO: Translate Layer Definition styles to CZML styles
        switch ($geomType) {
            case MgGeometryType::Point:
                {
                    if (isset($style->point)) {
                        $coord = $geom->GetCoordinate();
                        $pointColor = call_user_func_array($style->point->color, array($reader));
                        $pointSize = call_user_func_array($style->point->size, array($reader));
                        $fragment  = '"point": { "color": { "rgba": ['.implode(",", $pointColor).'] }, "pixelSize": { "number": '.$pointSize.' } }, "position": { "cartographicDegrees": '.self::CoordToCzml($coord)." }";
                        return $fragment;
                    } else {
                        return null; //No style, draw nothing
                    }
                }
            case MgGeometryType::LineString:
                {
                    if (isset($style->line)) {
                        $coords = $geom->GetCoordinates();
                        $posCzml = self::LineStringToCzml($coords);
                        if ($posCzml != null) {
                            $lineColor = call_user_func_array($style->line->color, array($reader));
                            return '"polyline": { "material": { "solidColor": { "color": { "rgba": ['.implode(",", $lineColor).'] } } },"positions": '.$posCzml.'}';
                        } else {
                            return null;
                        }
                    } else {
                        return null; //No style, draw nothing
                    }
                }
            case MgGeometryType::Polygon:
                {
                    if (isset($style->area)) {
                        $fragment = '"polygon": { ';
                        if ($zval > 0.0) {
                            $fragment .= '"extrudedHeight": { "number": '.$zval.' }, ';
                        }
                        if (isset($style->area->outline) && $style->area->outline === true && is_callable($style->area->outlineColor)) {
                            $areaOutlineColor = call_user_func_array($style->area->outlineColor, array($reader));
                            $fragment .= '"outline": { "boolean": true }, "outlineColor": { "rgba": ['.implode(",", $areaOutlineColor).'] }, ';
                        }
                        $areaFillColor = call_user_func_array($style->area->fillColor, array($reader));
                        $fragment .= '"material": { "solidColor": { "color": { "rgba": ['.implode(",", $areaFillColor).'] } } },"positions": '.self::PolygonToCzml($geom).'}';
                        return $fragment;
                    } else {
                        return null; //No style, draw nothing
                    }
                }
            default:
                return null;
        }
    }

    private static function CoordToCzml(MgCoordinate $coord, /*php_double*/ $zval = 0.0, /*php_bool*/ $enclose = true) {
        $str = "";
        if ($enclose)
            $str .= "[";
        $str .= $coord->GetX().",".$coord->GetY().",".$zval;
        if ($enclose)
            $str .= "]";
        return $str;
    }

    private static function LineStringToCzml(MgCoordinateIterator $coords, /*php_double*/ $zval = 0.0) {
        //HACK: Cesium does not like polylines that are all the same coordinates.
        //To workaround this, we bail on any line strings that have identical coordinates
        //this assoc array will use to check for this
        $fragments = array();
        $str = '{ "cartographicDegrees": [';
        $first = true;
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $coordCzml = self::CoordToCzml($coord, $zval, false);
            if (!array_key_exists($coordCzml, $fragments))
                $fragments[$coordCzml] = true;
            $str .= $coordCzml;
            $first = false;
        }
        $str .= ']}';

        //If there's only one item, it means all czml fragments we try to put into this
        //array resolve to the same item. (ie. All coordinates are identical). In this case
        //return null to indicate that this packet should not be written
        if (count(array_keys($fragments)) == 1)
            return null;
        else
            return $str;
    }

    private static function PolygonToCzml(MgGeometry $geom, /*php_double*/ $zval = 0.0) {
        $str = '{ "cartographicDegrees": [';
        $first = true;
        //TODO: Only handles exterior ring. Can CZML support polygons with holes?
        $extRing = $geom->GetExteriorRing();
        $coords = $extRing->GetCoordinates();
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $str .= self::CoordToCzml($coord, $zval, false);
            $first = false;
        }
        $str .= ']}';
        return $str;
    }
}