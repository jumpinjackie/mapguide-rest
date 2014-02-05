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

    private function GetTileImage($resId, $groupName, $scaleIndex, $row, $col) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $app = $this->app;
        $sessionId = "";
        if ($resId->GetRepositoryType() === MgRepositoryType::Session && $this->app->request->get("session") == null) {
            $sessionId = $resId->GetRepositoryName();
        }

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
        }, true, "", $sessionId); //Tile access can be anonymous, so allow for it if credentials/session specified, but if this is a session-based Map Definition, use the session id as the nominated one
    }

    private static function GetTransform($featSvc, $featureSourceId, $clsDef, $targetWkt, $factory) {
        $transform = null;
        //Has a designated geometry property, use it's spatial context
        if ($clsDef->GetDefaultGeometryPropertyName() !== "") {
            $props = $clsDef->GetProperties();
            $idx = $props->IndexOf($clsDef->GetDefaultGeometryPropertyName());
            if ($idx >= 0) {
                $geomProp = $props->GetItem($idx);
                $scName = $geomProp->GetSpatialContextAssociation();
                $scReader = $featSvc->GetSpatialContexts($featureSourceId, false);
                while ($scReader->ReadNext()) {
                    if ($scReader->GetName() === $scName) {
                        if ($scReader->GetCoordinateSystemWkt() !== $targetWkt) {
                            $targetCs = $factory->Create($targetWkt);
                            $sourceCs = $factory->Create($scReader->GetCoordinateSystemWkt());
                            $transform = $factory->GetTransform($sourceCs, $targetCs);
                            break;
                        }
                    }
                }
                $scReader->Close();
            }
        }
        return $transform;
    }

    private static function IsLayerVisibleAtScale($layer, $resSvc, $scale) {
        $ldfId = $layer->GetLayerDefinition();
        $layerContent = $resSvc->GetResourceContent($ldfId);
        $doc = new DOMDocument();
        $doc->loadXML($layerContent->ToString());
        $scaleRangeNodes = $doc->getElementsByTagName("VectorScaleRange");
        for ($i = 0; $i < $scaleRangeNodes->length; $i++) {
            $scaleRangeNode = $scaleRangeNodes->item($i);

            $minEl = $scaleRangeNode->getElementsByTagName("MinScale");
            $maxEl = $scaleRangeNode->getElementsByTagName("MaxScale");

            $minScale = 0;
            $maxScale = 1000000000000.0; //MdfModel::ScaleRange::MAX_MAP_SCALE
            if ($minEl->length > 0)
                $minScale = floatval($minEl->item(0)->nodeValue);
            if ($maxEl->length > 0)
                $maxScale = floatval($maxEl->item(0)->nodeValue);

            if ($scale >= $minScale && $scale < $maxScale)
                return true;
        }
        return false;
    }

    private static function GetVectorTilePath($app, $resId, $groupName, $scaleIndex, $row, $col) {
        $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$groupName/$scaleIndex/$row/$col.json";
        $path = $app->config("AppRootDir")."/".$app->config("Cache.RootDir")."/vectortile".$relPath;
        return $path;
    }

    private function GetGeoJsonVectorTile($resId, $groupName, $scaleIndex, $row, $col) {

        $path = self::GetVectorTilePath($this->app, $resId, $groupName, $scaleIndex, $row, $col);
        if (file_exists($path)) {
            $this->app->lastModified(filemtime($path));
        }

        $this->EnsureAuthenticationForSite("", true);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
        $tileSvc = $siteConn->CreateService(MgServiceType::TileService);
        $map = new MgMap($siteConn);
        $map->Create($resId, "VectorTileMap");

        $scale = $map->GetFiniteDisplayScaleAt($scaleIndex);
        $layerGroups = $map->GetLayerGroups();
        $baseGroup = $layerGroups->GetItem($groupName);

        $factory = new MgCoordinateSystemFactory();
        $mapCsWkt = $map->GetMapSRS();
        $mapCs = $factory->Create($mapCsWkt);
        
        $mapExtent = $map->GetMapExtent();
        $mapExLL = $mapExtent->GetLowerLeftCoordinate();
        $mapExUR = $mapExtent->GetUpperRightCoordinate();

        $metersPerUnit = $mapCs->ConvertCoordinateSystemUnitsToMeters(1.0);
        $metersPerPixel = 0.0254 / $map->GetDisplayDpi();

        $tileWidthMCS = $tileSvc->GetDefaultTileSizeX() * $metersPerPixel * $scale / $metersPerUnit;
        $tileHeightMCS = $tileSvc->GetDefaultTileSizeY() * $metersPerPixel * $scale / $metersPerUnit;

        $tileMinX = $mapExLL->GetX() + ($col       * $tileWidthMCS);  //left edge
        $tileMaxX = $mapExLL->GetX() + (($col + 1) * $tileWidthMCS);  //right edge 
        $tileMinY = $mapExUR->GetY() - (($row + 1) * $tileHeightMCS); //bottom edge
        $tileMaxY = $mapExUR->GetY() - ($row       * $tileHeightMCS); //top edge

        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();

        $layers = $map->GetLayers();
        $layerCount = $layers->GetCount();
        $firstFeature = true;

        $dir = dirname($path);
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        $fp = fopen($path, "w");

        $this->app->response->header("Content-Type", MgMimeType::Json);
        //$this->app->response->write('{ "Type": "FeatureCollection", "features": [');
        fwrite($fp, '{ "type": "FeatureCollection", "features": [');
        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            if (!self::IsLayerVisibleAtScale($layer, $resSvc, $scale))
                continue;
            $parentGroup = $layer->GetGroup();
            if ($parentGroup != null && $parentGroup->GetObjectId() == $baseGroup->GetObjectId()) {
                $wktPoly = MgUtils::MakeWktPolygon(
                    $tileMinX, $tileMinY, $tileMaxX, $tileMaxY);
                
                $geom = $wktRw->Read($wktPoly);
                $clsDef = $layer->GetClassDefinition();
                $fsId = new MgResourceIdentifier($layer->GetFeatureSourceId());
                
                //Set up forward and inverse transforms. Inverse for transforming map bounding box
                //Forward for transforming source geometries to map space
                $xform = self::GetTransform($featSvc, $fsId, $clsDef, $mapCsWkt, $factory);
                $query = new MgFeatureQueryOptions();
                $geomName = $layer->GetFeatureGeometryName();
                if ($xform != null) {
                    $sourceCs = $xform->GetSource();
                    $targetCs = $xform->GetTarget();
                    $invXform = $factory->GetTransform($targetCs, $sourceCs);
                    $txgeom = $geom->Transform($invXform);
                    $query->SetSpatialFilter($geomName, $txgeom, MgFeatureSpatialOperations::EnvelopeIntersects);
                } else {
                    $query->SetSpatialFilter($geomName, $geom, MgFeatureSpatialOperations::EnvelopeIntersects);
                }

                $reader = $layer->SelectFeatures($query);
                $read = 0;
                while ($reader->ReadNext()) {
                    $read++;
                    if (!$reader->IsNull($geomName)) {
                        if (!$firstFeature) {
                            //$this->app->response->write(",");
                            fwrite($fp, ",");
                        }
                        try {
                            $agf = $reader->GetGeometry($geomName);
                            $fgeom = $agfRw->Read($agf, $xform);
                            $geomJson = MgGeoJsonWriter::ToGeoJson($fgeom);
                            //$this->app->response->write('{ "Type": "Feature", "_layer": "'.$layer->GetName().'", '.$geomJson.'}');
                            fwrite($fp, '{ "type": "Feature", "_layer": "'.$layer->GetName().'", '.$geomJson.'}');
                            $firstFeature = false;
                        } catch (MgException $ex) {

                        }
                    }
                }
                $reader->Close();
            }
        }
        //$this->app->response->write(']}');
        fwrite($fp, ']}');
        fclose($fp);

        $this->app->lastModified(filemtime($path));
        $this->app->response->setBody(file_get_contents($path));
    }

    public function GetTile($resId, $groupName, $scaleIndex, $row, $col, $format) {
        $fmt = $this->ValidateRepresentation($format, array("img")); //, "json"));
        if ($fmt === "img")
            $this->GetTileImage($resId, $groupName, $scaleIndex, $row, $col, $format);
        else if ($fmt === "json")
            $this->GetGeoJsonVectorTile($resId, $groupName, $scaleIndex, $row, $col, $format);
    }
}

?>