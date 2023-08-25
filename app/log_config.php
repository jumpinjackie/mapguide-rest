<?php

//
//  Copyright (C) 2015 by Jackie Ng
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

class LogWriter extends \Slim\LogWriter
{
    public function __construct(/*php_string*/ $path) {
        parent::__construct(fopen($path, "a"));
    }
}

return array(
    "log.enabled" => false,
    "log.level" => \Slim\Log::DEBUG,
    "log.writer" => new LogWriter(dirname(__FILE__)."/../cache/debug.log")
);