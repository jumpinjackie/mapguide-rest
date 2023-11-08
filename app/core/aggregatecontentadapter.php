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

class AggregateContentAdapter implements StreamInterface {
    private $subStreams;
    private $currentStreamIndex;
    public function __construct(array $subStreams) {
        $this->subStreams = $subStreams;
        $this->currentStreamIndex = 0;
    }

    private function getCurrentStream() {
        // Gone past last stream
        if ($this->currentStreamIndex >= count($this->subStreams))
            return null;
        return $this->subStreams[$this->currentStreamIndex];
    }

    public function __toString() {
        $content = '';
        foreach ($this->subStreams as $ss) {
            $content .= $ss->__toString();
        }
        return $content;
    }

    public function close() {
        foreach ($this->subStreams as $ss) {
            $ss->close();
        }
    }

    public function detach() {
        return null;
    }

    public function getSize() {
        /*
        $size = 0;
        foreach ($this->subStreams as $ss) {
            $size += $ss->getSize();
        }
        return $size;
        */
        return null;
    }

    public function tell() {

    }

    public function eof() {
        $s = $this->getCurrentStream();
        if ($s != null) {
            if ($this->currentStreamIndex === count($this->subStreams) - 1)
                return $s->eof();
            else // Not last stream, so definitely not done
                return false;
        }
        return true;
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
        $buffer = "";
        $s = $this->getCurrentStream();
        if ($s != null) {
            $buffer .= $s->read($length);
            if ($s->eof()) {
                $remainingLength = $length - strlen($buffer);
                // Move to next stream and read the remaining requested length
                $this->currentStreamIndex++;
                $s = $this->getCurrentStream();
                if ($s != null) {
                    $buffer .= $s->read($remainingLength);
                }
            }
        }
        return $buffer;
    }

    public function getContents() {
        return '';
    }

    public function getMetadata($key = null) {
        return null;
    }
}