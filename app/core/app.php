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
require_once dirname(__FILE__)."/stringcontentadapter.php";
require_once dirname(__FILE__)."/exceptions.php";

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
        return call_user_func_array(array($this->localizer, "getText"), func_get_args());
    }

    public /* internal */ function GetConfig(/*php_string*/ $name) {
        return $this->config[$name];
    }

    public /* internal */ function GetRequestPathInfo() {
        return $this->request->getUri()->getPath();
    }

    public /* internal */ function GetRequestHeader(/*php_string*/ $name) {
        $h = $this->request->getHeader($name);
        if (is_array($h) && count($h) === 1) {
            return $h[0];
        }
        return $h; //assume it's a string
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

    public /* internal */ function SetResponseBody(/*string | Psr\Http\Message\StreamInterface*/ $content) {
        $adapter = is_string($content) ? new StringContentAdapter($content) : $content;
        $this->response = $this->response->withBody($adapter);
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

    public /* internal */ function Halt(/*php_int*/ $statusCode, /*php_string*/ $body, /*php_string*/ $mimeType) {
        throw new HaltException($body, $statusCode, $mimeType);
    }

    public /* internal */ function HasDependency(/*php_string*/ $name) {
        return $this->container->has($name);
    }

    public /* internal */ function GetDependency(/*php_string*/ $name) {
        return $this->container->get($name);
    }

    public /* internal */ function RegisterDependency(/*php_string*/ $name, /*php_mixed*/ $value) {
        $this->container[$name] = function($c) use ($value) {
            return $value;
        };
    }
    
    public function Done() {
        return $this->response;
    }
}