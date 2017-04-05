<?php

//
//  Copyright (C) 2017 by Jackie Ng
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

require_once "controller.php";

class MgProcessingController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function Buffer() {
        $geometry = $this->GetRequestParameter("geometry");
        $coordsys = $this->GetRequestParameter("coordsys");
        $distance = $this->GetRequestParameter("distance");
        $format = $this->ValidateValueInDomain($this->GetRequestParameter("format"), array("wkt", "geojson"));
        $units = $this->ValidateValueInDomain($this->GetRequestParameter("units", "m"), array("m", "km", "mi", "ft"));
        $transformto = $this->GetRequestParameter("transformto");

        try {
            $wktRw = new MgWktReaderWriter();
            $geom = $wktRw->Read($geometry);

            $csFactory = new MgCoordinateSystemFactory();
            $cs = $csFactory->CreateFromCode($coordsys);
            $measure = $cs->GetMeasure();

            $dist = doubleval($distance);
            // convert distance to meters
            switch ($units) {
                case "mi": //Miles
                    $dist *= 1609.35;
                    break;
                case "km": //Kilometers
                    $dist *= 1000;
                    break;
                case "ft": //Feet
                    $dist *= .30480;
                    break;
            }
            $distU = $cs->ConvertMetersToCoordinateSystemUnits($dist);
            $buffered = $geom->Buffer($distU, $measure);

            $oGeom = $buffered;
            if ($transformto != "") {
                $csDest = $csFactory->CreateFromCode($transformto);
                $xform = $csFactory->GetTransform($cs, $csDest);
                $oGeom = $buffered->Transform($xform);
            }

            $this->app->response->header("Content-Type", MgMimeType::Json);
            switch ($format) {
                case "wkt":
                    $resp = '{"type": "wkt", "result": "'.$wktRw->Write($oGeom).'"}';
                    $this->app->response->write($resp);
                    break;
                case "geojson":
                    $resp = '{"type": "geojson", "result": { "type": "Feature", "id": "'.uniqid().'", '.MgGeoJsonWriter::ToGeoJson($oGeom).'} }';
                    $this->app->response->write($resp);
                    break;
            }
        } catch (MgException $ex) {
            $this->OnException($ex, MgMimeType::Json);
        }
    }
}