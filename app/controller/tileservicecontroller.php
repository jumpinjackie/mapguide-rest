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
require_once dirname(__FILE__)."/../util/lockutil.php";

class MgTileServiceController extends MgBaseController {
    public function __construct(IAppServices $app) {
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
                        $this->app->GetConfig("MapGuide.PhysicalTilePath"),
                        $tileCacheName,
                        $scaleIndex,
                        $groupName,
                        MgTileServiceController::GetFolderName("R", $row),
                        MgTileServiceController::GetFolderName("C", $col),
                        MgTileServiceController::GetTileIndexString($row),
                        MgTileServiceController::GetTileIndexString($col),
                        $this->app->GetConfig("MapGuide.TileImageFormat"));
        //var_dump($path);
        //die;
        $path = str_replace("/", DIRECTORY_SEPARATOR, $path);
        if (file_exists($path)) {
            return filemtime($path);
        } else {
            //$this->app->SetResponseHeader("X-Debug-Message", "Could not fetch mtime of $path. File does not exist");
            return false;
        }
    }

    private function GetTileImage($resId, $groupName, $scaleIndex, $row, $col) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $app = $this->app;
        $sessionId = "";
        if ($resId->GetRepositoryType() === MgRepositoryType::Session && $this->app->GetRequestParameter("session") == null) {
            $sessionId = $resId->GetRepositoryName();
        }

        $this->EnsureAuthenticationForHttp(function($req, $param) use ($app, $that, $resIdStr, $groupName, $scaleIndex, $row, $col) {

            $tmd = $that->GetTileModificationDate($resIdStr, $groupName, $scaleIndex, $row, $col);
            if ($tmd !== FALSE) {
                $that->SetResponseLastModified($tmd);
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
                $that->SetResponseLastModified($tmd);
            }
            $app->SetResponseExpiry("+6 months");
            $app->SetResponseHeader("Cache-Control", "max-age=31536000, must-revalidate");
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

    private static function GetTilePath(IAppServices $app, $resId, $groupName, $z, $x, $y, $type, $layerNames, $scale = 1) {
        $ext = $type;
        if (strtolower($type) == "png8")
            $ext = substr($type, 0, 3); //png8 -> png
        $gn = $groupName;
        if ($scale > 1) {
            $gn .= "@".$scale."x";
        }
        if ($layerNames != NULL && count($layerNames) == 1) {
            $layerName = $layerNames[0];
            $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$gn/$layerName/$z/$x/$y.$ext";
        } else {
            $relPath = "/".$resId->GetPath()."/".$resId->GetName()."/$gn/$z/$x/$y.$ext";
        }
        $customRoot = $app->GetConfig("Cache.XYZTileRoot");
        if ($customRoot != null)
            $path = "$customRoot/tile.$type".$relPath;
        else
            $path = $app->GetConfig("AppRootDir")."/".$app->GetConfig("Cache.RootDir")."/tile.$type".$relPath;
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

    public function PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $csFactory, $path, $boundsMinx, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames) {
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
                            $output = MgReaderToGeoJsonWriter::FeatureToGeoJson($reader, $agfRw, $xform, $idName);
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

    public function PutTileImageXYZ($map, $groupName, $retinaScale, $renderSvc, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames, $requestId) {
        //We don't use RenderTile (as it uses key parameters that are locked to serverconfig.ini), we use RenderMap instead
        $bufferPx = $this->app->GetConfig("MapGuide.XYZTileBuffer") * $retinaScale;
        $tileWidth = self::XYZ_TILE_WIDTH * $retinaScale;
        $tileHeight = self::XYZ_TILE_HEIGHT * $retinaScale;
        $ratio = $bufferPx / $tileWidth;
        $dx = $boundsMaxX - $boundsMinX;
        $dy = $boundsMaxY - $boundsMinY;
        //If we have a buffer, we have to inflate the bounds by the given ratio
        $minx = $boundsMinX - ($dx * $ratio);
        $miny = $boundsMinY - ($dy * $ratio);
        $maxx = $boundsMaxX + ($dx * $ratio);
        $maxy = $boundsMaxY + ($dx * $ratio);
        $env = new MgEnvelope($minx, $miny, $maxx, $maxy);

        if ($retinaScale > 1) {
            $dpi = $map->GetDisplayDpi();
            $map->SetDisplayDpi($dpi * $retinaScale);
        }

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
        $tileImg = $renderSvc->RenderMap($map, null, $env, $tileWidth + (2 * $bufferPx), $tileHeight + (2 * $bufferPx), $bgColor, $format, false);
        $sink = new MgByteSink($tileImg);
        
        if ($bufferPx > 0)
        {
            $tmpPath = tempnam(sys_get_temp_dir(), 'TempTile');
            $sink->ToFile($tmpPath);
            
            $this->app->LogDebug("($requestId): Saved image to $tmpPath for cropping");
            
            $im = null;
            switch ($format)
            {
                case MgImageFormats::Png:
                    $im = imagecreatefrompng($tmpPath);
                    break;
                case MgImageFormats::Jpeg:
                    $im = imagecreatefromjpeg($tmpPath);
                    break;
                case MgImageFormats::Gif:
                    $im = imagecreatefromgif($tmpPath);
                    break;
            }
            
            if ($im == null)
                throw new Exception("Image format not supported for cropping: $format");

            $tile = imagecreatetruecolor($tileWidth, $tileHeight);
            imagesavealpha($tile, true);
            
            $trans_color = imagecolorallocatealpha($tile, 0, 0, 0, 127);
            imagefill($tile, 0, 0, $trans_color);
            
            $error = null;
            try {
                //Crop by using imagecopy(). imagecrop() exists in PHP 5.5 and if you're using PHP 5.5, chances
                //are you're using MGOS 3.0 that has native XYZ tile support in which case: Why are you even
                //here?
                imagecopy($tile, $im, 0, 0, $bufferPx, $bufferPx, $tileWidth, $tileHeight);
                $this->app->LogDebug("($requestId): Cropped image. Saving to $path");
                
                switch ($format)
                {
                    case MgImageFormats::Png:
                        imagepng($tile, $path);
                        break;
                    case MgImageFormats::Jpeg:
                        imagejpeg($tile, $path);
                        break;
                    case MgImageFormats::Gif:
                        imagegif($tile, $path);
                        break;
                }
            } catch (Exception $ex) {
                //Don't rethrow yet as we have some cleanup to do
                $error = $ex;
            }
            
            @imagedestroy($tile);
            @imagedestroy($im);
            @unlink($tmpPath);
            
            //We can rethrow now if something was caught
            if ($error != null)
                throw $error;
        }
        else
        {
            $sink->ToFile($path);
        }
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
        
        $requestId = rand();
        $lockUtil = new MgFileLockUtil($this->app, $path, $requestId, self::MAX_RETRY_ATTEMPTS);
        
        $lock = $lockUtil->Acquire($y);
        
        $section = new MgGetTileXYZCriticalSection($this->app, $resId, $groupName, $x, $y, $z, $layerNames, $type);
        $lock->EnterCriticalSection($this->app, $section);
    }

    public function GetTileXYZRetina($resId, $groupName, $x, $y, $z, $type, $scale, $layerNames = NULL) {
        $fmt = $this->ValidateRepresentation($type, array("png", "png8", "jpg", "gif"));
        $path = self::GetTilePath($this->app, $resId, $groupName, $z, $x, $y, $type, $layerNames, $scale);
        
        $requestId = rand();
        $lockUtil = new MgFileLockUtil($this->app, $path, $requestId, self::MAX_RETRY_ATTEMPTS);
        
        $lock = $lockUtil->Acquire($y);
        
        $section = new MgGetTileXYZCriticalSection($this->app, $resId, $groupName, $x, $y, $z, $layerNames, $type);
        $section->SetRetinaScale($scale);
        $lock->EnterCriticalSection($this->app, $section);
    }
    
    public function AcquireConnectionForGetTileXYZ() {
        $this->EnsureAuthenticationForSite("", true);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        return $siteConn;
    }
}