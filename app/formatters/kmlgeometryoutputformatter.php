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
require_once dirname(__FILE__)."/../constants.php";

class MgKmlGeometryOutputFormatter extends MgGeometryOutputFormatter
{
    public function __construct() {
        parent::__construct();
    }

    protected function OutputGeom($geom, $reader) {
        $extrude = 0;
        $zval = null;
        //Check if special extrusion properties exist
        try {
            if ($reader->GetPropertyIndex(MgRestConstants::PROP_Z_EXTRUSION) >= 0) {
                switch ($reader->GetPropertyType(MgRestConstants::PROP_Z_EXTRUSION)) {
                    case MgPropertyType::Int16:
                        $extrude = 1;
                        $zval = $reader->GetInt16(MgRestConstants::PROP_Z_EXTRUSION);
                        break;
                    case MgPropertyType::Int32:
                        $extrude = 1;
                        $zval = $reader->GetInt32(MgRestConstants::PROP_Z_EXTRUSION);
                        break;
                    case MgPropertyType::Int64:
                        $extrude = 1;
                        $zval = $reader->GetInt64(MgRestConstants::PROP_Z_EXTRUSION);
                        break;
                    case MgPropertyType::Double:
                        $extrude = 1;
                        $zval = $reader->GetDouble(MgRestConstants::PROP_Z_EXTRUSION);
                        break;
                    case MgPropertyType::Single:
                        $extrude = 1;
                        $zval = $reader->GetSingle(MgRestConstants::PROP_Z_EXTRUSION);
                        break;
                }
            }
        } catch (MgException $ex) {
            $extrude = 0;
            $zval = null;
        }
        $geomType = $geom->GetGeometryType();
        switch($geomType) {
            case MgGeometryType::Point:
                return $this->OutputPoint($geom, $extrude, $zval);
            case MgGeometryType::LineString:
                return $this->OutputLineString($geom, $extrude, $zval);
            case MgGeometryType::Polygon:
                return $this->OutputPolygon($geom, $extrude, $zval);
            case MgGeometryType::MultiPoint:
                return $this->OutputMultiPoint($geom, $extrude, $zval);
            case MgGeometryType::MultiLineString:
                return $this->OutputMultiLineString($geom, $extrude, $zval);
            case MgGeometryType::MultiPolygon:
                return $this->OutputMultiPolygon($geom, $extrude, $zval);
        }
    }

    private function OutputCoordinateIterator($iter, $zval) {
        $output = "<coordinates>";
        while ($iter->MoveNext()) {
            $coord = $iter->GetCurrent();
            if ($coord != null) {
                if ($zval == null)
                    $output .= $coord->GetX().",".$coord->GetY()." ";
                else
                    $output .= $coord->GetX().",".$coord->GetY().",".$zval." ";
            }
        }
        $output .= "</coordinates>";
        return $output;
    }

    private function OutputLinearRing($ring, $zval) {
        $output = "<LinearRing>";
        $iter = $ring->GetCoordinates();
        $output .= $this->OutputCoordinateIterator($iter, $zval);
        $output .= "</LinearRing>";
        return $output;
    }

    private function OutputLineString($geom, $extrude, $zval) {
        $output  = "<LineString>";
        $output .= "<extrude>$extrude</extrude>";
        $output .= "<altitudeMode>relativeToGround</altitudeMode>";
        $iter = $geom->GetCoordinates();
        $output .= $this->OutputCoordinateIterator($iter, $zval);
        $output .= "</LineString>";
        return $output;
    }

    private function OutputPoint($geom, $extrude, $zval) {
        $output  = "<Point>";
        $output .= "<extrude>$extrude</extrude>";
        $output .= "<altitudeMode>relativeToGround</altitudeMode>";
        $output .= "<coordinates>";
        $coord = $geom->GetCoordinate();
        if ($coord != null) {
            if ($zval != null)
                $output .= $coord->GetX().",".$coord->GetY().",".$zval." ";
            else
                $output .= $coord->GetX().",".$coord->GetY()." ";
        }
        $output .= "</coordinates>";
        $output .= "</Point>";
        return $output;
    }

    private function OutputMultiPoint($geom, $extrude, $zval) {
        $output = "<MultiGeometry>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= $this->OutputPoint($geom->GetPoint($i), $extrude, $zval);
        }
        $output .= "</MultiGeometry>";
        return $output;
    }

    private function OutputMultiLineString($geom, $extrude, $zval) {
        $output = "<MultiGeometry>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= $this->OutputLineString($geom->GetLineString($i), $extrude, $zval);
        }
        $output .= "</MultiGeometry>";
        return $output;
    }

    private function OutputMultiPolygon($geom, $extrude, $zval) {
        $output = "<MultiGeometry>";
        $geomCount = $geom->GetCount();
        for ($i = 0; $i < $geomCount; $i++) {
            $output .= $this->OutputPolygon($geom->GetPolygon($i), $extrude, $zval);
        }
        $output .= "</MultiGeometry>";
        return $output;
    }

    private function OutputPolygon($geom, $extrude, $zval) {
        $output  = "<Polygon>";
        $output .= "<extrude>$extrude</extrude>";
        $output .= "<altitudeMode>relativeToGround</altitudeMode>";
        $output .= "<outerBoundaryIs>";
        $output .= $this->OutputLinearRing($geom->GetExteriorRing(), $zval);
        $output .= "</outerBoundaryIs>";
        $output .= "</Polygon>";
        return $output;
    }
}