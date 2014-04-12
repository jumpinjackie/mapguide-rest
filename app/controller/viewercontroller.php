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

class MgViewerController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function LaunchAjaxViewer($resId) {
        $selfUrl = $this->app->config("SelfUrl");
        $this->app->redirect("$selfUrl/../mapviewerajax/?WEBLAYOUT=".$resId->ToString());
    }

    public function LaunchFusionViewer($resId, $template) {
        $selfUrl = $this->app->config("SelfUrl");
        $this->app->redirect("$selfUrl/../fusion/templates/mapguide/$template/index.html?ApplicationDefinition=".$resId->ToString());
    }

    private function GetFeatureClassMBR($featuresId, $className, $geomProp, $featureSrvc)
    {
        $extentGeometryAgg = null;
        $extentGeometrySc = null;
        $extentByteReader = null;
        
        $mbr = new stdClass();
        $geomName = $geomProp->GetName();
        $spatialContext = $geomProp->GetSpatialContextAssociation();

        // Finds the coordinate system
        $agfReaderWriter = new MgAgfReaderWriter();
        $spatialcontextReader = $featureSrvc->GetSpatialContexts($featuresId, false);
        while ($spatialcontextReader->ReadNext())
        {
            if ($spatialcontextReader->GetName() == $spatialContext)
            {
                $mbr->coordinateSystem = $spatialcontextReader->GetCoordinateSystemWkt();

                // Finds the extent
                $extentByteReader = $spatialcontextReader->GetExtent();
                break;
            }
        }
        $spatialcontextReader->Close();
        if ($extentByteReader != null)
        {
            // Get the extent geometry from the spatial context
            $extentGeometrySc = $agfReaderWriter->Read($extentByteReader);
        }

        // Try to get the extents using the selectaggregate as sometimes the spatial context
        // information is not set
        $aggregateOptions = new MgFeatureAggregateOptions();
        $featureProp = 'SPATIALEXTENTS("' . $geomName . '")';
        $aggregateOptions->AddComputedProperty('EXTENTS', $featureProp);

        try
        {
            $dataReader = $featureSrvc->SelectAggregate($featuresId, $className, $aggregateOptions);
            if($dataReader->ReadNext())
            {
                // Get the extents information
                $byteReader = $dataReader->GetGeometry('EXTENTS');
                $extentGeometryAgg = $agfReaderWriter->Read($byteReader);
            }
            $dataReader->Close();
        }
        catch (MgException $e)
        {
            if ($extentGeometryAgg == null) 
            {
                //We do have one last hope. EXTENT() is an internal MapGuide custom function that's universally supported
                //as it operates against an underlying select query result. This raw-spins the reader server-side so there
                //is no server -> web tier transmission overhead involved.
                try
                {
                    $aggregateOptions = new MgFeatureAggregateOptions();
                    $aggregateOptions->AddComputedProperty("COMP_EXTENT", "EXTENT(".$geomName.")");
                    
                    $dataReader = $featureSrvc->SelectAggregate($featuresId, $className, $aggregateOptions);
                    if($dataReader->ReadNext())
                    {
                        // Get the extents information
                        $byteReader = $dataReader->GetGeometry('COMP_EXTENT');
                        $extentGeometryAgg = $agfReaderWriter->Read($byteReader);
                    }
                    $dataReader->Close();
                }
                catch (MgException $e2) 
                {
                    
                }
            }
        }
        
        $mbr->extentGeometry = null;
        // Prefer SpatialExtents() of EXTENT() result over spatial context extent
        if ($extentGeometryAgg != null)
            $mbr->extentGeometry = $extentGeometryAgg;
        if ($mbr->extentGeometry == null) { //Stil null? Now try spatial context
            if ($extentGeometrySc != null)
                $mbr->extentGeometry = $extentGeometrySc;
        }
        return $mbr;
    }

    private function GetLayerBBOX($featSvc, $resSvc, $ldfId) {
        $br = $resSvc->GetResourceContent($ldfId);
        $doc = new DOMDocument();
        $doc->loadXML($br->ToString());

        $fsIdStr = $doc->getElementsByTagName("ResourceId")->item(0)->nodeValue;
        $featureClass = $doc->getElementsByTagName("FeatureName")->item(0)->nodeValue;
        $geom = $doc->getElementsByTagName("Geometry")->item(0)->nodeValue;
        $fsId = new MgResourceIdentifier($fsIdStr);
        $tokens = explode(":", $featureClass);

        $clsDef = $featSvc->GetClassDefinition($fsId, $tokens[0], $tokens[1]);
        $clsProps = $clsDef->GetProperties();
        $geomProp = $clsProps->GetItem($geom);

        $mbr = $this->GetFeatureClassMBR($fsId, $featureClass, $geomProp, $featSvc);
        // Get the coordinates
        $iterator = $mbr->extentGeometry->GetCoordinates();
        $firstTime = true;
        while($iterator->MoveNext())
        {
            $x = $iterator->GetCurrent()->GetX();
            $y = $iterator->GetCurrent()->GetY();
            if($firstTime)
            {
                $maxX = $x;
                $minX = $x;
                $maxY = $y;
                $minY = $y;
                $firstTime = false;
            }
            if($maxX<$x)
                $maxX = $x;
            if($minX>$x||$minX==0)
                $minX = $x;
            if($maxY<$y)
                $maxY = $y;
            if($minY>$y||$minY==0)
                $minY = $y;
        }

        $bbox = new stdClass();
        $bbox->minx = $minX;
        $bbox->miny = $minY;
        $bbox->maxx = $maxX;
        $bbox->maxy = $maxY;
        $bbox->coordsys = $mbr->coordinateSystem;

        return $bbox;
    }

    private function CreateLayerXmlFragment($ldfId) {
        return sprintf("<MapLayer><Name>%s</Name><ResourceId>%s</ResourceId><Selectable>true</Selectable><ShowInLegend>true</ShowInLegend><LegendLabel>%s</LegendLabel><ExpandInLegend>true</ExpandInLegend><Visible>true</Visible><Group /></MapLayer>",
            $ldfId->GetName(),
            $ldfId->ToString(),
            $ldfId->GetName());
    }

    private function CreateWatermarkFragment($resId) {
        return sprintf("<Watermarks><Watermark><Name>%s</Name><ResourceId>%s</ResourceId></Watermark></Watermarks>",
            $resId->GetName(),
            $resId->ToString());
    }

    const XY_COORDSYS = 'LOCAL_CS["Non-Earth (Meter)",LOCAL_DATUM["Local Datum",0],UNIT["Meter", 1],AXIS["X",EAST],AXIS["Y",NORTH]]';

    public function LaunchResourcePreview($resId) {
        $sessionId = $this->app->request->params("session");
        $this->EnsureAuthenticationForSite($sessionId, true);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);
        if ($sessionId == null) {
            $site = $siteConn->GetSite();
            $sessionId = $site->CreateSession();
        }
        $selfUrl = $this->app->config("SelfUrl");
        switch ($resId->GetResourceType()) {
            case MgResourceType::FeatureSource:
                {
                    $this->app->redirect("$selfUrl/../schemareport/describeschema.php?viewer=basic&schemaName=&className=&resId=".$resId->ToString()."&sessionId=".$sessionId);
                }
                break;
            case MgResourceType::LayerDefinition:
                {
                    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                    $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
                    $bbox = $this->GetLayerBBOX($featSvc, $resSvc, $resId);
                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_mapdefinition.xml");
                    $content = sprintf($content, $bbox->coordsys, $bbox->minx, $bbox->maxx, $bbox->miny, $bbox->maxy, $this->CreateLayerXmlFragment($resId), "");
                    //echo $content; die;
                    $mdfSource = new MgByteSource($content, strlen($content));
                    $mdfbr = $mdfSource->GetReader();
                    $mdfPreviewId = new MgResourceIdentifier("Session:$sessionId//Preview.MapDefinition");
                    $resSvc->SetResource($mdfPreviewId, $mdfbr, NULL);

                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_weblayout.xml");
                    $content = sprintf($content, $mdfPreviewId->ToString());
                    $source = new MgByteSource($content, strlen($content));
                    $br = $source->GetReader();
                    $previewId = new MgResourceIdentifier("Session:$sessionId//Preview.WebLayout");

                    $resSvc->SetResource($previewId, $br, NULL);
                    $this->app->redirect("$selfUrl/../mapviewerajax/?SESSION=$sessionId&USERNAME=Anonymous&WEBLAYOUT=".$previewId->ToString());
                }
                break;
            case MgResourceType::MapDefinition:
                {
                    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                    //$map = new MgMap();
                    //$map->Create($resSvc, $mdfId, $resId->GetName());
                    //$sel = new MgSelection($map);
                    //$sel->Save($resSvc, $resId->GetName());
                    //$map->Save($resSvc, $resId);

                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_weblayout.xml");
                    $content = sprintf($content, $resId->ToString());
                    $source = new MgByteSource($content, strlen($content));
                    $br = $source->GetReader();
                    $previewId = new MgResourceIdentifier("Session:$sessionId//Preview.WebLayout");

                    $resSvc->SetResource($previewId, $br, NULL);
                    $this->app->redirect("$selfUrl/../mapviewerajax/?SESSION=$sessionId&USERNAME=Anonymous&WEBLAYOUT=".$previewId->ToString());
                }
                break;
            case MgResourceType::SymbolDefinition:
                {
                    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                    $mapSvc = $siteConn->CreateService(MgServiceType::MappingService);
                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_symbollayer.xml");
                    $content = sprintf($content, $resId->ToString());

                    $ldfSource = new MgByteSource($content, strlen($content));
                    $ldfBr = $ldfSource->GetReader();
                    $ldfId = new MgResourceIdentifier("Session:$sessionId//SymbolPreview.LayerDefinition");
                    $resSvc->SetResource($ldfId, $ldfBr, NULL);

                    $br = $mapSvc->GenerateLegendImage($ldfId, 42, 100, 50, "PNG", 4, 0); //NOXLATE

                    $this->OutputByteReader($br);
                }
                break;
            case "WatermarkDefinition": //Really?
                {
                    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_mapdefinition.xml");
                    $content = sprintf($content, self::XY_COORDSYS, 0, 0, 0, 0, "", $this->CreateWatermarkFragment($resId));
                    //echo $content; die;
                    $mdfSource = new MgByteSource($content, strlen($content));
                    $mdfbr = $mdfSource->GetReader();
                    $mdfPreviewId = new MgResourceIdentifier("Session:$sessionId//Preview.MapDefinition");
                    $resSvc->SetResource($mdfPreviewId, $mdfbr, NULL);

                    $content = file_get_contents(dirname(__FILE__)."/../res/preview_weblayout.xml");
                    $content = sprintf($content, $mdfPreviewId->ToString());
                    $source = new MgByteSource($content, strlen($content));
                    $br = $source->GetReader();
                    $previewId = new MgResourceIdentifier("Session:$sessionId//Preview.WebLayout");

                    $resSvc->SetResource($previewId, $br, NULL);
                    $this->app->redirect("$selfUrl/../mapviewerajax/?SESSION=$sessionId&USERNAME=Anonymous&WEBLAYOUT=".$previewId->ToString());
                }
                break;
            default:
                $this->app->halt(400, "Resource type is not previewable");
                break;
        }
    }
}

?>