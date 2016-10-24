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

class ApiResponse {
    private $contentType;
    private $content;
    private $status;
    private $url;
    private $headers;
    private $method;
    private $reqData;
    private $ovMethod;
    public function __construct($status, $contentType, $content, $url, $headers, $method, $reqData) {
        $this->contentType = $contentType;
        $this->content = $content;
        $this->status = $status;
        $this->url = $url;
        $this->headers = $headers;
        $this->method = $method;
        $this->reqData = $reqData;
        $this->ovMethod = null;
    }
    public function getRequestMethod() { return $this->method; }
    public function getRequestUrl() { return $this->url; }
    public function getRequestHeaders() { return $this->headers; }
    public function getContentType() { return $this->contentType; }
    public function getStatusCode() { return $this->status; }
    public function getContent() { return $this->content; }

    public function setOverrideMethod($method) { $this->ovMethod = $method; }
    public function getOverrideMethod() { return $this->ovMethod; }

    public function dump() {
        return "Action: ".$this->getRequestMethod().($this->ovMethod != null ? " (Ov: ".$this->ovMethod.")" : "")." ".$this->url."\nRequest: ".var_export($this->reqData, true)."\nContent Type: ".$this->contentType."\nResponse was:\n".$this->content;
    }
}

?>