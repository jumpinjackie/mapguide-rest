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
    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    const OP_DIFFERENCE = 1;
    const OP_INTERSECTION = 2;
    const OP_SYMMETRICDIFFERENCE = 3;
    const OP_UNION = 4;

    const P_CONTAINS = "contains";
    const P_CROSSES = "crosses";
    const P_DISJOINT = "disjoint";
    const P_EQUALS = "equals";
    const P_INTERSECTS = "intersects";
    const P_OVERLAPS = "overlaps";
    const P_TOUCHES = "touches";
    const P_WITHIN = "within";

    private function GeometryPredicate(/*php_string*/ $wktA, /*php_string*/ $wktB, /*php_string*/ $op) {
        $wktRw = new MgWktReaderWriter();
        $geomA = $wktRw->Read($wktA);
        $geomB = $wktRw->Read($wktB);

        $result = NULL;

        switch ($op) {
            case self::P_CONTAINS:
                $result = $geomA->Contains($geomB);
                break;
            case self::P_CROSSES:
                $result = $geomA->Crosses($geomB);
                break;
            case self::P_DISJOINT:
                $result = $geomA->Disjoint($geomB);
                break;
            case self::P_EQUALS:
                $result = $geomA->Equals($geomB);
                break;
            case self::P_INTERSECTS:
                $result = $geomA->Intersects($geomB);
                break;
            case self::P_OVERLAPS:
                $result = $geomA->Overlaps($geomB);
                break;
            case self::P_TOUCHES:
                $result = $geomA->Touches($geomB);
                break;
            case self::P_WITHIN:
                $result = $geomA->Within($geomB);
                break;
        }

        $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        $body = MgBoxedValue::Boolean($result, "json");
        /*
        $o = json_decode($body);
        $o->op = $op;
        $body = json_encode($o);
        */
        $this->app->WriteResponseContent($body);
    }

    private function GeometryOperation(/*php_string*/ $wktA, /*php_string*/ $wktB, /*php_string*/ $op, /*php_string*/ $transformto, /*php_string*/ $format) {
        $wktRw = new MgWktReaderWriter();
        $geomA = $wktRw->Read($wktA);
        $geomB = $wktRw->Read($wktB);

        $result = NULL;

        switch ($op) {
            case self::OP_DIFFERENCE:
                $result = $geomA->Difference($geomB);
                break;
            case self::OP_INTERSECTION:
                $result = $geomA->Intersection($geomB);
                break;
            case self::OP_SYMMETRICDIFFERENCE:
                $result = $geomA->SymetricDifference($geomB);
                break;
            case self::OP_UNION:
                $result = $geomA->Union($geomB);
                break;
        }

        if ($result != NULL) {
            $csFactory = new MgCoordinateSystemFactory();
            $this->OutputGeom($result, $transformto, $wktRw, $csFactory, $format);
        }
    }

    private function OutputGeom(MgGeometry $oGeom, /*php_string*/ $transformto, MgWktReaderWriter $wktRw, MgCoordinateSystemFactory $csFactory, /*php_string*/ $format) {
        if ($transformto != "") {
            $csDest = $csFactory->CreateFromCode($transformto);
            $xform = $csFactory->GetTransform($cs, $csDest);
            $oGeom = $buffered->Transform($xform);
        }

        $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
        switch ($format) {
            case "wkt":
                $resp = '{"type": "wkt", "result": "'.$wktRw->Write($oGeom).'"}';
                $this->app->WriteResponseContent($resp);
                break;
            case "geojson":
                $resp = '{"type": "geojson", "result": { "type": "Feature", "id": "'.uniqid().'", '.MgReaderToGeoJsonWriter::ToGeoJson($oGeom).'} }';
                $this->app->WriteResponseContent($resp);
                break;
        }
    }

    public function Difference() {
        $wktA = $this->app->GetRequestParameter("geometry_a");
        $wktB = $this->app->GetRequestParameter("geometry_b");
        $format = $this->ValidateValueInDomain($this->app->GetRequestParameter("format"), array("wkt", "geojson"));
        $transformto = $this->app->GetRequestParameter("transformto");

        $this->GeometryOperation($wktA, $wktB, self::OP_DIFFERENCE, $transformto, $format);
    }

    public function Intersection() {
        $wktA = $this->app->GetRequestParameter("geometry_a");
        $wktB = $this->app->GetRequestParameter("geometry_b");
        $format = $this->ValidateValueInDomain($this->app->GetRequestParameter("format"), array("wkt", "geojson"));
        $transformto = $this->app->GetRequestParameter("transformto");

        $this->GeometryOperation($wktA, $wktB, self::OP_INTERSECTION, $transformto, $format);
    }

    public function SymmetricDifference() {
        $wktA = $this->app->GetRequestParameter("geometry_a");
        $wktB = $this->app->GetRequestParameter("geometry_b");
        $format = $this->ValidateValueInDomain($this->app->GetRequestParameter("format"), array("wkt", "geojson"));
        $transformto = $this->app->GetRequestParameter("transformto");

        $this->GeometryOperation($wktA, $wktB, self::OP_SYMMETRICDIFFERENCE, $transformto, $format);
    }

    public function Union() {
        $wktA = $this->app->GetRequestParameter("geometry_a");
        $wktB = $this->app->GetRequestParameter("geometry_b");
        $format = $this->ValidateValueInDomain($this->app->GetRequestParameter("format"), array("wkt", "geojson"));
        $transformto = $this->app->GetRequestParameter("transformto");

        $this->GeometryOperation($wktA, $wktB, self::OP_UNION, $transformto, $format);
    }

    public function Buffer() {
        $geometry = $this->app->GetRequestParameter("geometry");
        $coordsys = $this->app->GetRequestParameter("coordsys");
        $distance = $this->app->GetRequestParameter("distance");
        $format = $this->ValidateValueInDomain($this->app->GetRequestParameter("format"), array("wkt", "geojson"));
        $units = $this->ValidateValueInDomain($this->app->GetRequestParameter("units", "m"), array("m", "km", "mi", "ft"));
        $transformto = $this->app->GetRequestParameter("transformto");

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

            $this->OutputGeom($buffered, $transformto, $wktRw, $csFactory, $format);
        } catch (MgException $ex) {
            $this->OnException($ex, MgMimeType::Json);
        }
    }

    public function SpatialPredicate() {
        $wktA = $this->app->GetRequestParameter("geometry_a");
        $wktB = $this->app->GetRequestParameter("geometry_b");
        $op = $this->ValidateValueInDomain($this->app->GetRequestParameter("operator"), array("contains", "crosses", "disjoint", "equals", "intersects", "overlaps", "touches", "within"));

        $this->GeometryPredicate($wktA, $wktB, $op);
    }
}