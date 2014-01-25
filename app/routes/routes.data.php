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

require_once dirname(__FILE__)."/../controller/datacontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

$app->get("/data/:args+/config", function($args) use ($app, $container) {
    $ctrl = new MgDataController($app, $container);
    $ctrl->GetDataConfiguration($args);
});
$app->get("/data/:args+/:filename", function($args, $filename) use ($app, $container) {
    $tokens = explode(".", $filename);
    $ctrl = new MgDataController($app, $container);
    if (count($tokens) == 2) {
        if (strlen($tokens[0]) === 0) {
            $ctrl->HandleGet($args, $tokens[1]);
        } else {
            $ctrl->HandleGetSingle($args, $tokens[0], $tokens[1]);
        }
    } else {
        $ctrl->HandleGet($args, substr($filename, 1));
    }
});

?>