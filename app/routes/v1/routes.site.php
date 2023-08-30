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

require_once dirname(__FILE__)."/../../controller/siteadmincontroller.php";
require_once dirname(__FILE__)."/../../util/utils.php";
require_once dirname(__FILE__)."/../../core/app.php";

/**
 *     @SWG\Get(
 *        path="/site/status.{type}",
 *        operationId="GetSiteStatus",
 *        summary="Gets the status of the current Site Server",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/status.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteStatus($type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/info.{type}",
 *        operationId="GetSiteInformation",
 *        summary="Gets the information of the current Site Server",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="response output format", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/info.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteInformation($type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/version.{type}",
 *        operationId="GetSiteVersion",
 *        summary="Gets the version of the current Site Server",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="response output format", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/version.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteVersion($type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/groups.{type}",
 *        operationId="EnumerateGroups",
 *        summary="Lists the current user groups",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/groups.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroups($type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/groups/{groupName}/users.{type}",
 *        operationId="EnumerateUsersForGroup",
 *        summary="Lists the users for the specified group",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="groupName", in="path", required=true, type="string", description="The group name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/groups/{groupName}/users.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $groupName = $args['groupName'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateUsersForGroup($groupName, $type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/user/{userName}/groups.{type}",
 *        operationId="EnumerateGroupsForUser",
 *        summary="Lists the groups for the specified user",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="userName", in="path", required=true, type="string", description="The user name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/user/{userName}/groups.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $userName = $args['userName'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroupsForUser($userName, $type);
    return $app->Done();
});
/**
 *     @SWG\Get(
 *        path="/site/user/{userName}/roles.{type}",
 *        operationId="EnumerateRolesForUser",
 *        summary="Lists the roles for the specified user",
 *        tags={"site"},
 *          @SWG\Parameter(name="session", in="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="userName", in="path", required=true, type="string", description="The user name"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"xml", "json"}),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
$app->get("/site/user/{userName}/roles.{type}", function($req, $resp, $args) {
    $type = $args['type'];
    $userName = $args['userName'];
    $app = $this->get("AppServices");
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateRolesForUser($userName, $type);
    return $app->Done();
});