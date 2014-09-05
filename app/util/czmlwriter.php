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

class MgCzmlWriter
{
    public static function FeatureToCzml($reader, $agfRw, $transform, $geometryName, $idName = NULL) {
        $idIndex = -1;
        if ($idName != NULL)
            $idIndex = $reader->GetPropertyIndex($idName);
        $output = "{";
        //Write ID
        $idVal = "";
        if ($idIndex >= 0 && !$reader->IsNull($idIndex)) {
            $propType = $reader->GetPropertyType($idIndex);
            switch($propType) {
                case MgPropertyType::DateTime:
                    $dt = $reader->GetDateTime($idIndex);
                    $idVal = '"'.$dt->ToString().'"';
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
                    $idVal = '"'.MgUtils::EscapeJsonString($reader->GetString($idIndex)).'"';
                    break;
            }
        } else {
            $idVal = '"'.uniqid().'"';
        }
        $output .= '"id": '.$idVal;
        //Geometry
        if (!$reader->IsNull($geometryName)) {
            $agf = $reader->GetGeometry($geometryName);
            $geom = $agfRw->Read($agf, $transform);
            $output .= ",";
            $output .= self::GeometryToCzml($geom);
        }
        $output .= "}";
        return $output;
    }

    const POLYGON_STYLE = '{ "material": { "solidColor": { "color": { "rgba": [ 255, 127, 127, 153 ] } } } }';

    private static function GeometryToCzml($geom) {
        $geomType = $geom->GetGeometryType();
        //TODO: Convert all the geometry types.
        //TODO: Translate Layer Definition styles to CZML styles
        switch ($geomType) {
            case MgGeometryType::Point:
                {
                    $coord = $geom->GetCoordinate();
                    $fragment  = '"point": { "color": { "rgba": [0, 255, 0, 255] }, "pixelSize": { "number": 3.0 } }, "position": { "cartographicDegrees": '.self::CoordToCzml($coord)." }";
                    //$fragment .= '"point": {}';
                    return $fragment;
                }
            case MgGeometryType::LineString:
                {
                    $coords = $geom->GetCoordinates();
                    return '"polyline": { "color": { "rgba": [0, 255, 255, 255] }, "width": 2.0 }, "position": '.self::LineStringToCzml($coords);
                }
            case MgGeometryType::Polygon:
                {
                    return '"polygon": { "material": { "solidColor": { "color": { "rgba": [ 255, 127, 127, 153 ] } } },"positions": '.self::PolygonToCzml($geom).'}';
                }
            default:
                return '"position": null, "_error": "Unsupported geometry type '.$geomType.'"';
        }
    }

    private static function CoordToCzml($coord, $enclose = true) {
        //return "[".$coord->GetX().",".$coord->GetY().",".$coord->GetZ()."]";
        $str = "";
        if ($enclose)
            $str .= "[";
        $str .= $coord->GetX().",".$coord->GetY().",0.0";
        if ($enclose)
            $str .= "]";
        return $str;
    }

    private static function LineStringToCzml($coords) {
        $str = '{ "cartographicDegrees": [';
        $first = true;
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $str .= self::CoordToCzml($coord, false);
            $first = false;
        }
        $str .= ']}';
        return $str;
    }

    private static function PolygonToCzml($geom) {
        $str = '{ "cartographicDegrees": [';
        $first = true;
        //TODO: Only handles exterior ring. Can CZML support polygons with holes?
        $extRing = $geom->GetExteriorRing();
        $coords = $extRing->GetCoordinates();
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $str .= self::CoordToCzml($coord, false);
            $first = false;
        }
        $str .= ']}';
        return $str;
    }
}

?>