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

use Psr\Http\Message\StreamInterface;

class StringContentAdapter implements StreamInterface {
    private $content;
    private $pos;
    private $size;
    public function __construct($content) {
        $this->content = $content;
        $this->size = strlen($this->content);;
        $this->pos = 0;
    }

    public function __toString() {
        return $this->content;
    }

    public function close() { }

    public function detach() {
        return null;
    }

    public function getSize() {
        return $this->size;
    }

    public function tell() {

    }

    public function eof() {
        return $this->pos > $this->size;
    }

    public function isSeekable() {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET) {
        throw new Exception("Not supported");
    }

    public function rewind() {
        throw new Exception("Not supported");
    }

    public function isWritable() {
        return false;
    }

    public function write($string) {
        throw new Exception("Not supported");
    }

    public function isReadable() {
        return true;
    }

    public function read($length) {
        $buffer = substr($this->content, $this->pos, $length);
        $this->pos += $length;
        return $buffer;
    }

    public function getContents() {
        return '';
    }

    public function getMetadata($key = null) {
        return null;
    }
}