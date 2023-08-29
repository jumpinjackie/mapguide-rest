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
    private $container;
    private $config;
    private $localizer;
    private $request;
    private $response;
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->config = $this->container->get('settings');
        $this->localizer = $this->container->get('localizer');
        $this->request = $this->container->get('request');
        $this->response = $this->container->get('response');
    }

    public /* internal */ function GetLocalizedText(/*php_string*/ $key) {
        $this->localizer->getText($key);
    }

    public /* internal */ function GetConfig(/*php_string*/ $name) {
        return $this->config[$name];
    }

    public /* internal */ function GetRequestPathInfo() {
        return $this->request->getUri()->getPath();
    }

    public /* internal */ function GetRequestHeader(/*php_string*/ $name) {
        return $this->request->getHeader($name);
    }

    public /* internal */ function GetRequestBody() {
        return $this->request->getBody();
    }

    public /* internal */ function GetAllRequestParams() {
        return $this->request->params();
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
        $value = $this->request->getParam($key);
        if ($value == null)
            $value = $this->request->getParam(strtoupper($key));
        if ($value == null)
            $value = $this->request->getParam(strtolower($key));
        if ($value == null)
            $value = $defaultValue;

        return $value;
    }

    public /* internal */ function SetResponseHeader(/*php_string*/ $name, /*php_string*/ $value) {
        $this->response = $this->response->withHeader($name, $value);
    }

    public /* internal */ function WriteResponseContent(/*php_string*/ $content) {
        $this->response->write($content);
    }

    public /* internal */ function SetResponseBody(/*php_mixed*/ $content) {
        $body = $this->response->getBody();
        $body->write($content);
    }

    public /* internal */ function SetResponseStatus(/*php_int*/ $statusCode) {
        $this->response = $this->response->withStatus($statusCode);
    }

    public /* internal */ function LogDebug(/*php_string*/ $message) {
        //$this->app->log->debug($message);
    }

    public /* internal */ function SetResponseExpiry(/*php_string*/ $expires) {
        $this->app->expires($expires);
    }

    public /* internal */ function GetMapGuideVersion() {
        return $this->container->get('mgVersion');
    }

    public /* internal */ function SetResponseLastModified(/*php_string*/ $mod) {
        $this->app->lastModified($mod);
    }

    public /* internal */ function Redirect(/*php_string*/ $url) {
        $this->app->redirect($url);
    }

    public /* internal */ function Halt(/*php_int*/ $statusCode, /*php_string*/ $body) {
        $this->SetResponseStatus($statusCode);
        $this->SetResponseBody($body);
    }

    public /* internal */ function HasDependency(/*php_string*/ $name) {
        return $this->container->has($name);
    }

    public /* internal */ function GetDependency(/*php_string*/ $name) {
        return $this->container->get($name);
    }

    public /* internal */ function RegisterDependency(/*php_string*/ $name, /*php_mixed*/ $value) {
        $this->app->container->set($name, $value);
    }
    
    public function Done() {
        return $this->response;
    }
}