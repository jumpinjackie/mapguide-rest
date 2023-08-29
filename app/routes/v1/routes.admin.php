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

/*
$app->get("/admin/:args+", function($args) use ($app) {
    var_dump($args);
    var_dump($app->request->get());
});

//TODO: Add these to current unit (not integration) test suite

require_once dirname(__FILE__)."/../../core/bytereaderstreamadapter.php";
require_once dirname(__FILE__)."/../../core/stringcontentadapter.php";
require_once dirname(__FILE__)."/../../core/aggregatecontentadapter.php";

$app->get("/bytereadertest", function($req, $resp, $args) {
    $bs = new MgByteSource(dirname(__FILE__)."/../../../test/data/Parcels_Writeable.FeatureSource.xml");
    $bs->SetMimeType(MgMimeType::Xml);
    $br = $bs->GetReader();
    $adapter = new MgByteReaderStreamAdapter($br);
    return $resp->withHeader("Content-Type", MgMimeType::Xml)
                ->withBody($adapter);
});

$app->get("/stringadaptertest", function($req, $resp, $args) {
    $adapter = new StringContentAdapter(file_get_contents(dirname(__FILE__)."/../../../test/data/Parcels_Writeable.FeatureSource.xml"));
    return $resp->withHeader("Content-Type", MgMimeType::Xml)
                ->withBody($adapter);
});

$app->get("/aggregateadaptertest", function($req, $resp, $args) {
    $adapter = new AggregateContentAdapter([
        new StringContentAdapter("Hello "),
        new StringContentAdapter("World!")
    ]);
    return $resp->withHeader("Content-Type", "text/plain")
                ->withBody($adapter);
});
*/