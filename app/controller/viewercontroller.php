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

require_once "controller.php";

class MgViewerController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function LaunchAjaxViewer($resId) {
        $selfUrl = $this->app->config("SelfUrl");
        $this->app->redirect("$selfUrl/../mapviewerajax/?WEBLAYOUT=".$resId->ToString());
    }

    public function LaunchFusionViewer($resId, $template) {
        $selfUrl = $this->app->config("SelfUrl");
        $this->app->redirect("$selfUrl/../fusion/templates/mapguide/$template/index.html?ApplicationDefinition=".$resId->ToString());
    }
}

?>