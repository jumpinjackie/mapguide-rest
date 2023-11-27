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
require_once "mappingservicecontroller.php";
require_once dirname(__FILE__)."/../util/readerchunkedresult.php";
require_once dirname(__FILE__)."/../util/selectionrenderer.php";

class MgMapController extends MgBaseController {
    public function __construct(IAppServices $app) {
        parent::__construct($app);
    }

    const RenderSelection   = 1;
    const RenderLayers      = 2;
    const KeepSelection     = 4;
    const RenderBaseLayers  = 8;

    private function TranslateToSelectionXml(MgSiteConnection $siteConn, /*php_string*/ $mapName, /*php_string*/ $featFilter, /*php_bool*/ $bAppend) {
        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $sel = new MgSelection($map);
        $layers = $map->GetLayers();

        //If appending, load the current selection first
        if ($bAppend) {
            $sel->Open($resSvc, $mapName);
        }

        $doc = new DOMDocument();
        $doc->loadXML($featFilter);

        /*
        Document structure

        /SelectionUpdate
            /Layer
                /Name
                /Feature [0...n]
                    /ID
                        /Name
                        /Value
                /SelectionFilter [0...n]
         */
        $root = $doc->documentElement;
        if ($root->tagName != "SelectionUpdate") {
            $this->BadRequest($this->app->GetLocalizedText("E_INVALID_DOCUMENT"), MgMimeType::Xml);
        }
        $layerNodes = $root->childNodes;
        for ($i = 0; $i < $layerNodes->length; $i++) {
            $layerNode = $layerNodes->item($i);
            if ($layerNode->tagName == "Layer") {
                //$this->app->LogDebug("Found //SelectionUpdate/Layer");
                $featureNodes = $layerNode->childNodes;
                for ($j = 0; $j < $featureNodes->length; $j++) {
                    $featureNode = $featureNodes->item($j);
                    if ($featureNode->tagName == "Name") {
                        //$this->app->LogDebug("Found //SelectionUpdate/Layer/Name");
                        $layerName = $featureNode->nodeValue;
                        $lidx = $layers->IndexOf($layerName);
                        if ($lidx < 0)
                            $this->BadRequest($this->app->GetLocalizedText("E_LAYER_NOT_FOUND_IN_MAP", $layerName), MgMimeType::Xml);

                        $layer = $layers->GetItem($lidx);
                        $clsDef = $layer->GetClassDefinition();
                        $clsIdProps = $clsDef->GetIdentityProperties();
                    } else if ($featureNode->tagName == "SelectionFilter") {
                        $query = new MgFeatureQueryOptions();
                        $query->SetFilter($featureNode->nodeValue);
                        $fr = $layer->SelectFeatures($query);
                        $sel->AddFeatures($layer, $fr, 0);
                    } else if ($featureNode->tagName == "Feature") {
                        //$this->app->LogDebug("Found //SelectionUpdate/Layer/Feature");
                        $idNodes = $featureNode->childNodes;
                        if ($idNodes->length == 1) {
                            $idNode = $idNodes->item(0);
                            if ($idNode->tagName == "ID") {
                                //$this->app->LogDebug("Found //SelectionUpdate/Layer/Feature/ID");
                                $nameNode = null;
                                $valueNode = null;
                                for ($nv = 0; $nv < $idNode->childNodes->length; $nv++) {
                                    $children = $idNode->childNodes;
                                    $child = $children->item($nv);
                                    if ($child->tagName == "Name") {
                                        //$this->app->LogDebug("Found //SelectionUpdate/Layer/Feature/ID/Name");
                                        $nameNode = $child;
                                    } else if ($child->tagName == "Value") {
                                        //$this->app->LogDebug("Found //SelectionUpdate/Layer/Feature/ID/Value");
                                        $valueNode = $child;
                                    }
                                }

                                //Name/Value nodes must be specified
                                if ($nameNode == null || $valueNode == null)
                                    $this->BadRequest($this->app->GetLocalizedText("E_INVALID_DOCUMENT"), MgMimeType::Xml);

                                //Property must exist
                                $pidx = $clsIdProps->IndexOf($nameNode->nodeValue);
                                if ($pidx < 0)
                                    $this->BadRequest($this->app->GetLocalizedText("E_PROPERTY_NOT_FOUND_IN_CLASS", $nameNode->nodeValue, $clsDef->GetName()), MgMimeType::Xml);

                                $propDef = $clsIdProps->GetItem($pidx);
                                $value = $valueNode->nodeValue;
                                $propType = $propDef->GetDataType();
                                //$this->app->LogDebug("Value is: $value");
                                //$this->app->LogDebug("Property type: $propType");
                                switch ($propType) {
                                    case MgPropertyType::Int16:
                                        //$this->app->LogDebug("=== ADD INT16: $value ===");
                                        $sel->AddFeatureIdInt16($layer, $layer->GetFeatureClassName(), intval($value));
                                        break;
                                    case MgPropertyType::Int32:
                                        //$this->app->LogDebug("=== ADD INT32: $value ===");
                                        $sel->AddFeatureIdInt32($layer, $layer->GetFeatureClassName(), intval($value));
                                        break;
                                    case MgPropertyType::Int64:
                                        //$this->app->LogDebug("=== ADD INT64: $value ===");
                                        $sel->AddFeatureIdInt64($layer, $layer->GetFeatureClassName(), intval($value));
                                        break;
                                    case MgPropertyType::String:
                                        //$this->app->LogDebug("=== ADD STRING: $value ===");
                                        $sel->AddFeatureIdString($layer, $layer->GetFeatureClassName(), $value);
                                        break;
                                    case MgPropertyType::Single:
                                    case MgPropertyType::Double:
                                        //$this->app->LogDebug("=== ADD DOUBLE: $value ===");
                                        $sel->AddFeatureIdInt64($layer, $layer->GetFeatureClassName(), floatval($value));
                                        break;
                                    //case MgPropertyType::DateTime:
                                    //    break;
                                }
                            }
                        } else if ($idNodes->length > 1) {
                            throw new Exception($this->app->GetLocalizedText("E_MULTIPLE_IDENTITY_PROPS_NOT_SUPPORTED"));
                        }
                    }
                }
            }
        }
        return $sel->ToXml();
    }

