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

class MgGeoJsonWriter
{
    public static function ToGeoJson($geom) {
        $geomType = $geom->GetGeometryType();
        //TODO: Convert all the geometry types. Right now, we're converting the same subset as GeoREST
        switch ($geomType) {
            case MgGeometryType::Point:
                {
                    $coord = $geom->GetCoordinate();
                    return '"geometry": { "type": "Point", "coordinates": '.MgGeoJsonWriter::CoordToGeoJson($coord)." }";
                }
            case MgGeometryType::LineString:
                {
                    $coords = $geom->GetCoordinates();
                    return '"geometry": { "type": "LineString", "coordinates": '.MgGeoJsonWriter::CoordsToGeoJson($coords)." }";
                }
            case MgGeometryType::Polygon:
                {
                    return '"geometry": { "type": "Polygon", "coordinates": '.MgGeoJsonWriter::PolygonToGeoJson($geom)." }";
                }
            case MgGeometryType::MultiLineString:
                {
                    return '"geometry": { "type": "MultiLineString", "coordinates": '.MgGeoJsonWriter::MultiLineStringToGeoJson($geom)." }";
                }
            default:
                return '"geometry": null';
        }
    }

    public static function CoordToGeoJson($coord) {
        $x = $coord->GetX();
        $y = $coord->GetY();
        return "[$x, $y]";
    }

    public static function CoordsToGeoJson($coords) {
        $str = '[';
        $first = true;
        while ($coords->MoveNext()) {
            if (!$first)
                $str .= ",";
            $coord = $coords->GetCurrent();
            $x = $coord->GetX();
            $y = $coord->GetY();
            $str .= "[$x, $y]";
            $first = false;
        }
        $str .= ']';
        return $str;
    }

    public static function MultiLineStringToGeoJson($multiLineStr) {
        $str = '[';

        $count = $multiLineStr->GetCount();
        $firstLineStr = true;
        for ($i = 0; $i < $count; $i++) {
            if (!$firstLineStr)
                $str .= ",";
            $line = $multiLineStr->GetLineString($i);
            $coords = $line->GetCoordinates();
            $str .= MgGeoJsonWriter::CoordsToGeoJson($coords);
            $firstLineStr = false;
        }

        $str .= ']';
        return $str;
    }

    public static function PolygonToGeoJson($poly) {
        $str = '[';

        $extRing = $poly->GetExteriorRing();
        if ($extRing != null) {
            $coords = $extRing->GetCoordinates();
            $str .= MgGeoJsonWriter::CoordsToGeoJson($coords);
        }
        $count = $poly->GetInteriorRingCount();
        if ($count > 0) {
            if ($extRing != null) {
                $str .= ",";
            }
            for ($i = 0; $i < $count; $i++) {
                $ring = $poly->GetInteriorRing($i);
                $coords = $ring->GetCoordinates();
                $str .= MgGeoJsonWriter::CoordsToGeoJson($coords);
                if ($i < $count - 1) {
                    $str .= ",";
                }
            }
        }
        $str .= "]";
        return $str;
    }
}

?>