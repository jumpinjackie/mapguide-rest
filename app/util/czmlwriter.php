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
    private static function SingleFeatureToCzml($idVal, $reader, $geomCzml) {
        $output = "{";
        $output .= '"id": "'.$idVal.'"';

        $ttIndex = -1;
        $hlIndex = -1;
        $elevIndex = -1;
        try {
            $ttIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_TOOLTIP);
        } catch (MgException $ex) { }
        try {
            $hlIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_HYPERLINK);
        } catch (MgException $ex) { }
        try {
            $elevIndex = $reader->GetPropertyIndex(MgRestConstants::PROP_Z_EXTRUSION);
        } catch (MgException $ex) { }

        if ($ttIndex >= 0) {
            $output .= ', "description": "'.MgUtils::EscapeJsonString($reader->GetString($ttIndex)).'"';
        }

        $output .= ",";
        
        $output .= $geomCzml;

        $output .= "}";
        return $output;
    }

    public static function FeatureToCzml($reader, $agfRw, $transform, $geometryName, $idName = NULL) {
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
                        $idVal = MgUtils::EscapeJsonString($reader->GetString($idIndex));
                        break;
                }
            } else {
                $idVal = uniqid();
            }

            switch ($geom->GetGeometryType()) {
                case MgGeometryType::Point:
                case MgGeometryType::LineString:
                case MgGeometryType::Polygon:
                    {
                        $geomCzml = self::GeometryToCzml($geom);
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
                            $geomCzml = self::GeometryToCzml($lineStr);
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

    private static function GeometryToCzml($geom) {
        $geomType = $geom->GetGeometryType();
        //TODO: Convert all the geometry types.
        //TODO: Translate Layer Definition styles to CZML styles
        switch ($geomType) {
            case MgGeometryType::Point:
                {
                    $coord = $geom->GetCoordinate();
                    $fragment  = '"point": { "color": { "rgba": [0, 255, 0, 255] }, "pixelSize": { "number": 3.0 } }, "position": { "cartographicDegrees": '.self::CoordToCzml($coord)." }";
                    return $fragment;
                }
            case MgGeometryType::LineString:
                {
                    $coords = $geom->GetCoordinates();
                    $posCzml = self::LineStringToCzml($coords);
                    if ($posCzml != null)
                        return '"polyline": { "material": { "solidColor": { "color": { "rgba": [ 0, 255, 255, 255 ] } } },"positions": '.$posCzml.'}';
                    else
                        return null;
                }
            case MgGeometryType::Polygon:
                {
                    $fragment = '"polygon": { ';
                    $fragment .= '"outline": { "boolean": true }, "outlineColor": { "rgba": [ 0, 0, 0, 255 ] }, ';
                    $fragment .= '"material": { "solidColor": { "color": { "rgba": [ 255, 127, 127, 153 ] } } },"positions": '.self::PolygonToCzml($geom).'}';
                    return $fragment;
                }
            default:
                return null;
        }
    }

    private static function CoordToCzml($coord, $enclose = true) {
        $str = "";
        if ($enclose)
            $str .= "[";
        $str .= $coord->GetX().",".$coord->GetY().",0.0";
        if ($enclose)
            $str .= "]";
        return $str;
    }

    private static function LineStringToCzml($coords) {
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
            $coordCzml = self::CoordToCzml($coord, false);
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