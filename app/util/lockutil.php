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

/**
 * Utility class to assist with serializing concurrent file access for writing cached content
 */
class MgFileLockUtil 
{
    private $path;
    private $lockFileFormat;
    private $maxCreateDirAttempts;
    private $requestId;
    private $app;
    
    public function __construct($app, $path, $requestId, $maxCreateDirAttempts = 5, $lockFileFormat = "lock_%s.lck") {
        $this->app = $app;
        $this->path = $path;
        $this->lockFileFormat = $lockFileFormat;
        $this->maxCreateDirAttempts = $maxCreateDirAttempts;
        $this->requestId = $requestId;
    }
    
    /**
     * Acquire a file-based lock to write the given file. The lock is represented by the MgLock class
     */
    public function Acquire($lockName) {
        clearstatcache();
        $dir = dirname($this->path);
        $lockPath = $dir."/".sprintf($this->lockFileFormat, $lockName);
        $attempts = 0;
        while (!@is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
            } catch (Exception $e) { //Another tile request may have already created this since
                $attempts++;
                //Bail after MAX_RETRY_ATTEMPTS
                if ($attempts >= $this->maxCreateDirAttempts)
                    throw new Exception($this->app->localizer->getText("E_FAILED_TO_CREATE_DIR_AFTER_N_ATTEMPTS", $attempts), $this->GetMimeTypeForFormat($type));
            }
        }
        
        //If there's a dangling lock file, attempt to remove it
        if (file_exists($lockPath))
            unlink($lockPath);
            
        $fpLockFile = fopen($lockPath, "a+");
        return new MgFileLock($this->requestId, $this->path, $lockPath, $fpLockFile, $this->maxCreateDirAttempts, $this->app->log);
    }
}

/**
 * Represents a file-based lock acquired by the MgLockUtil
 */
class MgFileLock
{
    private $requestId;
    private $filePath;
    private $lockPath;
    private $fpLockFile;
    private $bLocked;
    private $maxCreateDirAttempts;
    private $log;
    
    public function __construct($requestId, $filePath, $lockPath, $fpLockFile, $maxCreateDirAttempts, $log) {
        $this->requestId = $requestId;
        $this->filePath = $filePath;
        $this->lockPath = $lockPath;
        $this->fpLockFile = $fpLockFile;
        $this->bLocked = false;
        $this->maxCreateDirAttempts = $maxCreateDirAttempts;
        $this->log = $log;
    }
    
    public function EnterCriticalSection($app, $section) {
        //Message of any exception caught will be set to this variable
        $sectionError = null;
        
        $app->log->debug("(".$this->requestId.") Checking if ".$this->filePath." exists");
        
        $attempts = 0;
        while (!file_exists($this->filePath)) {
            //Bail after max attempts exceeded
            if ($attempts >= $this->maxCreateDirAttempts)
                throw new Exception($app->localizer->getText("E_FAILED_TO_GENERATE_TILE_AFTER_N_ATTEMPTS", $attempts));
            $attempts++;
            
            $app->log->debug("(".$this->requestId.") ".$this->filePath." does not exist. Locking for writing");
            $this->LockExclusive();
            $app->log->debug("(".$this->requestId.") Acquired lock for ".$this->filePath.". Checking if path exists again.");
            
            //check once more to see if the cache file was created while waiting for
            //the lock
            clearstatcache();
            if (!file_exists($this->filePath)) {
                try {
                    //Enter our critical section
                    $section->Enter($this->requestId, $app, $this->filePath);
                } catch (MgException $ex) {
                    $sectionError = $ex->GetExceptionMessage();
                    $this->UnlockExclusive();
                    $section->HandleMgException($ex);
                } catch (Exception $ex) {
                    $sectionError = get_class($ex)." - ".$ex->getMessage();
                    $app->log->debug("(".$this->requestId.") Exception caught ($sectionError). Releasing lock for ".$this->filePath);
                    $section->HandleException($ex);
                }
            }
            
            $this->UnlockExclusive();
        }
        
        if ($sectionError != null) {
            $this->Cleanup();
            throw new Exception($sectionError);
        }
        
        $section->PostProcess($this->requestId, $app, $this, $this->filePath);
        // Release lock and cleanup 
        $this->Unlock();
        $this->Cleanup();
    }
    
    public function IsLockedExclusive() {
        return $this->bLocked;
    }
    
    public function LockExclusive() {
        $this->bLocked = false;
        flock($this->fpLockFile, LOCK_EX);
        fwrite($this->fpLockFile, ".");
        $this->bLocked = true;
    }
    
    public function LockShared() {
        flock($this->fpLockFile, LOCK_SH);
    }
    
    public function Unlock() {
        flock($this->fpLockFile, LOCK_UN);
    }
    
    public function UnlockExclusive() {
        if ($this->bLocked) {
            $this->log->debug("(".$this->requestId.") Releasing lock for ".$this->lockPath);
            $this->Unlock();
            $this->bLocked = false;
        }
    }
    
    public function Cleanup() {
        //Try to delete the lock file
        try {
            fclose($this->fpLockFile);
            unlink($this->lockPath);
        } catch (Exception $ex) {
            $this->log->debug("(".$this->requestId.") Failed to delete lock file. Perhaps another concurrent request to the same tile is happening?");
        }
    }
}

abstract class MgFileLockCriticalSection
{
    protected function __construct() { }
    
    /**
     * Perform an implementation-specific action against the supplied path
     *
     * The given path will be file-locked for exclusive access. No exception handling
     * is required in the implementing method, but the implementing class may choose
     * to override various hooks in this class to handle various events that may happen
     */
    public abstract function Enter($requestId, $app, $path);
    
