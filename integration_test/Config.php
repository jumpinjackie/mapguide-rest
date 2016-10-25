<?php

//
//  Copyright (C) 2016 by Jackie Ng
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

class Configuration {
    const MIME_XML = "text/xml";
    const MIME_JSON = "application/json";
    const MIME_HTML = "text/html";
    const MIME_PNG = "image/png";
    const MIME_JPEG = "image/jpeg";
    const MIME_GIF = "image/gif";
    public static function getRestUrl($relPart) {
        $root = "http://localhost/mapguide/rest";
        if (array_key_exists("MG_REST_ROOT_URL", $_SERVER)) {
            $root = $_SERVER["MG_REST_ROOT_URL"];
        }
        return $root . str_replace(" ", "%20", $relPart);
    }
    public static function getAnonLogin() {
        $resp = new stdClass();
        $resp->user = "Anonymous";
        $resp->pass = "";
        return $resp;
    }
    public static function getAdminLogin() {
        $resp = new stdClass();
        $resp->user = "Administrator";
        $resp->pass = "admin";
        return $resp;
    }
    public static function getAuthorLogin() {
        $resp = new stdClass();
        $resp->user = "Author";
        $resp->pass = "author";
        return $resp;
    }
    public static function getWfsLogin() {
        $resp = new stdClass();
        $resp->user = "WfsUser";
        $resp->pass = "wfs";
        return $resp;
    }
    public static function getWmsLogin() {
        $resp = new stdClass();
        $resp->user = "WmsUser";
        $resp->pass = "wms";
        return $resp;
    }
    public static function getUser1Login() {
        $resp = new stdClass();
        $resp->user = "User1";
        $resp->pass = "user1";
        return $resp;
    }
    public static function getUser2Login() {
        $resp = new stdClass();
        $resp->user = "User2";
        $resp->pass = "user2";
        return $resp;
    }
}

?>