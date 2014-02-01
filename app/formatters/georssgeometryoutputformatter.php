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
require_once "gmlgeometryoutputformatter.php";

class MgGeoRssSimpleGeometryOutputFormatter extends MgGeometryOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputGeom($geom, $zval = null) {
        $geomType = $geom->GetGeometryType();
        switch($geomType) {
            case MgGeometryType::Point:
                return $this->OutputPoint($geom, $zval);
            case MgGeometryType::LineString:
                return $this->OutputLineString($geom, $zval);
            case MgGeometryType::Polygon:
                return $this->OutputPolygon($geom, $zval);
        }
    }

    private function OutputCoordinateIterator($iter, $zval) {
        $bFirstCoord = true;
        $output = "";
        while ($iter->MoveNext()) {
            $coord = $iter->GetCurrent();
            if ($coord != null) {
                if ($bFirstCoord)
                    $output .= $coord->GetY()." ".$coord->GetX();
                else
                    $output .= " ".$coord->GetY()." ".$coord->GetX();
            }
            $bFirstCoord = false;
        }
        return $output;
    }

    private function OutputLinearRing($ring, $zval) {
        $iter = $ring->GetCoordinates();
        return $this->OutputCoordinateIterator($iter, $zval);
    }

    private function OutputLineString($geom, $zval) {
        $output  = "<georss:line>";
        $iter = $geom->GetCoordinates();
        $output .= $this->OutputCoordinateIterator($iter, $zval);
        $output .= "</georss:line>";
        if ($zval != null)
            $output .= "<georss:elev>$zval</georss:elev>";
        return $output;
    }

    private function OutputPoint($geom, $zval) {
        $output  = "<georss:point>";
        $coord = $geom->GetCoordinate();
        if ($coord != null) {
            $output .= $coord->GetY()." ".$coord->GetX();
        }
        $output .= "</georss:point>";
        if ($zval != null)
            $output .= "<georss:elev>$zval</georss:elev>";
        return $output;
    }

    private function OutputPolygon($geom, $zval) {
        $output  = "<georss:polygon>";
        $output .= $this->OutputLinearRing($geom->GetExteriorRing(), $zval);
        $output .= "</georss:polygon>";
        if ($zval != null)
            $output .= "<georss:elev>$zval</georss:elev>";
        return $output;
    }
}

class MgGeoRssGmlGeometryOutputFormatter extends MgGmlGeometryOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputGeom($geom, $zval = null) {
        return "<georss:where>".parent::OutputGeom($geom, $zval)."</georss:where>";
    }
}

?>