<?php

//
//  Copyright (C) 2023 by Jackie Ng
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

require_once dirname(__FILE__)."/interfaces.php";

class AppServices implements IAppServices {
    private $app;
    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    public /* internal */ function GetLocalizedText(/*php_string*/ $key) {
        $this->app->localizer->getText($key);
    }

    public /* internal */ function GetConfig(/*php_string*/ $name) {
        return $this->app->config($name);
    }

    public /* internal */ function GetRequestPathInfo() {
        return $this->app->request->getPathInfo();
    }

    public /* internal */ function GetRequestHeader(/*php_string*/ $name) {
        return $this->app->request->headers->get($name);
    }

    public /* internal */ function GetRequestBody() {
        return $this->app->request->getBody();
    }

    public /* internal */ function GetAllRequestParams() {
        return $this->app->request->params();
    }

    /**
     * Method: GetRequestParameter
     *
     * Convenience method to get a parameter by name. This method tries to get the named parameter:
     *  1. As-is
     *  2. As upper-case
     *  3. As lower-case
     *
     * In that particular order, if none could be found after these attempts, the defaultValue is returned
     * instead, otherwise the matching parameter value is returned
     *
     * Parameters:
     *
     *   String key          - [String/The parameter name]
     *   String defaultValue - [String/The default value]
     *
     * Returns:
     *
     *   String - the matching parameter value or the default value if no matches can be found
     */
    public /* internal */ function GetRequestParameter(/*php_string*/ $key, /*php_string*/ $defaultValue = "") {
        $value = $this->app->request->params($key);
        if ($value == null)
            $value = $this->app->request->params(strtoupper($key));
        if ($value == null)
            $value = $this->app->request->params(strtolower($key));
        if ($value == null)
            $value = $defaultValue;

        return $value;
    }

    public /* internal */ function SetResponseHeader(/*php_string*/ $name, /*php_string*/ $value) {
        $this->app->response->header($name, $value);
    }

    public /* internal */ function WriteResponseContent(/*php_string*/ $content) {
        $this->app->response->write($content);
    }

    public /* internal */ function SetResponseBody(/*php_mixed*/ $body) {
        $this->app->response->setBody($body);
    }

    public /* internal */ function SetResponseStatus(/*php_int*/ $statusCode) {
        $this->app->response->setStatus($statusCode);
    }

    public /* internal */ function LogDebug(/*php_string*/ $message) {
        $this->app->log->debug($message);
    }

    public /* internal */ function SetResponseExpiry(/*php_string*/ $expires) {
        $this->app->expires($expires);
    }

    public /* internal */ function GetMapGuideVersion() {
        return $this->app->MG_VERSION;
    }

    public /* internal */ function SetResponseLastModified(/*php_string*/ $mod) {
        $this->app->lastModified($mod);
    }

    public /* internal */ function Redirect(/*php_string*/ $url) {
        $this->app->redirect($url);
    }

    public /* internal */ function Halt(/*php_int*/ $statusCode, /*php_string*/ $body) {
        $this->app->Halt($statusCode, $body);
    }

    public /* internal */ function HasDependency(/*php_string*/ $name) {
        return $this->app->container->has($name);
    }

    public /* internal */ function GetDependency(/*php_string*/ $name) {
        return $this->app->container->$name;
    }

    public /* internal */ function RegisterDependency(/*php_string*/ $name, /*php_mixed*/ $value) {
        $this->app->container->set($name, $value);
    }
}