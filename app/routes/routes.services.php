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

require_once dirname(__FILE__)."/../controller/coordinatesystemcontroller.php";
require_once dirname(__FILE__)."/../controller/resourceservicecontroller.php";
require_once dirname(__FILE__)."/../controller/mappingservicecontroller.php";

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="Additional Services",
 *      resourcePath="/services"
 * )
 */

$app->post("/services/copyresource", function() use ($app) {
    $ctrl = new MgResourceServiceController($app);
    $ctrl->CopyResource();
});
$app->post("/services/moveresource", function() use ($app) {
    $ctrl = new MgResourceServiceController($app);
    $ctrl->MoveResource();
});
$app->post("/services/transformcoords", function() use ($app) {
    $ctrl = new MgCoordinateSystemController($app);
    $ctrl->TransformCoordinates();
});
$app->post("/services/createmap", function() use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->CreateRuntimeMap("xml");
});
$app->post("/services/createmap.:format", function($format) use ($app) {
    $ctrl = new MgMappingServiceController($app);
    $ctrl->CreateRuntimeMap($format);
});

?>