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

require_once dirname(__FILE__)."/../ServiceTest.php";
require_once dirname(__FILE__)."/../Config.php";

class GetTileSessionTest extends ServiceTest {
    public function testOperation() {
        $resp = $this->apiTest("/services/copyresource", "POST", array("session" => $this->anonymousSessionId, "source" => "Library://Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition", "destination" => "Session:".$this->anonymousSessionId."//Sheboygan.MapDefinition", "overwrite" => 1));
        $this->assertStatusCodeIs(200, $resp);
        $group = "Base Layer Group";
        $resp = $this->apiTest("/session/".$this->anonymousSessionId."/Sheboygan.MapDefinition/tile.img/$group/6/1/0", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $resp = $this->apiTestAnon("/session/".$this->anonymousSessionId."/Sheboygan.MapDefinition/tile.img/$group/6/1/0", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
        $resp = $this->apiTestAdmin("/session/".$this->anonymousSessionId."/Sheboygan.MapDefinition/tile.img/$group/6/1/0", "GET", array());
        $this->assertStatusCodeIs(200, $resp);
    }
}