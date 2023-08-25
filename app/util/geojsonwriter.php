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

class MgReaderToGeoJsonWriter
{
    public static function FeatureToGeoJson(MgReader $reader, MgAgfReaderWriter $agfRw, MgTransform $transform = NULL, /*php_string*/ $idName = NULL, array $displayMap = NULL) {
        $idVal = NULL;
        $propVals = array();
        $geomJson = "";
        $idIndex = -1;
        if ($idName != NULL)
            $idIndex = $reader->GetPropertyIndex($idName);

        $propCount = $reader->GetPropertyCount();
        for ($i = 0; $i < $propCount; $i++) {
            $name = $reader->GetPropertyName($i);

            //Override with display name if specified
            if (isset($displayMap) && array_key_exists($name, $displayMap)) {
                $name = $displayMap[$name];
            }

            $propType = $reader->GetPropertyType($i);
            if (!$reader->IsNull($i)) {
                if ($idIndex == $i) {
                    switch($propType) {
                        case MgPropertyType::DateTime:
                            $dt = $reader->GetDateTime($i);
                            $idVal = '"'.MgUtils::DateTimeToString($dt).'"';
                            break;
                        case MgPropertyType::Double:
                            $idVal = $reader->GetDouble($i);
                            break;
                        case MgPropertyType::Int16:
                            $idVal = $reader->GetInt16($i);
                            break;
                        case MgPropertyType::Int32:
                            $idVal = $reader->GetInt32($i);
                            break;
                        case MgPropertyType::Int64:
                            $idVal = $reader->GetInt64($i);
                            break;
                        case MgPropertyType::Single:
                            $idVal = $reader->GetSingle($i);
                            break;
                        case MgPropertyType::String:
                            $idVal = '"'.MgUtils::EscapeJsonString($reader->GetString($i)).'"';
                            break;
                    }
                    if ($idVal != null) {
                        array_push($propVals, '"'.$name.'": '.$idVal);
                    }
                } else {
                    switch($propType) {
                        case MgPropertyType::Boolean:
                            //NOTE: It appears PHP booleans are not string-able
                            array_push($propVals, '"'.$name.'": '.($reader->GetBoolean($i)?"true":"false"));
                            break;
                        case MgPropertyType::Byte:
                            array_push($propVals, '"'.$name.'": '.$reader->GetByte($i));
                            break;
                        case MgPropertyType::DateTime:
                            $dt = $reader->GetDateTime($i);
                            array_push($propVals, '"'.$name.'": "'.MgUtils::DateTimeToString($dt).'"');
                            break;
                        case MgPropertyType::Decimal:
                        case MgPropertyType::Double:
                            array_push($propVals, '"'.$name.'": '.$reader->GetDouble($i));
                            break;
                        case MgPropertyType::Geometry:
                            {
                                try {
                                    $agf = $reader->GetGeometry($i);
                                    $geom = ($transform != null) ? $agfRw->Read($agf, $transform) : $agfRw->Read($agf);
                                    $geomJson = self::ToGeoJson($geom);
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
                }
            } else {
                array_push($propVals, '"'.$name.'": null');
            }
        }
        $output = '{ "type": "Feature", ';
        $idJson = "";
        if ($idVal !== NULL) {
            $idJson = '"id": '.$idVal.', ';
            $output .= $idJson;
        }
        if ($geomJson !== "") {
            $output .= $geomJson.', "properties": {'.implode(",", $propVals)."} }\n";
        } else {
            $output .= '"properties": {'.implode(",", $propVals)."} }\n";;
        }
        return $output;
    }

    public static function ToGeoJson(MgGeometry $geom, /*php_bool*/ $bIncludePropertyName = true, /*php_int*/ $coord_precision = 7) {
        $geomType = $geom->GetGeometryType();
        $prefix = "";
        if ($bIncludePropertyName)
            $prefix = '"geometry": ';

        switch ($geomType) {
            case MgGeometryType::Point:
                {
                    $coord = $geom->GetCoordinate();
                    return $prefix.'{ "type": "Point", "coordinates": '.self::CoordToGeoJson($coord, $coord_precision)." }";
                }
            case MgGeometryType::LineString:
                {
                    $coords = $geom->GetCoordinates();
                    return $prefix.'{ "type": "LineString", "coordinates": '.self::CoordsToGeoJson($coords, $coord_precision)." }";
                }
            case MgGeometryType::Polygon:
                {
                    return $prefix.'{ "type": "Polygon", "coordinates": '.self::PolygonToGeoJson($geom, $coord_precision)." }";
                }
            case MgGeometryType::MultiPoint:
                {
                    $strCoords = "";
                    $count = $geom->GetCount();
                    $bFirst = true;
                    for ($i = 0; $i < $count; $i++) {
                        if (!$bFirst)
                            $strCoords .= ",";
                        $pt = $geom->GetPoint($i);
                        $coord = $pt->GetCoordinate();
                        $strCoords .= self::CoordToGeoJson($coord, $coord_precision);
                        $bFirst = false;
                    }
                    return $prefix.'{ "type": "MultiPoint", "coordinates": ['.$strCoords.'] }';
                }
            case MgGeometryType::MultiLineString:
                {
                    return $prefix.'{ "type": "MultiLineString", "coordinates": '.self::MultiLineStringToGeoJson($geom, $coord_precision)." }";
                }
            case MgGeometryType::MultiPolygon:
                {
                    $str = $prefix.'{ "type": "MultiPolygon", "coordinates": [';
                    $count = $geom->GetCount();
                    $bFirst = true;
                    for ($i = 0; $i < $count; $i++) {
                        if (!$bFirst)
                            $str .= ",";
                        $poly = $geom->GetPolygon($i);
                        $str .= self::PolygonToGeoJson($poly, $coord_precision);
                        $bFirst = false;
                    }
                    $str .= ']';
                    $str .= '}';
                    return $str;
                }
            case MgGeometryType::MultiGeometry:
                {
                    $str = $prefix.'{ "type": "GeometryCollection", "geometries": [';
                    $count = $geom->GetCount();
                    $bFirst = true;
                    for ($i = 0; $i < $count; $i++) {
                        if (!$bFirst)
                            $str .= ",";
                        $g = $geom->GetGeometry($i);
                        $str .= self::ToGeoJson($g, false, $coord_precision);
                        $bFirst = false;
                    }
                    $str .= ']';
                    $str .= '}';
                    return $str;
                }
            default:
                return '"geometry": null';
        }
    }

    public static function CoordToGeoJson(MgCoordinate $coord, /*php_int*/ $coord_precision = 7) {
        $x = $coord->GetX();
        $y = $coord->GetY();
        return "[".number_format($x, $coord_precision, '.', '').", ".number_format($y, $coord_precision, '.', '')."]";
    }

    public static function CoordsToGeoJson(MgCoordinateIterator $coords, /*php_int*/ $coord_precision = 7) {
        $str = '[';
        $first = true;
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $x = $coord->GetX();
            $y = $coord->GetY();
            $str .= "[".number_format($x, $coord_precision, '.', '').", ".number_format($y, $coord_precision, '.', '')."]";
            $first = false;
        }
        $str .= ']';
        return $str;
    }

    public static function MultiLineStringToGeoJson(MgMultiLineString $multiLineStr, /*php_int*/ $coord_precision = 7) {
        $str = '[';

        $count = $multiLineStr->GetCount();
        $firstLineStr = true;
        for ($i = 0; $i < $count; $i++) {
            if (!$firstLineStr)
                $str .= ",";
            $line = $multiLineStr->GetLineString($i);
            $coords = $line->GetCoordinates();
            $str .= self::CoordsToGeoJson($coords, $coord_precision);
            $firstLineStr = false;
        }

        $str .= ']';
        return $str;
    }

    public static function PolygonToGeoJson(MgPolygon $poly, /*php_int*/ $coord_precision = 7) {
        $str = '[';

        $extRing = $poly->GetExteriorRing();
        if ($extRing != null) {
            $coords = $extRing->GetCoordinates();
            $str .= self::CoordsToGeoJson($coords, $coord_precision);
        }
        $count = $poly->GetInteriorRingCount();
        if ($count > 0) {
            if ($extRing != null) {
                $str .= ",";
            }
            for ($i = 0; $i < $count; $i++) {
                $ring = $poly->GetInteriorRing($i);
                $coords = $ring->GetCoordinates();
                $str .= self::CoordsToGeoJson($coords, $coord_precision);
                if ($i < $count - 1) {
                    $str .= ",";
                }
            }
        }
        $str .= "]";
        return $str;
    }
}