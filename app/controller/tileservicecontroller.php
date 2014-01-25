<?php

//
//  Copyright (C) 2014 by Jackie Ng
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

require_once "controller.php";

class MgTileServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    private static function GetFolderName($prefix, $index, $tilesPerFolder = 30) {
        $folder = "";
        $folderIndex = floor($index / $tilesPerFolder);
        $firstTileIndex = floor($folderIndex * $tilesPerFolder);
        if ($index < 0 && $firstTileIndex === 0) {
            $folder = "-0";
        } else {
            $folder = "$firstTileIndex";
        }
        return $prefix.$folder;
    }

    private static function GetTileIndexString($index, $tilesPerFolder = 30) {
        $name = "";
        $tnIndex = $index % $tilesPerFolder;
        if ($index < 0 && $tnIndex === 0) {
            $name = "-0";
        } else {
            $name = "$tnIndex";
        }
        return $name;
    }

    public function GetTileModificationDate($mapDefIdStr, $groupName, $scaleIndex, $row, $col) {
        $tileCacheName = str_replace("Library://", "", $mapDefIdStr);
        $tileCacheName = str_replace(".MapDefinition", "", $tileCacheName);
        $tileCacheName = str_replace("/", "_", $tileCacheName);
        $path = sprintf("%s/%s/S%s/%s/%s/%s/%s_%s.%s", 
                        $this->app->config("MapGuide.PhysicalTilePath"),
                        $tileCacheName,
                        $scaleIndex,
                        $groupName,
                        MgTileServiceController::GetFolderName("R", $row),
                        MgTileServiceController::GetFolderName("C", $col),
                        MgTileServiceController::GetTileIndexString($row),
                        MgTileServiceController::GetTileIndexString($col),
                        $this->app->config("MapGuide.TileImageFormat"));
        //var_dump($path);
        //die;
        $path = str_replace("/", DIRECTORY_SEPARATOR, $path);
        if (file_exists($path)) {
            return filemtime($path);
        } else {
            //$this->app->response->header("X-Debug-Message", "Could not fetch mtime of $path. File does not exist");
            return false;
        }
    }

    public function GetTile($resId, $groupName, $scaleIndex, $row, $col) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $app = $this->app;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($app, $that, $resIdStr, $groupName, $scaleIndex, $row, $col) {

            $tmd = $that->GetTileModificationDate($resIdStr, $groupName, $scaleIndex, $row, $col);
            if ($tmd !== FALSE) {
                $app->lastModified($tmd);
            }

            $param->AddParameter("OPERATION", "GETTILEIMAGE");
            $param->AddParameter("VERSION", "1.2.0");
            $param->AddParameter("MAPDEFINITION", $resIdStr);
            $param->AddParameter("BASEMAPLAYERGROUPNAME", $groupName);
            $param->AddParameter("SCALEINDEX", $scaleIndex);
            $param->AddParameter("TILEROW", $row);
            $param->AddParameter("TILECOL", $col);
            $that->ExecuteHttpRequest($req);

            $tmd = $that->GetTileModificationDate($resIdStr, $groupName, $scaleIndex, $row, $col);
            if ($tmd !== FALSE) {
                $app->lastModified($tmd);
            }
            $app->expires("+6 months");
        }, true); //Tile access can be anonymous, so allow for it if credentials/session specified
    }
}

?>