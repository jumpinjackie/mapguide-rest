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

require_once dirname(__FILE__)."/../controller/siteadmincontroller.php";
require_once dirname(__FILE__)."/../util/utils.php";

$app->get("/site/status", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteStatus("xml");
});
$app->get("/site/status.:format", function($format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteStatus($format);
});
$app->get("/site/version", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteVersion();
});
$app->get("/site/groups", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroups("xml");
});
$app->get("/site/groups.:format", function($format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroups($format);
});
$app->get("/site/groups/:groupName/users", function($groupName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateUsersForGroup($groupName, "xml");
});
$app->get("/site/groups/:groupName/users.:format", function($groupName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateUsersForGroup($groupName, $format);
});
$app->get("/site/user/:userName/groups", function($userName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroupsForUser($userName, "xml");
});
$app->get("/site/user/:userName/groups.:format", function($userName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroupsForUser($userName, $format);
});
$app->get("/site/user/:userName/roles", function($userName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateRolesForUser($userName, "xml");
});
$app->get("/site/user/:userName/roles.:format", function($userName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateRolesForUser($userName, $format);
});

?>