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

class MgWktGeometryOutputFormatter extends MgGeometryOutputFormatter
{
    private $agfRw;
    private $wktRw;

    public function __construct() {
        parent::__construct();
        $this->agfRw = new MgAgfReaderWriter();
        $this->wktRw = new MgWktReaderWriter();
    }
    
    public function Output($reader, $geomName, $transform) {
        $output = "";
        try {
            if (!$reader->IsNull($geomName)) {
                if ($transform != null)
                    $agf = $reader->GetGeometry($geomName, $transform);
                else
                    $agf = $reader->GetGeometry($geomName);
                $geom = $this->agfRw->Read($agf);
                $output = $this->wktRw->Write($geom);
            }
        } catch (MgException $ex) {
        }
        return $output;
    }
}

?>