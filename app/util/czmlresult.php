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
require_once "czmlwriter.php";

class MgCzmlResult
{
    private $featSvc;
    private $reader;
    private $limit;
    private $transform;
    private $writer;

    public function __construct($featSvc, $reader, $limit, $writer = NULL) {
        $this->featSvc = $featSvc;
        $this->reader = $reader;
        $this->limit = $limit;
        $this->transform = null;
        if ($writer != null)
            $this->writer = $writer;
        else
            $this->writer = new MgHttpChunkWriter();
    }

    public function CheckAndSetDownloadHeaders($app, $format) {
        $downloadFlag = $app->request->params("download");
        if ($downloadFlag === "1" || $downloadFlag === "true") {
            $fn = "download";
            if ($app->request->params("downloadname"))
                $fn = $app->request->params("downloadname");
            $ext = $format;
            if ($format == "geojson")
                $ext = "json";
            $this->writer->SetHeader("Content-Disposition", "attachment; filename=".$fn.".".$ext);
        }
    }

    public function SetTransform($tx) {
        $this->transform = $tx;
    }

    public function Output() {
        $read = 0;
        $agfRw = new MgAgfReaderWriter();
        $this->writer->SetHeader("Content-Type", MgMimeType::Json);
        $this->writer->StartChunking();
        $output = '['."\n";
        $output .= '{ "id": "document", "version": "1.0" }';
        $clsDef = $this->reader->GetClassDefinition();
        $clsIdProps = $clsDef->GetIdentityProperties();
        $idProp = NULL;
        if ($clsIdProps->GetCount() == 1) {
            $idProp = $clsIdProps->GetItem(0);
        }
        $propCount = $this->reader->GetPropertyCount();
        while ($this->reader->ReadNext()) {
            $read++;
            if ($this->limit > 0 && $read > $this->limit) {
                break;
            }
            $featCzml = MgCzmlWriter::FeatureToCzml($this->reader, $agfRw, $this->transform, $clsDef->GetDefaultGeometryPropertyName(), ($idProp != NULL ? $idProp->GetName() : NULL));
            if (strlen($featCzml) > 0) {
                $output .= ",";
                $output .= $featCzml;
                $this->writer->WriteChunk($output);
                $output = "";
            }
        }
        $output .= "]";
        $this->writer->WriteChunk($output);
        $this->writer->EndChunking();
        $this->reader->Close();
    }
}

?>