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

abstract class MgGeometryOutputFormatter
{
    private $agfRw;

    protected function __construct() {
        $this->agfRw = new MgAgfReaderWriter();
    }

    protected abstract function OutputGeom($geom);
    
    public function Output($reader, $geomName, $transform) {
        $output = "";
        try {
            if (!$reader->IsNull($geomName)) {
                $agf = $reader->GetGeometry($geomName);
                if ($transform != null)
                    $geom = $this->agfRw->Read($agf, $transform);
                else
                    $geom = $this->agfRw->Read($agf);

                if ($geom != null)
                    $output = $this->OutputGeom($geom);
            }
        } catch (MgException $ex) {
        }
        return $output;
    }
};

?>