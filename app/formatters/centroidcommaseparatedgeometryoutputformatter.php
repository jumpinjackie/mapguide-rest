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

class MgCentroidCommaSeparatedGeometryOutputFormatter extends MgGeometryOutputFormatter
{
    public function __construct() {
        parent::__construct();
    }

    protected function OutputGeom($geom) {
        $pt = $geom->GetCentroid();
        $coord = $pt->GetCoordinate();
        return $coord->GetX().",".$coord->GetY();
    }
}

?>