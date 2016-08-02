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
    public function __construct($status, $contentType, $content) {
        $this->contentType = $contentType;
        $this->content = $content;
        $this->status = $status;
    }
    public function getContentType() { return $this->contentType; }
    public function getStatusCode() { return $this->status; }
    public function getContent() { return $this->content; }
}

?>