    /**
     * Handles any MgException caught when entering this critical section
     */
    public function HandleMgException($ex) { }
    
    /**
     * Handles any Exception caught when entering this critical section
     */
    public function HandleException($ex) { }
    
    /**
     * Performs any post-processing after leaving the critical section
     */
    public function PostProcess($requestId, $app, $lock, $path) { }
}

class MgGetTileXYZCriticalSection extends MgFileLockCriticalSection {
    private $ctrl;
    private $format;
    private $resId;
    private $groupName;
    private $x;
    private $y;
    private $z;
    private $layerNames;
    
    public function __construct($ctrl, $resId, $groupName, $x, $y, $z, $layerNames, $format) {
        parent::__construct();
        $this->ctrl = $ctrl;
        $this->resId = $resId;
        $this->groupName = $groupName;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->layerNames = $layerNames;
        $this->format = $format;
    }
    
    public function PostProcess($requestId, $app, $lock, $path) {
        $modTime = filemtime($path);
        $app->lastModified($modTime);

        $app->log->debug("($requestId) Acquiring shared lock for $path");
        //acquire shared lock for reading to prevent a problem that could occur
        //if a tile exists but is only partially generated.
        $lock->LockShared();

        $app->log->debug("($requestId) Outputting $path");

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

        $app->response->header("Content-Type", $mimeType);
        $app->expires("+6 months");
        $app->response->header("Cache-Control", "max-age=31536000, must-revalidate");
        $app->response->setBody(file_get_contents($path));

        $app->log->debug("($requestId) Releasing shared lock for $path");
    }
    
    public function HandleMgException($ex) {
        if ($ex instanceof MgResourceNotFoundException || $ex instanceof MgObjectNotFoundException) {
            $this->ctrl->NotFound($ex->GetExceptionMessage(), $this->ctrl->GetMimeTypeForFormat($fmt));
        }
        else if ($ex instanceof MgConnectionFailedException) {
            $this->ctrl->ServiceUnavailable($ex->GetExceptionMessage(), $this->ctrl->GetMimeTypeForFormat($fmt));
        }
    }
    
    public function Enter($requestId, $app, $path) {
        $app->log->debug("($requestId) Rendering tile to $path");

        $groupName = $this->groupName;
        $x = $this->x;
        $y = $this->y;
        $z = $this->z;
        $layerNames = $this->layerNames;

        $bOldPath = true;
        if ($this->format != "json") {
            //if this is MGOS 3.0 and we're dealing with a tile set, we invoke GETTILEIMAGE as that we can pass in Tile Set Definition
            //resource ids without issues. We cannot create MgMaps from Tile Set Definitions that are not using the default tile provider.
            //
            //The given tile set is assumed to be using the XYZ provider, the case where the Tile Set Definition is using the default provider
            //is not handled
            if ($app->MG_VERSION[0] >= 3 && $this->resId->GetResourceType() == "TileSetDefinition") {
                $bOldPath = false;
                $sessionId = "";
                if ($this->resId->GetRepositoryType() === MgRepositoryType::Session && $app->request->get("session") == null) {
                    $sessionId = $this->resId->GetRepositoryName();
                }
                $resIdStr = $this->resId->ToString();
                $that = $this;
                $this->ctrl->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $groupName, $x, $y, $z, $requestId, $path) {
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
            $app->log->debug("($requestId) Going down old code path");
            $siteConn = $this->ctrl->AcquireConnectionForGetTileXYZ();

            $map = new MgMap($siteConn);
            $map->Create($this->resId, "VectorTileMap");

            $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);

            $groups = $map->GetLayerGroups();
            $baseGroup = $groups->GetItem($groupName); //Will throw MgObjectNotFoundException -> 404 if no such group exists

            $factory = new MgCoordinateSystemFactory();
            $mapCsWkt = $map->GetMapSRS();
            $mapCs = $factory->Create($mapCsWkt);

            $mapExtent = $map->GetMapExtent();
            $mapExLL = $mapExtent->GetLowerLeftCoordinate();
            $mapExUR = $mapExtent->GetUpperRightCoordinate();

            $metersPerUnit = $mapCs->ConvertCoordinateSystemUnitsToMeters(1.0);

            $app->log->debug("($requestId) Calc bounds from XYZ");
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
            
            $layerCount = $layers->GetCount();
            $groupCount = $groups->GetCount();

            //Turn all groups that are not the given group to be hidden
            for ($i = 0; $i < $groupCount; $i++) {
                $group = $groups->GetItem($i);
                if ($group->GetName() != $groupName) {
                    $group->SetVisible(false);
                } else {
                    $group->SetVisible(true);
                }
            }

            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $group = $layer->GetGroup();
                if (null == $group) {
                    continue;
                }
                if ($group->GetName() != $groupName && $layer->GetLayerType() == MgLayerType::Dynamic) {
                    $layer->SetVisible(false);
                    continue;
                }
                if ($layer->GetLayerType() == MgLayerType::Dynamic)
                    $layer->SetVisible(true);
            }

            if ($this->format == "json") {
                //error_log("($requestId) Render vector tile");
                $this->ctrl->PutVectorTileXYZ($map, $groupName, $siteConn, $metersPerUnit, $factory, $path, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames);
            } else {
                $format = strtoupper($this->format);
                //error_log("($requestId) Render image tile");
                $this->ctrl->PutTileImageXYZ($map, $groupName, $renderSvc, $path, $format, $boundsMinX, $boundsMinY, $boundsMaxX, $boundsMaxY, $layerNames, $requestId);
            }
        }
    }
}