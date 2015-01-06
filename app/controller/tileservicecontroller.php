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

    private static function GetTilePath($app, $resId, $groupName, $z, $x, $y, $type, $layerNames) {
        $ext = $type;
        if (strtolower($type) == "png8")
            $ext = substr($type, 0, 3); //png8 -> png
        if ($layerNames != NULL && count($layerNames) == 1) {
            $layerName = $layerNames[0];
            $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$groupName/$layerName/$z/$x/$y.$ext";
        } else {
            $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$groupName/$z/$x/$y.$ext";
        }
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

    private function PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $csFactory, $path, $boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames) {
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

        fwrite($fp, '{ "type": "FeatureCollection", "features": [');
        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            $parentGroup = $layer->GetGroup();
            if ($parentGroup != null && $parentGroup->GetObjectId() == $baseGroup->GetObjectId()) {
                if (!self::IsLayerVisibleAtScale($layer, $resSvc, $scale))
                    continue;

                //If list of layer names specified, skip if this layer is not in that list
                if ($layerNames != null) {
                    $bFound = false;
                    foreach ($layerNames as $layerName) {
                        if ($layer->GetName() == $layerName) {
                            $bFound = true;
                            break;
                        }
                    }
                    if (!$bFound) {
                        continue;
                    }
                }

                $wktPoly = MgUtils::MakeWktPolygon($boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY);

                $geom = $wktRw->Read($wktPoly);
                $clsDef = $layer->GetClassDefinition();
                $clsProps = $clsDef->GetProperties();
                $idProps = $clsDef->GetIdentityProperties();
                $idName = NULL;
                if ($idProps->GetCount() == 1) {
                    $idp = $idProps->GetItem(0);
                    $idName = $idp->GetName();
                }

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

                for ($p = 0; $p < $clsProps->GetCount(); $p++) {
                    $propDef = $clsProps->GetItem($p);
                    $query->AddFeatureProperty($propDef->GetName());
                }

                //If we're rendering a vector tile for a single layer, we don't need these special attributes
                if ($layerNames == NULL || count($layerNames) > 1) {
                    $query->AddComputedProperty("_displayIndex", ($layerCount - $i));
                    $query->AddComputedProperty("_layer", "'".$layer->GetName()."'");
                    $query->AddComputedProperty("_selectable", ($layer->GetSelectable() ? "true" : "false"));
                }

                $reader = $layer->SelectFeatures($query);
                $read = 0;
                while ($reader->ReadNext()) {
                    $read++;
                    if (!$reader->IsNull($geomName)) {
                        if (!$firstFeature) {
                            fwrite($fp, ",");
                        }
                        try {
                            $output = MgGeoJsonWriter::FeatureToGeoJson($reader, $agfRw, $xform, $idName);
                            fwrite($fp, $output);
                            $firstFeature = false;
                        } catch (MgException $ex) {

                        }
                    }
                }
                $reader->Close();
            }
        }
        fwrite($fp, ']}');
        fclose($fp);

        return $path;
    }

    private function PutTileImageXYZ($map, $groupName, $renderSvc, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames) {
        //We don't use RenderTile (as it uses key parameters that are locked to serverconfig.ini), we use RenderMap instead
        $env = new MgEnvelope($boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY);
        $strColor = $map->GetBackgroundColor();
        //Make sure the alpha component is transparent
        if (strlen($strColor) == 8) {
            $strColor = substr($strColor, 2)."00";
        } else if (strlen($strColor) == 6) {
            $strColor = $strColor."00";
        }
        if ($layerNames != NULL) {
            $layers = $map->GetLayers();
            for ($i = 0; $i < $layers->GetCount(); $i++) {
                $layer = $layers->GetItem($i);
                $layer->SetVisible(false);
            }
            foreach ($layerNames as $layerName) {
                $layer = $layers->GetItem($layerName);
                $layer->SetVisible(true);
            }
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

    const MAX_RETRY_ATTEMPTS = 5;

    public function GetTileXYZForLayer($resId, $groupName, $layerName, $x, $y, $z, $type) {
        $fmt = $this->ValidateRepresentation($type, array("json"));
        $this->GetTileXYZ($resId, $groupName, $x, $y, $z, $type, array($layerName));
    }

    public function GetTileXYZ($resId, $groupName, $x, $y, $z, $type, $layerNames = NULL) {
        $fmt = $this->ValidateRepresentation($type, array("json", "png", "png8", "jpg", "gif"));
        $path = self::GetTilePath($this->app, $resId, $groupName, $z, $x, $y, $type, $layerNames);
        clearstatcache();

        $dir = dirname($path);
        $lockPath = "$dir/lock_".$y.".lck";
        $attempts = 0;
        while (!@is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
            } catch (Exception $e) { //Another tile request may have already created this since
                $attempts++;
                //Bail after MAX_RETRY_ATTEMPTS
                if ($attempts >= self::MAX_RETRY_ATTEMPTS)
                    $this->ServerError($this->app->localizer->getText("E_FAILED_TO_CREATE_DIR_AFTER_N_ATTEMPTS", $attempts), $this->GetMimeTypeForFormat($type));
            }
        }

        //If there's a dangling lock file, attempt to remove it
        if (file_exists($lockPath))
            unlink($lockPath);

        $fpLockFile = fopen($lockPath, "a+");

        //Message of any exception caught will be set to this variable
        $tileError = null;

        $requestId = rand();
        $this->app->log->debug("($requestId) Checking if $path exists");

        $attempts = 0;
        while (!file_exists($path)) {
            //Bail after MAX_RETRY_ATTEMPTS
            if ($attempts >= self::MAX_RETRY_ATTEMPTS)
                $this->ServerError($this->app->localizer->getText("E_FAILED_TO_GENERATE_TILE_AFTER_N_ATTEMPTS", $attempts), $this->GetMimeTypeForFormat($type));
            $attempts++;

            $this->app->log->debug("($requestId) $path does not exist. Locking for writing");

            $bLocked = false;
            flock($fpLockFile, LOCK_EX);
            fwrite($fpLockFile, ".");
            $bLocked = true;

            $this->app->log->debug("($requestId) Acquired lock for $path. Checking if path exists again.");

            //check once more to see if the cache file was created while waiting for
            //the lock
            clearstatcache();
            if (!file_exists($path)) {
                try {
                    $this->app->log->debug("($requestId) Rendering tile to $path");

                    $bOldPath = true;
                    if ($type != "json") {
                        //if this is MGOS 3.0 and we're dealing with a tile set, we invoke GETTILEIMAGE as that we can pass in Tile Set Definition
                        //resource ids without issues. We cannot create MgMaps from Tile Set Definitions that are not using the default tile provider.
                        //
                        //The given tile set is assumed to be using the XYZ provider, the case where the Tile Set Definition is using the default provider
                        //is not handled
                        if ($this->app->MG_VERSION[0] >= 3 && $resId->GetResourceType() == "TileSetDefinition") {
                            $bOldPath = false;
                            $sessionId = "";
                            if ($resId->GetRepositoryType() === MgRepositoryType::Session && $this->app->request->get("session") == null) {
                                $sessionId = $resId->GetRepositoryName();
                            }
                            $resIdStr = $resId->ToString();
                            $that = $this;
                            $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $groupName, $x, $y, $z, $requestId, $path) {
                                $param->AddParameter("OPERATION", "GETTILEIMAGE");
                                $param->AddParameter("VERSION", "1.2.0");
                                $param->AddParameter("MAPDEFINITION", $resIdStr);
                                $param->AddParameter("BASEMAPLAYERGROUPNAME", $groupName);
                                $param->AddParameter("SCALEINDEX", $z);
                                $param->AddParameter("TILEROW", $x);
                                $param->AddParameter("TILECOL", $y);
                                $that->app->log->debug("($requestId) Executing GETTILEIMAGE");
                                $that->ExecuteHttpRequest($req, function($result, $status) use ($path) {
                                    if ($status == 200) {
                                        //Need to dump the rendered tile to the specified path so the caching stuff below can still do its thing
                                        $resultObj = $result->GetResultObject();
                                        $sink = new MgByteSink($resultObj);
                                        $sink->ToFile($path);
                                    }
                                });
                            }, true, "", $sessionId); //Tile access can be anonymous, so allow for it if credentials/session specified, but if this is a session-based Map Definition, use the session id as the nominated one
                        }
                    }

                    //Pre MGOS 3.0 code path
                    if ($bOldPath) {
                        $this->app->log->debug("($requestId) Going down old code path");
                        $this->EnsureAuthenticationForSite("", true);
                        $siteConn = new MgSiteConnection();
                        $siteConn->Open($this->userInfo);

                        $map = new MgMap($siteConn);
                        $map->Create($resId, "VectorTileMap");

                        $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);

                        $layerGroups = $map->GetLayerGroups();
                        $baseGroup = $layerGroups->GetItem($groupName);

                        $factory = new MgCoordinateSystemFactory();
                        $mapCsWkt = $map->GetMapSRS();
                        $mapCs = $factory->Create($mapCsWkt);

                        $mapExtent = $map->GetMapExtent();
                        $mapExLL = $mapExtent->GetLowerLeftCoordinate();
                        $mapExUR = $mapExtent->GetUpperRightCoordinate();

                        $metersPerUnit = $mapCs->ConvertCoordinateSystemUnitsToMeters(1.0);

                        $this->app->log->debug("($requestId) Calc bounds from XYZ");
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
                            throw new Exception($this->app->localizer->getText("E_GROUP_NOT_FOUND", $groupName));
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
                            //error_log("($requestId) Render vector tile");
                            $this->PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $factory, $path, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames);
                        } else {
                            $format = strtoupper($type);
                            //error_log("($requestId) Render image tile");
                            $this->PutTileImageXYZ($map, $groupName, $renderSvc, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames);
                        }
                    }
                } catch (MgException $ex) {
                    if ($bLocked) {
                        $this->app->log->debug("($requestId) MgException caught ".$ex->GetExceptionMessage().". Releasing lock for $path");
                        $tileError = $ex->GetExceptionMessage();
                        flock($fpLockFile, LOCK_UN);
                        $bLocked = false;
                    }
                    if ($ex instanceof MgResourceNotFoundException) {
                        $this->NotFound($ex->GetExceptionMessage(), $this->GetMimeTypeForFormat($fmt));
                    }
                    else if ($ex instanceof MgConnectionFailedException) {
                        $this->ServiceUnavailable($ex->GetExceptionMessage(), $this->GetMimeTypeForFormat($fmt));
                    }
                } catch (Exception $ex) {
                    if ($bLocked) {
                        $tileError = $ex->getMessage();
                        $this->app->log->debug("($requestId) Exception caught ($tileError). Releasing lock for $path");
                        flock($fpLockFile, LOCK_UN);
                        $bLocked = false;
                    }
                }
            }

            if ($bLocked) {
                $this->app->log->debug("($requestId) Releasing lock for $path");
                flock($fpLockFile, LOCK_UN);
                $bLocked = false;
            }
        }

        //An exception occurred, try to clean up lock before bailing
        if ($tileError != null) {
            try {
                fclose($fpLockFile);
                unlink($lockPath);
            } catch (Exception $ex) {
                $this->app->log->debug("($requestId) Failed to delete lock file. Perhaps another concurrent request to the same tile is happening?");
            }
            throw new Exception($tileError);
        }

        $modTime = filemtime($path);
        $this->app->lastModified($modTime);

        $this->app->log->debug("($requestId) Acquiring shared lock for $path");
        //acquire shared lock for reading to prevent a problem that could occur
        //if a tile exists but is only partially generated.
        flock($fpLockFile, LOCK_SH);

        $this->app->log->debug("($requestId) Outputting $path");

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

        $this->app->log->debug("($requestId) Releasing shared lock for $path");

        //Release lock
        flock($fpLockFile, LOCK_UN);
        //Try to delete the lock file
        try {
            fclose($fpLockFile);
            unlink($lockPath);
        } catch (Exception $ex) {
            $this->app->log->debug("($requestId) Failed to delete lock file. Perhaps another concurrent request to the same tile is happening?");
        }
    }
}

?>
