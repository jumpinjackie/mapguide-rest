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

require_once "geometryoutputformatter.php";

class MgGmlGeometryOutputFormatter extends MgGeometryOutputFormatter
{
    public function __construct() {
        parent::__construct();
    }

    protected function OutputGeom($geom, $reader) {
        $zval = null;
        $geomType = $geom->GetGeometryType();
        switch($geomType) {
            case MgGeometryType::Point:
                return $this->OutputPoint($geom, $zval);
            case MgGeometryType::LineString:
                return $this->OutputLineString($geom, $zval);
            case MgGeometryType::Polygon:
                return $this->OutputPolygon($geom, $zval);
            case MgGeometryType::MultiPoint:
                return $this->OutputMultiPoint($geom, $zval);
            case MgGeometryType::MultiLineString:
                return $this->OutputMultiLineString($geom, $zval);
            case MgGeometryType::MultiPolygon:
                return $this->OutputMultiPolygon($geom, $zval);
        }
    }

    private function OutputCoordinateIterator($iter, $zval) {
        $bFirstCoord = true;
        $output = "<gml:posList>";
        while ($iter->MoveNext()) {
            $coord = $iter->GetCurrent();
            if ($coord != null) {
                if ($bFirstCoord)
                    $output .= " ";
                if ($zval == null)
                    $output .= $coord->GetX()." ".$coord->GetY();
                else
                    $output .= $coord->GetX()." ".$coord->GetY()." ".$zval;
            }
            $bFirstCoord = false;
        }
        $output .= "</gml:posList>";
        return $output;
    }

    private function OutputLinearRing($ring, $zval) {
        $output  = "<gml:LinearRing>";
        $iter = $ring->GetCoordinates();
        $output .= $this->OutputCoordinateIterator($iter, $zval);
        $output .= "</gml:LinearRing>";
        return $output;
    }

    private function OutputLineString($geom, $zval) {
        $output  = "<gml:LineString>";
        $iter = $geom->GetCoordinates();
        $output .= $this->OutputCoordinateIterator($iter, $zval);
        $output .= "</gml:LineString>";
        return $output;
    }

    private function OutputPoint($geom, $zval) {
        $output  = "<gml:Point>";
        $output .= "<gml:pos>";
        $coord = $geom->GetCoordinate();
        if ($coord != null) {
            $output .= $coord->GetX()." ".$coord->GetY();
        }
        $output .= "</gml:pos>";
        $output .= "</gml:Point>";
        return $output;
    }

    private function OutputMultiPoint($geom, $zval) {
        $output = "<gml:MultiPoint>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= "<gml:pointMember>";
            $output .= $this->OutputPoint($geom->GetPoint($i), $extrude, $zval);
            $output .= "</gml:pointMember>";
        }
        $output .= "</gml:MultiPoint>";
        return $output;
    }

    private function OutputMultiLineString($geom, $zval) {
        $output = "<gml:MultiLineString>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= "<gml:lineStringMember>";
            $output .= $this->OutputLineString($geom->GetLineString($i), $extrude, $zval);
            $output .= "</gml:lineStringMember>";
        }
        $output .= "</gml:MultiLineString>";
        return $output;
    }

    private function OutputMultiPolygon($geom, $zval) {
        $output = "<gml:MultiPolygon>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= "<gml:polygonMember>";
            $output .= $this->OutputPolygon($geom->GetPolygon($i), $extrude, $zval);
            $output .= "</gml:polygonMember>";
        }
        $output .= "</gml:MultiPolygon>";
        return $output;
    }

    private function OutputPolygon($geom, $zval) {
        $output  = "<gml:Polygon>";
        $output .= "<gml:exterior>";
        $output .= $this->OutputLinearRing($geom->GetExteriorRing(), $zval);
        $output .= "</gml:exterior>";
        $output .= "</gml:Polygon>";
        return $output;
    }
}