    private function AppendSelectionXml(MgSiteConnection $siteConn, /*php_string*/ $mapName, /*php_string*/ $featFilter) {
        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $sel = new MgSelection($map);
        $sel->Open($resSvc, $mapName);

        $sel2 = new MgSelection($map, $featFilter);
        MgUtils::MergeSelections($sel, $sel2);
        return $sel->ToXml();
    }

// ================================ NOTE ====================================== //
// This was supposed to be the implementation of stateless QueryMapFeatures, but
// unfortunately the MapGuide API does not provide the sufficient functionality for
// this to be possible, namely the ability to set width/height/dpi/center/scale of
// an MgMap instance directly. We can't use the MgHttpRequest/MgHttpResponse/GETDYNAMICMAPOVERLAYIMAGE
// trick to set these parameters as that requires a session-based map, which we
// are trying to avoid in the first place!

// ====================================== Miscellaneous APIs ============================================== //
/**
 *     @SWG\Post(
 *        path="/library/{resourcePath}.MapDefinition/query.{type}",
 *        operationId="QueryFeaturesStatelessly",
 *        summary="Performs a selection query against a Map Definition. ",
 *        tags={"library"},
 *          @SWG\Parameter(name="session", in="formData", required=false, type="string", description="Your MapGuide Session ID"),
 *          @SWG\Parameter(name="username", in="formData", required=false, type="string", description="The MapGuide username"),
 *          @SWG\Parameter(name="password", in="formData", required=false, type="string", description="The password"),
 *          @SWG\Parameter(name="type", in="path", required=true, type="string", description="xml or json", enum={"json", "xml"}),
 *          @SWG\Parameter(name="resourcePath", in="path", required=true, type="string", description="The path of the Map Definition"),
 *          @SWG\Parameter(name="layernames", in="formData", required=false, type="string", description="A comma-separated list of layer names"),
 *          @SWG\Parameter(name="geometry", in="formData", required=false, type="string", description="The WKT of the intersecting geometry"),
 *          @SWG\Parameter(name="maxfeatures", in="formData", required=false, type="integer", description="The maximum number features to select as a result of this operation"),
 *          @SWG\Parameter(name="selectionvariant", in="formData", required=true, type="string", description="The geometry operator to apply", enum={"TOUCHES", "INTERSECTS", "WITHIN", "ENVELOPEINTERSECTS"}),
 *          @SWG\Parameter(name="selectioncolor", in="formData", required=false, type="string", description="The selection color"),
 *          @SWG\Parameter(name="selectionformat", in="formData", required=false, type="string", description="The selection image format", enum={"PNG", "PNG8", "JPG", "GIF"}),
 *          @SWG\Parameter(name="requestdata", in="formData", required=true, type="string", description="A bitmask specifying the information to return in the response. 1=Attributes, 2=Inline Selection, 4=Tooltip, 8=Hyperlink"),
 *          @SWG\Parameter(name="layerattributefilter", in="formData", required=false, type="string", description="Bitmask value determining which layers will be queried. 1=Visible, 2=Selectable, 4=HasTooltips"),
 *          @SWG\Parameter(name="selection", in="formData", required=false, type="string", description="An XML selection string containing the required feature IDs"),
 *          @SWG\Parameter(name="append", in="formData", required=false, type="boolean", description="Indicates if the this query selection indicated by the 'featurefilter' parameter should append to the current selection"),
 *        @SWG\Response(response=400, description="You supplied a bad request due to one or more missing or invalid parameters"),
 *        @SWG\Response(response=401, description="Session ID or MapGuide credentials not specified"),
 *        @SWG\Response(response=500, description="An error occurred during the operation")
 *     )
 */
/*
$app->post("/library/:resourcePath+.MapDefinition/query.:format", function($resourcePath, $format) use ($app) {
    $count = count($resourcePath);
    if ($count > 0) {
        $resourcePath[$count - 1] = $resourcePath[$count - 1].".MapDefinition";
    }
    $resId = MgUtils::ParseLibraryResourceID($resourcePath);
    $ctrl = new MgMapController($app);
    $ctrl->QueryMapDefinitionFeatures($resId, $format);
});

    public function QueryMapDefinitionFeatures($resId, $format) {
        $format = $this->ValidateRepresentation($format, array("xml", "json"));

        $layerNames = $this->app->GetRequestParameter("layernames");
        $geometry = $this->app->GetRequestParameter("geometry");
        $maxFeatures = $this->app->GetRequestParameter("maxfeatures");
        $selVariant = $this->app->GetRequestParameter("selectionvariant");
        $selColor = $this->app->GetRequestParameter("selectioncolor");
        $selFormat = $this->app->GetRequestParameter("selectionformat");
        $reqData = $this->app->GetRequestParameter("requestdata");
        $featFilter = $this->app->GetRequestParameter("featurefilter");
        $bAppend = $this->app->GetRequestParameter("append");

        $layerAttFilter = $this->app->GetRequestParameter("layerattributefilter");
        $format = $this->app->GetRequestParameter("format");

        //Convert or coerce to defaults
        if ($maxFeatures == null)
            $maxFeatures = -1;
        else
            $maxFeatures = intval($maxFeatures);

        if ($selFormat == null)
            $selFormat = "PNG";
        else
            $selFormat = strtoupper($selFormat);

        if ($layerAttFilter == null)
            $layerAttFilter = 3; //visible and selectable
        else
            $layerAttFilter = intval($layerAttFilter);

        if ($bAppend == null)
            $bAppend = false;
        else
            $bAppend = ($bAppend == "1" || $bAppend == "true");

        if ($reqData == null)
            $reqData = 0;
        else
            $reqData = intval($reqData);

        if ($selColor == null)
            $selColor = "0x0000FFFF";

        $this->TrySetCredentialsFromRequest();
        try {
            $this->EnsureAuthenticationForSite();
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $map = new MgMap($siteConn);
            $map->Create($resId, $resId->GetName());
            $selection = new MgSelection($map);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);
            $updater = new MgNullSelectionUpdater();
            $renderer = new MgStatelessSelectionRenderer();

            $this->QueryMapFeaturesInternal($map,
                                            $selection,
                                            $resSvc,
                                            $renderSvc,
                                            $layerNames,
                                            $selVariant,
                                            $geometry,
                                            $featFilter,
                                            $maxFeatures,
                                            $layerAttFilter,
                                            $reqData,
                                            $selColor,
                                            $selFormat,
                                            $bAppend,
                                            $format,
                                            $updater,
                                            $renderer);

        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }
    */

