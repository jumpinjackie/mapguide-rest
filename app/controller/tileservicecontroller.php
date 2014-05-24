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
            $app->response->header("Cache-Control", "max-age=31536000, must-revalidate");
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

    private static function GetTilePath($app, $resId, $groupName, $z, $x, $y, $type) {
        $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$groupName/$z/$x/$y.$type";
        $path = $app->config("AppRootDir")."/".$app->config("Cache.RootDir")."/tile.$type".$relPath;
        return $path;
    }

    private static function GetScaleFromBounds($map, $devW, $devH, $metersPerUnit, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY) {
        $mcsW = $boundsMaxX - $boundsMinX;
        $mcsH = $boundsMaxY - $boundsMinY;
        $metersPerPixel = 0.0254 / $map->GetDisplayDpi();
        if ($devH * $mcsW > $devW * $mcsH)
            return $mcsW * $metersPerUnit / ($devW * $metersPerPixel); // width-limited
        else
            return $mcsH * $metersPerUnit / ($devH * $metersPerPixel); // height-limited
    }

    private function PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $csFactory, $path, $boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY) {
        $wktRw = new MgWktReaderWriter();
        $agfRw = new MgAgfReaderWriter();

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
        $mapCsWkt = $map->GetMapSRS();
        $layers = $map->GetLayers();
        $groups = $map->GetLayerGroups();
        $baseGroup = $groups->GetItem($groupName);
        $layerCount = $layers->GetCount();
        $firstFeature = true;

        $scale = self::GetScaleFromBounds($map, self::XYZ_TILE_WIDTH, self::XYZ_TILE_HEIGHT, $metersPerUnit, $boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY);
        $fp = fopen($path, "w");

        //$this->app->response->write('{ "Type": "FeatureCollection", "features": [');
        fwrite($fp, '{ "type": "FeatureCollection", "features": [');
        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            $parentGroup = $layer->GetGroup();
            if ($parentGroup != null && $parentGroup->GetObjectId() == $baseGroup->GetObjectId()) {
                if (!self::IsLayerVisibleAtScale($layer, $resSvc, $scale))
                    continue;

                $wktPoly = MgUtils::MakeWktPolygon($boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY);
                
                $geom = $wktRw->Read($wktPoly);
                $clsDef = $layer->GetClassDefinition();
                $fsId = new MgResourceIdentifier($layer->GetFeatureSourceId());
                
                //Set up forward and inverse transforms. Inverse for transforming map bounding box
                //Forward for transforming source geometries to map space
                $xform = self::GetTransform($featSvc, $fsId, $clsDef, $mapCsWkt, $csFactory);
                $query = new MgFeatureQueryOptions();
                $geomName = $layer->GetFeatureGeometryName();
                if ($xform != null) {
                    $sourceCs = $xform->GetSource();
                    $targetCs = $xform->GetTarget();
                    $invXform = $csFactory->GetTransform($targetCs, $sourceCs);
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
                            $propsJson = '"properties": {';
                            $propsJson .= '"_layer": "'.$layer->GetName().'",';
                            $propsJson .= '"_selectable": '.($layer->GetSelectable()?"true":"false").',';
                            $propsJson .= '"_displayIndex": '.($layerCount - $i);
                            for ($j = 0; $j < $reader->GetPropertyCount(); $j++) {
                                $pname = $reader->GetPropertyName($j);
                                $ptype = $reader->GetPropertyType($j);
                                switch($ptype) {
                                    case MgPropertyType::Boolean:
                                        $val = $reader->IsNull($j) ? "null" : ($reader->GetBoolean($j) ? "true" : "false");
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::Byte:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetByte($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::DateTime:
                                        $val = "null";
                                        if (!$reader->IsNull($j)) {
                                            $dt = $reader->GetDateTime($j);
                                            $val = sprintf("%d-%02d-%02d %02d:%02d:%02d", $dt->GetYear(), $dt->GetMonth(), $dt->GetDay(), $dt->GetHour(), $dt->GetMinute(), $dt->GetSecond());
                                        }
                                        $propsJson .= ',"'.$pname.'": "'.$val.'"';
                                        break;
                                    case MgPropertyType::Decimal:
                                    case MgPropertyType::Double:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetDouble($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::Int16:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetInt16($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::Int32:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetInt32($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::Int64:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetInt64($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::Single:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetSingle($j);
                                        $propsJson .= ',"'.$pname.'": '.$val;
                                        break;
                                    case MgPropertyType::String:
                                        $val = $reader->IsNull($j) ? "null" : $reader->GetString($j);
                                        $propsJson .= ',"'.$pname.'": "'.MgUtils::EscapeJsonString($val).'"';
                                        break;
                                }
                            }
                            $propsJson .= '}';
                            fwrite($fp, '{ "type": "Feature", '.$propsJson.', '.$geomJson.'}');
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

        return $path;
    }

    private function PutTileImageXYZ($map, $groupName, $siteConn, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY) {
        //We don't use RenderTile (as it uses key parameters that are locked to serverconfig.ini), we use RenderMap instead
        $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);
        $env = new MgEnvelope($boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY);
        $strColor = $map->GetBackgroundColor();
        //Make sure the alpha component is transparent
        if (strlen($strColor) == 8) {
            $strColor = substr($strColor, 2)."00";
        } else if (strlen($strColor) == 6) {
            $strColor = $strColor."00";
        }
        $bgColor = new MgColor($strColor);
        $tileImg = $renderSvc->RenderMap($map, null, $env, self::XYZ_TILE_WIDTH, self::XYZ_TILE_HEIGHT, $bgColor, $format, false);
        $sink = new MgByteSink($tileImg);
        $sink->ToFile($path);
    }

    public function GetTile($resId, $groupName, $scaleIndex, $row, $col, $format) {
        $fmt = $this->ValidateRepresentation($format, array("img")); //, "json"));
        if ($fmt === "img")
            $this->GetTileImage($resId, $groupName, $scaleIndex, $row, $col, $format);
    }

    const XYZ_TILE_WIDTH = 256;
    const XYZ_TILE_HEIGHT = 256;

    public function GetTileXYZ($resId, $groupName, $x, $y, $z, $type) {
        $fmt = $this->ValidateRepresentation($type, array("json", "png", "png8", "jpg", "gif"));

        $path = self::GetTilePath($this->app, $resId, $groupName, $z, $x, $y, $type);
        clearstatcache();

        $dir = dirname($path);
        if (!@is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
            } catch (Exception $e) { //Another tile request may have already created this since

            }
        }
        $lockPath = "$dir/lock.lck";
        $fpLockFile = fopen($lockPath, "a+");

        $requestId = rand();
        error_log("($requestId) Checking if $path exists");

        if (!file_exists($path)) {

            error_log("($requestId) $path does not exist. Locking for writing");

            $bLocked = false;
            flock($fpLockFile, LOCK_EX);
            fwrite($fpLockFile, ".");
            $bLocked = true;

            error_log("($requestId) Acquired lock for $path. Checking if path exists again.");

            //check once more to see if the cache file was created while waiting for
            //the lock
            clearstatcache();
            if (!file_exists($path)) {
                try {
                    error_log("($requestId) Rendering tile to $path");

                    $this->EnsureAuthenticationForSite("", true);
                    $siteConn = new MgSiteConnection();
                    $siteConn->Open($this->userInfo);

                    $map = new MgMap($siteConn);
                    $map->Create($resId, "VectorTileMap");

                    $layerGroups = $map->GetLayerGroups();
                    $baseGroup = $layerGroups->GetItem($groupName);

                    $factory = new MgCoordinateSystemFactory();
                    $mapCsWkt = $map->GetMapSRS();
                    $mapCs = $factory->Create($mapCsWkt);
                    
                    $mapExtent = $map->GetMapExtent();
                    $mapExLL = $mapExtent->GetLowerLeftCoordinate();
                    $mapExUR = $mapExtent->GetUpperRightCoordinate();

                    $metersPerUnit = $mapCs->ConvertCoordinateSystemUnitsToMeters(1.0);

                    error_log("($requestId) Calc bounds from XYZ");
                    //XYZ to lat/lon math. From this we can convert to the bounds in the map's CS
                    //
                    //Source: http://wiki.openstreetmap.org/wiki/Slippy_map_tilenames
                    $n = pow(2, $z);
                    $lonMin = $x / $n * 360.0 - 180.0;
                    $latMin = rad2deg(atan(sinh(pi() * (1 - 2 * $y / $n))));
                    $lonMax = ($x + 1) / $n * 360.0 - 180.0;
                    $latMax = rad2deg(atan(sinh(pi() * (1 - 2 * ($y + 1) / $n))));

                    $boundsMinX = min($lonMin, $lonMax);
                    $boundsMinY = min($latMin, $latMax);
                    $boundsMaxX = max($lonMax, $lonMin);
                    $boundsMaxY = max($latMax, $latMin);

                    if ($mapCs->GetCsCode() != "LL84") {
                        $llCs = $factory->CreateFromCode("LL84");
                        $trans = $factory->GetTransform($llCs, $mapCs);

                        $ul = $trans->Transform($lonMin, $latMin);
                        $lr = $trans->Transform($lonMax, $latMax);

                        $boundsMinX = min($lr->GetX(), $ul->GetX());
                        $boundsMinY = min($lr->GetY(), $ul->GetY());
                        $boundsMaxX = max($lr->GetX(), $ul->GetX());
                        $boundsMaxY = max($lr->GetY(), $ul->GetY());
                    }

                    //Set all layers under group to be visible
                    $layers = $map->GetLayers();
                    $groups = $map->GetLayerGroups();
                    $layerCount = $layers->GetCount();

                    if ($groups->IndexOf($groupName) < 0) {
                        throw new Exception("Group not found: $groupName"); //TODO: Localize
                    } else {
                        $grp = $groups->GetItem($groupName);
                        $grp->SetVisible(true);
                    }

                    for ($i = 0; $i < $layerCount; $i++) {
                        $layer = $layers->GetItem($i);
                        $group = $layer->GetGroup();
                        if (null == $group) {
                            continue;
                        }
                        if ($group->GetName() != $groupName) {
                            $layer->SetVisible(false);
                            continue;
                        }
                        if ($layer->GetLayerType() == MgLayerType::Dynamic)
                            $layer->SetVisible(true);
                    }

                    if ($type == "json") {
                        error_log("($requestId) Render vector tile");
                        $this->PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $factory, $path, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY);
                    } else {
                        $format = strtoupper($type);
                        error_log("($requestId) Render image tile");
                        $this->PutTileImageXYZ($map, $groupName, $siteConn, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY);
                    }
                } catch (MgException $ex) {
                    if ($bLocked) {
                        error_log("($requestId) MgException caught ".$ex->GetExceptionMessage().". Releasing lock for $path");
                        flock($fpLockFile, LOCK_UN);
                        $bLocked = false;
                    }
                    if ($ex instanceof MgResourceNotFoundException) {
                        $this->app->halt(404, $ex->GetExceptionMessage());
                    }
                } catch (Exception $ex) {
                    if ($bLocked) {
                        error_log("($requestId) Exception caught. Releasing lock for $path");
                        flock($fpLockFile, LOCK_UN);
                        $bLocked = false;
                    }
                }
            }

            if ($bLocked) {
                error_log("($requestId) Releasing lock for $path");
                flock($fpLockFile, LOCK_UN);
                $bLocked = false;
            }
        }

        $modTime = filemtime($path);
        $this->app->lastModified($modTime);

        error_log("($requestId) Acquiring shared lock for $path");
        //acquire shared lock for reading to prevent a problem that could occur
        //if a tile exists but is only partially generated.
        flock($fpLockFile, LOCK_SH);

        error_log("($requestId) Outputting $path");

        $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = "";
        switch ($ext) {
            case "PNG": //MgImageFormats::Png:
                $mimeType = MgMimeType::Png;
                break;
            case "GIF": //MgImageFormats::Gif:
                $mimeType = MgMimeType::Gif;
                break;
            case "JPG": //MgImageFormats::Jpeg:
                $mimeType = MgMimeType::Jpeg;
                break;
            case "JSON":
                $mimeType = MgMimeType::Json;
                break;
        }

        $this->app->response->header("Content-Type", $mimeType);
        $this->app->expires("+6 months");
        $this->app->response->header("Cache-Control", "max-age=31536000, must-revalidate");
        $this->app->response->setBody(file_get_contents($path));

        error_log("($requestId) Releasing shared lock for $path");

        //Release lock
        flock($fpLockFile, LOCK_UN);
        fclose($fpLockFile);
    }
}

?>