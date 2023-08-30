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

class HaltException extends Exception {
    private $mimeType;
    public function __construct($message, $code, $mimeType) {
        $this->mimeType = $mimeType;
        parent::__construct($message, $code);
    }
    public function getMimeType() {
        return $this->mimeType;
    }
}