    public function QueryMapFeatures(/*php_string*/ $sessionId, /*php_string*/ $mapName) {
        //TODO: Append only works in featurefilter mode. Add append support for geometry-based selections
        $layerNames = $this->app->GetRequestParameter("layernames");
        $geometry = $this->app->GetRequestParameter("geometry");
        $maxFeatures = $this->app->GetRequestParameter("maxfeatures");
        $selVariant = $this->app->GetRequestParameter("selectionvariant");
        $selColor = $this->app->GetRequestParameter("selectioncolor");
        $selFormat = $this->app->GetRequestParameter("selectionformat");
        $persist = $this->app->GetRequestParameter("persist");
        $reqData = $this->app->GetRequestParameter("requestdata");
        $featFilter = $this->app->GetRequestParameter("featurefilter");

        $bSelectionXml = $this->app->GetRequestParameter("selectionxml");
        $bAppend = $this->app->GetRequestParameter("append");

        $layerAttFilter = $this->app->GetRequestParameter("layerattributefilter");
        $format = $this->app->GetRequestParameter("format");

        //Convert or coerce to defaults
        if ($format == null)
            $format = "xml";
        else
            $format = strtolower($format);

        if ($maxFeatures == null)
            $maxFeatures = -1;
        else
            $maxFeatures = intval($maxFeatures);

        if ($selFormat == null)
            $selFormat = "PNG";
        else
            $selFormat = strtoupper($selFormat);

        if ($layerAttFilter == null)
            $layerAttFilter = 3; //visible and selectable
        else
            $layerAttFilter = intval($layerAttFilter);

        if ($persist == null)
            $persist = true;
        else
            $persist = ($persist == "1" || $persist == "true");

        if ($bSelectionXml == null)
            $bSelectionXml = true;
        else
            $bSelectionXml = ($bSelectionXml == "1" || $bSelectionXml == "true");

        if ($bAppend == null)
            $bAppend = false;
        else
            $bAppend = ($bAppend == "1" || $bAppend == "true");

        if ($reqData == null)
            $reqData = 0;
        else
            $reqData = intval($reqData);

        if ($selColor == null)
            $selColor = "0x0000FFFF";

        try {
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $admin = new MgServerAdmin();
            $admin->Open($this->userInfo);
            $version = explode(".", $admin->GetSiteVersion());
            $bCanUseNative = false;
            //If appending, we can't use the native operation as that operation does not support appending
            if (intval($version[0]) > 2) { //3.0 or greater
                $bCanUseNative = !$bAppend;
            } else if (intval($version[0]) == 2 && intval($version[1]) >= 6) { //2.6 or greater
                $bCanUseNative = !$bAppend;
            }

            //$this->app->LogDebug("APPEND: $bAppend");
            //$this->app->LogDebug("FILTER (Before): $featFilter");

            if (!$bSelectionXml) {
                //Append only works in the absence of geometry
                if ($geometry == null && $featFilter != null) {
                    $featFilter = $this->TranslateToSelectionXml($siteConn, $mapName, $featFilter, $bAppend);
                    //$this->app->LogDebug("FeatFilter: $featFilter");
                }
            } else {
                //Append only works in the absence of geometry
                if ($geometry == null && $bAppend) {
                    $featFilter = $this->AppendSelectionXml($siteConn, $mapName, $featFilter);
                }
            }

            //$this->app->LogDebug("GEOMETRY: $geometry");
            //$this->app->LogDebug("FILTER: $featFilter");
            //$this->app->LogDebug("Can use native: $bCanUseNative");
            if ($bCanUseNative) {
                $req = new MgHttpRequest("");
                $param = $req->GetRequestParam();

                $param->AddParameter("OPERATION", "QUERYMAPFEATURES");
                $param->AddParameter("VERSION", "2.6.0");
                $param->AddParameter("SESSION", $sessionId);
                $param->AddParameter("MAPNAME", $mapName);

                $param->AddParameter("GEOMETRY", $geometry);
                $param->AddParameter("SELECTIONVARIANT", $selVariant);
                $param->AddParameter("MAXFEATURES", $maxFeatures);
                $param->AddParameter("LAYERNAMES", $layerNames);
                $param->AddParameter("PERSIST", $persist ? "1" : "0");
                $param->AddParameter("LAYERATTRIBUTEFILTER", $layerAttFilter);
                if ($featFilter != null)
                    $param->AddParameter("FEATUREFILTER", $featFilter);

                $param->AddParameter("REQUESTDATA", $reqData);
                $param->AddParameter("SELECTIONCOLOR", $selColor);
                $param->AddParameter("SELECTIONFORMAT", $selFormat);

                if ($format === "json")
                    $param->AddParameter("FORMAT", MgMimeType::Json);
                else
                    $param->AddParameter("FORMAT", MgMimeType::Xml);
                $this->ExecuteHttpRequest($req);
            } else { //Shim the response
                $map = new MgMap($siteConn);
                $map->Open($mapName);
                $selection = new MgSelection($map);

                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
                $renderSvc = $siteConn->CreateService(MgServiceType::RenderingService);
                $updater = new MgSelectionUpdater($map, $resSvc, $mapName, $persist);
                $renderer = new MgSelectionRenderer();

                $this->QueryMapFeaturesInternal($map,
                                                $selection,
                                                $resSvc,
                                                $renderSvc,
                                                $layerNames,
                                                $selVariant,
                                                $geometry,
                                                $featFilter,
                                                $maxFeatures,
                                                $layerAttFilter,
                                                $reqData,
                                                $selColor,
                                                $selFormat,
                                                $bAppend,
                                                $format,
                                                $updater,
                                                $renderer);
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }

    private function QueryMapFeaturesInternal(MgMap $map,
                                              MgSelection $selection,
                                              MgResourceService $resSvc,
                                              MgRenderingService $renderSvc,
                                              /*php_string*/ $layerNames,
                                              /*php_string*/ $selVariant,
                                              /*php_string*/ $geometry,
                                              /*php_string*/ $featFilter,
                                              /*php_int*/ $maxFeatures,
                                              /*php_int*/ $layerAttFilter,
                                              /*php_int*/ $reqData,
                                              /*php_string*/ $selColor,
                                              /*php_string*/ $selFormat,
                                              /*php_bool*/ $bAppend,
                                              /*php_string*/ $format,
                                              MgSelectionUpdaterBase $updater,
                                              MgSelectionRenderer $renderer) {
        $layersToQuery = null;
        if ($layerNames != null) {
            $layersToQuery = new MgStringCollection();
            $names = explode(",", $layerNames);
            foreach ($names as $name) {
                $layersToQuery->Add($name);
            }
        }

        $variant = 0;
        if ($selVariant === "TOUCHES")
            $variant = MgFeatureSpatialOperations::Touches;
        else if ($selVariant === "INTERSECTS")
            $variant = MgFeatureSpatialOperations::Intersects;
        else if ($selVariant === "WITHIN")
            $variant = MgFeatureSpatialOperations::Within;
        else if ($selVariant === "ENVELOPEINTERSECTS")
            $variant = MgFeatureSpatialOperations::EnvelopeIntersects;

        $wktRw = new MgWktReaderWriter();
        $selectGeom = null;
        if ($geometry != null)
            $selectGeom = $wktRw->Read($geometry);

        $featInfo = $renderSvc->QueryFeatures($map, $layersToQuery, $selectGeom, $variant, $featFilter, $maxFeatures, $layerAttFilter);

        $updater->Update($selection, $featInfo, $bAppend);

        // Render an image of this selection if requested
        $inlineSelectionImg = null;
        if ((($reqData & MgSelectionRequestedFeatures::REQUEST_INLINE_SELECTION) == MgSelectionRequestedFeatures::REQUEST_INLINE_SELECTION) && $updater->HasNewSelection()) {
            $color = new MgColor($selColor);
            $renderOpts = new MgRenderingOptions($selFormat, self::RenderSelection | self::KeepSelection, $color);
            $inlineSelectionImg = $renderSvc->RenderDynamicOverlay($map, $selection, $renderOpts);
        }

        // Collect any attributes of selected features
        $bRequestAttributes = (($reqData & MgSelectionRequestedFeatures::REQUEST_ATTRIBUTES) == MgSelectionRequestedFeatures::REQUEST_ATTRIBUTES);

        $xml = $renderer->Render($resSvc, $reqData, $featInfo, $selection, $bRequestAttributes, $inlineSelectionImg);

        $bs = new MgByteSource($xml, strlen($xml));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function CreateMap(MgResourceIdentifier $resId) {
        $mdfIdStr = $this->app->GetRequestParameter("mapdefinition");
        if ($mdfIdStr == null) {
            $this->BadRequest($this->app->GetLocalizedText("E_MISSING_REQUIRED_PARAMETER", "mapdefinition"), $this->GetMimeTypeForFormat($format));
        } else {
            $mdfId = new MgResourceIdentifier($mdfIdStr);
            if ($mdfId->GetResourceType() != MgResourceType::MapDefinition) {
                $this->BadRequest($this->app->GetLocalizedText("E_INVALID_MAP_DEFINITION_PARAMETER", "mapdefinition"), $this->GetMimeTypeForFormat($format));
            } else {
                //$this->EnsureAuthenticationForSite();
                $userInfo = new MgUserInformation($resId->GetRepositoryName());
                $siteConn = new MgSiteConnection();
                $siteConn->Open($userInfo);
                $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

                $map = new MgMap();
                $map->Create($resSvc, $mdfId, $resId->GetName());
                $sel = new MgSelection($map);
                $sel->Save($resSvc, $resId->GetName());
                $map->Save($resSvc, $resId);

                $this->app->SetResponseStatus(201);
                $this->app->SetResponseBody(MgUtils::GetNamedRoute($this->app, "/session", "session_resource_id", array("sessionId" => $resId->GetRepositoryName(), "resName" => $resId->GetName().".".$resId->GetResourceType())));
            }
        }
    }

    public function EnumerateMapLayers(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $format) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $reqFeatures = $this->app->GetRequestParameter("requestedfeatures");
        $iconFormat = $this->app->GetRequestParameter("iconformat");
        $iconWidth = $this->app->GetRequestParameter("iconwidth");
        $iconHeight = $this->app->GetRequestParameter("iconheight");
        $iconsPerScaleRange = $this->app->GetRequestParameter("iconsperscalerange");
        $groupName = $this->app->GetRequestParameter("group");

        //Assign default values or coerce existing ones to their expected types
        if ($reqFeatures != null) {
            $reqFeatures = intval($reqFeatures);
        }

        if ($iconFormat == null) {
            $iconFormat = "PNG";
        }

        if ($iconWidth == null) {
            $iconWidth = 16;
        } else {
            $iconWidth = intval($iconWidth);
        }

        if ($iconHeight == null) {
            $iconHeight = 16;
        } else {
            $iconHeight = intval($iconHeight);
        }

        if ($iconsPerScaleRange == null) {
            $iconsPerScaleRange = 25;
        } else {
            $iconsPerScaleRange = intval($iconsPerScaleRange);
        }

        if ($format == null) {
            $format = "xml";
        } else {
            $format = strtolower($format);
        }

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $layers = $map->GetLayers();
        $layerCount = $layers->GetCount();

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $mappingSvc = $siteConn->CreateService(MgServiceType::MappingService);

        $output = "<LayerCollection>";
        $layerDefinitionMap = array();
        //Build our LayerDefinition map for code below that requires it
        if (($reqFeatures & MgMappingServiceController::REQUEST_LAYER_ICONS) == MgMappingServiceController::REQUEST_LAYER_ICONS) {
            $layerIds = new MgStringCollection();
            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $parent = $layer->GetGroup();
                if ($groupName != null && $parent != null && $parent->GetName() != $groupName)
                    continue;
                $ldfId = $layer->GetLayerDefinition();
                $layerIds->Add($ldfId->ToString());
            }

            $layerContents = $resSvc->GetResourceContents($layerIds, null);
            $layerIdCount = $layerIds->GetCount();
            for ($i = 0; $i < $layerIdCount; $i++) {
                $ldfId = $layerIds->GetItem($i);
                $content = $layerContents->GetItem($i);
                $layerDefinitionMap[$ldfId] = $content;
            }
        }
        // ----------- Some pre-processing before we do groups/layers ------------- //
        $doc = new DOMDocument();
        for ($i = 0; $i < $layerCount; $i++) {
            $layer = $layers->GetItem($i);
            $parent = $layer->GetGroup();
            if ($groupName != null && $parent != null && $parent->GetName() != $groupName)
                continue;
            $ldf = $layer->GetLayerDefinition();
            $layerId = $ldf->ToString();

            $layerDoc = null;
            if (array_key_exists($layerId, $layerDefinitionMap)) {
                $doc->loadXML($layerDefinitionMap[$layerId]);
                $layerDoc = $doc;
            }

            $output .= MgMappingServiceController::CreateLayerItem($reqFeatures, $iconsPerScaleRange, $iconFormat, $iconWidth, $iconHeight, $layer, $parent, $layerDoc, $mappingSvc);
        }
        $output .= "</LayerCollection>";

        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function EnumerateMapLayerGroups(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $format) {
        $userInfo = new MgUserInformation($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($userInfo);

        $map = new MgMap($siteConn);
        $map->Open($mapName);

        $groups = $map->GetLayerGroups();
        $groupCount = $groups->GetCount();

        $output = "<GroupCollection>";
        for ($i = 0; $i < $groupCount; $i++) {
            $group = $groups->GetItem($i);
            $parent = $group->GetGroup();
            $output .= MgMappingServiceController::CreateGroupItem($group, $parent);
        }
        $output .= "</GroupCollection>";

        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function GetSelectionXml(/*php_string*/ $sessionId, /*php_string*/ $mapName) {
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $this->app->SetResponseHeader("Content-Type", MgMimeType::Xml);
        $this->app->WriteResponseContent($selection->ToXml());
    }

    public function GetSelectionOverview(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $bIncludeBounds = $this->GetBooleanRequestParameter("bounds", false);

        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
        $featSvc = null;
        if ($bIncludeBounds) {
            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);
        }

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $output = "<SelectionOverview>";

        if ($bIncludeBounds) {
            $env = $selection->GetExtents($featSvc);
            if ($env != null && !$env->IsNull()) {
                $ll = $env->GetLowerLeftCoordinate();
                $ur = $env->GetUpperRightCoordinate();
                $output .= "<Bounds>";

                $output .= "<MinX>";
                $output .= $ll->GetX();
                $output .= "</MinX>";

                $output .= "<MinY>";
                $output .= $ll->GetY();
                $output .= "</MinY>";

                $output .= "<MaxX>";
                $output .= $ur->GetX();
                $output .= "</MaxX>";

                $output .= "<MaxY>";
                $output .= $ur->GetY();
                $output .= "</MaxY>";

                $output .= "</Bounds>";
            }
        }

        $layers = $selection->GetLayers();
        if ($layers != NULL) {
            for ($i = 0; $i < $layers->GetCount(); $i++) {
                $layer = $layers->GetItem($i);
                $layerName = $layer->GetName();

                $output .= "<Layer>";

                $output .= "<Name>";
                $output .= $layerName;
                $output .= "</Name>";
                $output .= "<SelectionCount>";
                $output .= $selection->GetSelectedFeaturesCount($layer, $layer->GetFeatureClassName());
                $output .= "</SelectionCount>";
                $output .= "<FeaturesUrl>";
                $innerFormat = ($format == "json" ? "geojson" : $format);
                $output .= MgUtils::GetNamedRoute($this->app, "/session", "get_selected_features", array("sessionId" => $sessionId, "mapName" => $mapName, "layerName" => $layerName, "format" => $innerFormat));
                $output .= "</FeaturesUrl>";

                $output .= "</Layer>";
            }
        }

        $output .= "</SelectionOverview>";

        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($format == "json") {
            $this->OutputXmlByteReaderAsJson($br);
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function GetSelectionLayerNames(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $selection = new MgSelection($map);
        $selection->Open($resSvc, $mapName);

        $layers = $selection->GetLayers();

        $output  = "<SelectedLayerCollection>";
        if ($layers != null) {
            $layerCount = $layers->GetCount();
            for ($i = 0; $i < $layerCount; $i++) {
                $layer = $layers->GetItem($i);
                $name = $layer->GetName();
                $count = $selection->GetSelectedFeaturesCount($layer, $layer->GetFeatureClassName());
                $objId = $layer->GetObjectId();
                $output .= "<SelectedLayer><Name>$name</Name><ObjectId>$objId</ObjectId><Count>$count</Count></SelectedLayer>";
            }
        }
        $output .= "</SelectedLayerCollection>";
        $bs = new MgByteSource($output, strlen($output));
        $bs->SetMimeType(MgMimeType::Xml);
        $br = $bs->GetReader();
        if ($fmt === "json") {
            if ($layers == null) {
                //HACK: Bug (?) in how xml2json processes empty tags
                $this->app->SetResponseHeader("Content-Type", MgMimeType::Json);
                $this->app->WriteResponseContent('{ "SelectedLayerCollection": [] }');
            } else {
                $this->OutputXmlByteReaderAsJson($br);
            }
        } else {
            $this->OutputByteReader($br);
        }
    }

    public function GetSelectedFeatures(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $layerName, /*php_string*/ $format) {
        $fmt = $this->ValidateRepresentation($format, array("xml", "geojson", "html"));

        $propList = $this->app->GetRequestParameter("properties", "");
        $pageSize = $this->app->GetRequestParameter("pagesize", -1);
        $pageNo = $this->app->GetRequestParameter("page", -1);
        $orientation = $this->app->GetRequestParameter("orientation", "h");

        try {
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
            $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

            $map = new MgMap($siteConn);
            $map->Open($mapName);
            $selection = new MgSelection($map);
            $selection->Open($resSvc, $mapName);

            $layers = $selection->GetLayers();
            if ($layers != null) {
                $lidx = -1;
                $layerCount = $layers->GetCount();
                for ($i = 0; $i < $layerCount; $i++) {
                    $currentlayer = $layers->GetItem($i);
                    if ($currentlayer->GetName() == $layerName) {
                        $lidx = $i;
                        break;
                    }
                }
                if ($lidx < 0) {
                    $this->NotFound($this->app->GetLocalizedText("E_LAYER_NOT_IN_SELECTION", $layerName), $this->GetMimeTypeForFormat($fmt));
                } else {
                    $layer = $layers->GetItem($lidx);
                    $bMapped = ($this->GetBooleanRequestParameter("mappedonly", "0") == "1");
                    $bIncludeGeom = ($this->GetBooleanRequestParameter("includegeom", "0") == "1");
                    $bDisplayProperties = ($this->GetBooleanRequestParameter("displayproperties", "0") == "1");
                    $transformto = $this->app->GetRequestParameter("transformto", "");
                    $transform = null;
                    if ($transformto !== "") {
                        $resId = new MgResourceIdentifier($layer->GetFeatureSourceId());
                        $tokens = explode(":", $layer->GetFeatureClassName());
                        $transform = MgUtils::GetTransform($featSvc, $resId, $tokens[0], $tokens[1], $transformto);
                    }

                    $owriter = new MgSlimChunkWriter($this->app);

                    $displayMap = array();

                    //NOTE: This does not do a query to ascertain a total, this is already a pre-computed property of the selection set.
                    $total = $selection->GetSelectedFeaturesCount($layer, $layer->GetFeatureClassName());
                    if (strlen($propList) > 0) {
                        $tokens = explode(",", $propList);
                        $propNames = new MgStringCollection();
                        foreach ($tokens as $propName) {
                            $propNames->Add($propName);
                        }
                        $reader = $selection->GetSelectedFeatures($layer, $layer->GetFeatureClassName(), $propNames);
                    } else {
                        if ($bMapped) {
                            $ldfId = $layer->GetLayerDefinition();
                            $ldfContent = $resSvc->GetResourceContent($ldfId);
                            $ldfDoc = new DOMDocument();
                            $ldfDoc->loadXML($ldfContent->ToString());
                            $mappings = $ldfDoc->getElementsByTagName("PropertyMapping");
                            $propNames = new MgStringCollection();
                            foreach ($mappings as $mapping) {
                                $nameNode = $mapping->getElementsByTagName("Name")->item(0);
                                $valueNode = $mapping->getElementsByTagName("Value")->item(0);
                                $propName = $nameNode->nodeValue;
                                $propNames->Add($propName);
                                $displayMap[$propName] = $valueNode->nodeValue;
                            }
                            if ($bIncludeGeom) {
                                $propNames->Add($layer->GetFeatureGeometryName());
                            }
                            $reader = $selection->GetSelectedFeatures($layer, $layer->GetFeatureClassName(), $propNames);
                        } else {
                            $reader = $selection->GetSelectedFeatures($layer, $layer->GetFeatureClassName(), false);
                        }
                    }
                    if ($pageSize > 0) {
                        $pageReader = new MgPaginatedFeatureReader($reader, $pageSize, $pageNo, $total);
                        $result = new MgReaderChunkedResult($featSvc, $pageReader, -1, $owriter);
                    } else {
                        $result = new MgReaderChunkedResult($featSvc, $reader, -1, $owriter);
                    }
                    if ($bDisplayProperties) {
                        $result->SetDisplayMappings($displayMap);
                    }
                    $result->CheckAndSetDownloadHeaders($this->app, $format);
                    if ($transform != null)
                        $result->SetTransform($transform);
                    if ($fmt === "html") {
                        $result->SetAttributeDisplayOrientation($orientation);
                        $result->SetHtmlParams($this->app);
                    }
                    $result->Output($format);
                }
            } else {
                $owriter = new MgSlimChunkWriter($this->app);
                $reader = new MgNullFeatureReader();
                $result = new MgReaderChunkedResult($featSvc, $reader, -1, $owriter);
                if ($fmt === "html") {
                    $result->SetAttributeDisplayOrientation($orientation);
                    $result->SetHtmlParams($this->app);
                }
                $result->Output($format);
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }

    public function UpdateSelectionFromXml(/*php_string*/ $sessionId, /*php_string*/ $mapName) {
        $this->EnsureAuthenticationForSite($sessionId);
        $siteConn = new MgSiteConnection();
        $siteConn->Open($this->userInfo);

        $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

        $map = new MgMap($siteConn);
        $map->Open($mapName);
        $xml = trim($this->app->GetRequestBody());
        $selection = new MgSelection($map);
        $selection->FromXml($xml);

        $selection->Save($resSvc, $mapName);
    }

    public function UpdateMapLayersAndGroups(/*php_string*/ $sessionId, /*php_string*/ $mapName, /*php_string*/ $format) {
        //Check for unsupported representations
        $fmt = $this->ValidateRepresentation($format, array("xml", "json"));
        try {
            $this->EnsureAuthenticationForSite($sessionId);
            $siteConn = new MgSiteConnection();
            $siteConn->Open($this->userInfo);

            $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);

            $map = new MgMap($siteConn);
            $map->Open($mapName);

            if ($fmt == "json") {
                $body = $this->app->GetRequestBody();
                $json = json_decode($body);
                if ($json == NULL)
                    throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
            } else {
                $body = $this->app->GetRequestBody();
                $jsonStr = MgUtils::Xml2Json($body);
                $json = json_decode($jsonStr);
            }

            if (!isset($json->UpdateMap)) {
                throw new Exception($this->app->GetLocalizedText("E_MALFORMED_JSON_BODY"));
            }

            /*
            Expected structure

            /UpdateMap
                /Operation [1...n]
                    /Type - [AddLayer|UpdateLayer|RemoveLayer|AddGroup|UpdateGroup|RemoveGroup]
                    /Name
                    /ResourceId
                    /SetLegendLabel
                    /SetDisplayInLegend
                    /SetExpandInLegend
                    /SetVisible
                    /SetSelectable
                    /InsertAt
            */

            $layers = $map->GetLayers();
            $groups = $map->GetLayerGroups();

            $um = $json->UpdateMap;
            $updateStats = new stdClass();
            $updateStats->AddedLayers = 0;
            $updateStats->UpdatedLayers = 0;
            $updateStats->RemovedLayers = 0;
            $updateStats->AddedGroups = 0;
            $updateStats->UpdatedGroups = 0;
            $updateStats->RemovedGroups = 0;

            $this->app->LogDebug("Operations found: ".count($um->Operation));

            for ($i = 0; $i < count($um->Operation); $i++) {
                $op = $um->Operation[$i];
                switch ($op->Type) {
                    case "AddLayer":
                    {
                        $resId = new MgResourceIdentifier($op->ResourceId);
                        $layer = new MgLayer($resId, $resSvc);
                        $layer->SetName($op->Name);
                        self::ApplyCommonLayerProperties($layer, $op, $groups);
                        if (isset($op->InsertAt)) {
                            $layers->Insert(intval($op->InsertAt), $layer);
                        } else {
                            $layers->Add($layer);
                        }
                        $this->app->LogDebug("Add Layer: ".$op->Name);
                        $updateStats->AddedLayers++;
                    }
                    break;
                    case "UpdateLayer":
                    {
                        $layer = $layers->GetItem($op->Name);

                        if (self::ApplyCommonLayerProperties($layer, $op, $groups)) {
                            $this->app->LogDebug("Updated Layer: ".$op->Name);
                            $updateStats->UpdatedLayers++;
                        }
                    }
                    break;
                    case "RemoveLayer": {
                        $layer = $layers->GetItem($op->Name);

                        if ($layers->Remove($layer)) {
                            $this->app->LogDebug("Removed Layer: ".$op->Name);
                            $updateStats->RemovedLayers++;
                        }
                    }
                    break;
                    case "AddGroup":
                    {
                        $group = new MgLayerGroup($op->Name);
                        self::ApplyCommonGroupProperties($group, $op, $groups);
                        if (isset($op->InsertAt)) {
                            $groups->Insert(intval($op->InsertAt), $group);
                        } else {
                            $groups->Add($group);
                        }
                        $this->app->LogDebug("Add Group: ".$op->Name);
                        $updateStats->AddedGroups++;
                    }
                    break;
                    case "UpdateGroup":
                    {
                        $gidx = $groups->IndexOf($op->Name);
                        if ($gidx < 0) {
                            if ($op->AddIfNotExists) {
                                $group = new MgLayerGroup($op->Name);
                                self::ApplyCommonGroupProperties($group, $op, $groups);
                                if (isset($op->InsertAt)) {
                                    $groups->Insert(intval($op->InsertAt), $group);
                                } else {
                                    $groups->Add($group);
                                }
                                $this->app->LogDebug("Add Group: ".$op->Name);
                                $updateStats->AddedGroups++;
                            } else {
                                throw new Exception($this->app->GetLocalizedText("E_GROUP_NOT_FOUND",$op->Name));
                            }
                        } else {
                            $group = $groups->GetItem($gidx);
                            if (self::ApplyCommonGroupProperties($group, $op, $groups)) {
                                $this->app->LogDebug("Updated Group: ".$op->Name);
                                $updateStats->UpdatedGroups++;
                            }
                        }
                    }
                    break;
                    case "RemoveGroup":
                    {
                        $group = $groups->GetItem($op->Name);

                        if ($groups->Remove($group)) {
                            $this->app->LogDebug("Removed Group: ".$op->Name);
                            $updateStats->RemovedGroups++;
                        }
                    }
                    break;
                }
            }

            if ($updateStats->AddedLayers > 0 ||
                $updateStats->UpdatedLayers > 0 ||
                $updateStats->RemovedLayers > 0 ||
                $updateStats->AddedGroups > 0 ||
                $updateStats->UpdatedGroups > 0 ||
                $updateStats->RemovedGroups > 0) {
                $map->Save();
            }

            $response = "<UpdateMapResult>";
            $response .= "<AddedLayers>";
            $response .= $updateStats->AddedLayers;
            $response .= "</AddedLayers>";
            $response .= "<UpdatedLayers>";
            $response .= $updateStats->UpdatedLayers;
            $response .= "</UpdatedLayers>";
            $response .= "<RemovedLayers>";
            $response .= $updateStats->RemovedLayers;
            $response .= "</RemovedLayers>";
            $response .= "<AddedGroups>";
            $response .= $updateStats->AddedGroups;
            $response .= "</AddedGroups>";
            $response .= "<UpdatedGroups>";
            $response .= $updateStats->UpdatedGroups;
            $response .= "</UpdatedGroups>";
            $response .= "<RemovedGroups>";
            $response .= $updateStats->RemovedGroups;
            $response .= "</RemovedGroups>";
            $response .= "</UpdateMapResult>";

            $bs = new MgByteSource($response, strlen($response));
            $bs->SetMimeType(MgMimeType::Xml);
            $br = $bs->GetReader();
            if ($format == "json") {
                $this->OutputXmlByteReaderAsJson($br);
            } else {
                $this->OutputByteReader($br);
            }
        } catch (MgException $ex) {
            $this->OnException($ex, $this->GetMimeTypeForFormat($format));
        }
    }

    static function ApplyCommonProperties(/*MgLayerBase|MgLayerGroup*/ $obj, /*php_object*/ $op, MgLayerGroupCollection $groups) {
        $bChanged = false;
        if (isset($op->SetDisplayInLegend)) {
            $ov = $obj->GetDisplayInLegend();
            $nv = MgUtils::StringToBool($op->SetDisplayInLegend);
            if ($ov != $nv) {
                $obj->SetDisplayInLegend($nv);
                $bChanged = true;
            }
        }
        if (isset($op->SetGroup)) {
            $oGroup = $obj->GetGroup();
            if ($oGroup == null) {
                $parent = $groups->GetItem($op->SetGroup);
                $obj->SetGroup($parent);
                $bChanged = true;
            } else {
                $parent = $groups->GetItem($op->SetGroup);
                if ($parent->GetObjectId() != $oGroup->GetObjectId()) {
                    $obj->SetGroup($parent);
                    $bChanged = true;
                }
            }
        }
        if (isset($op->SetLegendLabel)) {
            $ov = $obj->GetLegendLabel();
            if ($ov != $op->SetLegendLabel) {
                $obj->SetLegendLabel($op->SetLegendLabel);
                $bChanged = true;
            }
        }
        if (isset($op->SetVisible)) {
            $ov = $obj->GetVisible();
            $nv = MgUtils::StringToBool($op->SetVisible);
            if ($ov != $nv) {
                $obj->SetVisible($nv);
                $bChanged = true;
            }
        }
        return $bChanged;
    }

    static function ApplyCommonGroupProperties(MgLayerGroup $group, /*php_object*/ $op, MgLayerGroupCollection $groups) {
        $bChanged = self::ApplyCommonProperties($group, $op, $groups);
        if (isset($op->SetExpandInLegend)) {
            $ov = $group->GetExpandInLegend();
            $nv = MgUtils::StringToBool($op->SetExpandInLegend);
            if ($ov != $nv) {
                $group->SetExpandInLegend($nv);
                $bChanged = true;
            }
        }
        return $bChanged;
    }

    static function ApplyCommonLayerProperties(MgLayerBase $layer, /*php_object*/ $op, MgLayerGroupCollection $groups) {
        $bChanged = self::ApplyCommonProperties($layer, $op, $groups);
        if (isset($op->SetSelectable)) {
            $ov = $layer->GetSelectable();
            $nv = MgUtils::StringToBool($op->SetSelectable);
            if ($ov != $nv) {
                $layer->SetSelectable($nv);
                $bChanged = true;
            }
        }
        return $bChanged;
    }
}