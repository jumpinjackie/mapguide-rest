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

/**
 * @SWG\Resource(
 *      apiVersion="0.5",
 *      swaggerVersion="1.2",
 *      description="Site Service",
 *      resourcePath="/site"
 * )
 */

/**
 * @SWG\Api(
 *     path="/site/status",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSiteStatus",
 *        summary="Gets the status of the current Site Server",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/status", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteStatus("xml");
});

/**
 * @SWG\Api(
 *     path="/site/status.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSiteStatus",
 *        summary="Gets the status of the current Site Server",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/status.:format", function($format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteStatus($format);
});

/**
 * @SWG\Api(
 *     path="/site/version",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="GetSiteVersion",
 *        summary="Gets the version of the current Site Server",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/version", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->GetSiteVersion();
});
/**
 * @SWG\Api(
 *     path="/site/groups",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateGroups",
 *        summary="Lists the current user groups",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/groups", function() use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroups("xml");
});
/**
 * @SWG\Api(
 *     path="/site/groups.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateGroups",
 *        summary="Lists the current user groups",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/groups.:format", function($format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroups($format);
});
/**
 * @SWG\Api(
 *     path="/site/groups/{groupName}/users",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateUsersForGroup",
 *        summary="Lists the users for the specified group",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The group name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/groups/:groupName/users", function($groupName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateUsersForGroup($groupName, "xml");
});
/**
 * @SWG\Api(
 *     path="/site/groups/{groupName}/users.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateUsersForGroup",
 *        summary="Lists the users for the specified group",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="groupName", paramType="path", required=true, type="string", description="The group name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/groups/:groupName/users.:format", function($groupName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateUsersForGroup($groupName, $format);
});
/**
 * @SWG\Api(
 *     path="/site/user/{userName}/groups",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateGroupsForUser",
 *        summary="Lists the groups for the specified user",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="userName", paramType="path", required=true, type="string", description="The user name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/user/:userName/groups", function($userName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroupsForUser($userName, "xml");
});
/**
 * @SWG\Api(
 *     path="/site/user/{userName}/groups.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateGroupsForUser",
 *        summary="Lists the groups for the specified user",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="userName", paramType="path", required=true, type="string", description="The user name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/user/:userName/groups.:format", function($userName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateGroupsForUser($userName, $format);
});
/**
 * @SWG\Api(
 *     path="/site/user/{userName}/roles",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateRolesForUser",
 *        summary="Lists the roles for the specified user",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="userName", paramType="path", required=true, type="string", description="The user name")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/user/:userName/roles", function($userName) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateRolesForUser($userName, "xml");
});
/**
 * @SWG\Api(
 *     path="/site/user/{userName}/roles.{type}",
 *     @SWG\Operation(
 *        method="GET",
 *        nickname="EnumerateRolesForUser",
 *        summary="Lists the roles for the specified user",
 *        @SWG\parameters(
 *          @SWG\parameter(name="session", paramType="query", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\parameter(name="userName", paramType="path", required=true, type="string", description="The user name"),
 *          @SWG\parameter(name="type", paramType="path", required=true, type="string", description="xml or json", enum="['xml','json']")
 *        ),
 *        @SWG\ResponseMessage(code=401, message="Session ID or MapGuide credentials not specified"),
 *        @SWG\ResponseMessage(code=500, message="An error occurred during the operation")
 *     )
 *   )
 */
$app->get("/site/user/:userName/roles.:format", function($userName, $format) use ($app) {
    $ctrl = new MgSiteAdminController($app);
    $ctrl->EnumerateRolesForUser($userName, $format);
});

?>