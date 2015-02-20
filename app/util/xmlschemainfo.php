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

class MgXmlSchemaInfo
{
    const NS_XSD = "http://www.w3.org/2001/XMLSchema";
    const NS_XSI = "http://www.w3.org/2001/XMLSchema-instance";

    const XML_DATA_TYPE_NUMBER = 1;
    const XML_DATA_TYPE_BOOLEAN = 2;
    const XML_DATA_TYPE_STRING = 3;

    //FIXME: Some schemas technically allow for nested groups of the same element many levels deep, meaning groups beyond the first level
    //of nesting will probably have all string values, in the future we should fix this is that an XML path can partially match instead
    //of requiring a full match

    static $XML_ELEMENT_TYPES = array(
        //ApplicationDefinition-1.0.0.xsd
        "/ApplicationDefinition/MapSet/MapGroup/InitialView/CenterX" => self::XML_DATA_TYPE_NUMBER,
        "/ApplicationDefinition/MapSet/MapGroup/InitialView/CenterY" => self::XML_DATA_TYPE_NUMBER,
        "/ApplicationDefinition/MapSet/MapGroup/InitialView/Scale" => self::XML_DATA_TYPE_NUMBER,
        "/ApplicationDefinition/WidgetSet/Widget/Disabled" => self::XML_DATA_TYPE_BOOLEAN,
        //ApplicationDefinitionInfo-1.0.0.xsd
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo/StandardUi" => self::XML_DATA_TYPE_BOOLEAN,
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo/Parameter/IsMandatory" => self::XML_DATA_TYPE_BOOLEAN,
        //DataStoreList-1.0.0.xsd
        "/DataStoreList/DataStore/FdoEnabled" => self::XML_DATA_TYPE_BOOLEAN,
        //DrawingSource-1.0.0.xsd
        "/DrawingSource/Sheet/Extent/MinX" => self::XML_DATA_TYPE_NUMBER,
        "/DrawingSource/Sheet/Extent/MinY" => self::XML_DATA_TYPE_NUMBER,
        "/DrawingSource/Sheet/Extent/MaxX" => self::XML_DATA_TYPE_NUMBER,
        "/DrawingSource/Sheet/Extent/MaxY" => self::XML_DATA_TYPE_NUMBER,
        //FdoLongTransactionList-1.0.0.xsd
        "/FdoLongTransactionList/LongTransaction/@IsActive" => self::XML_DATA_TYPE_BOOLEAN,
        "/FdoLongTransactionList/LongTransaction/@IsFrozen" => self::XML_DATA_TYPE_BOOLEAN,
        //FdoSpatialContextList-1.0.0.xsd
        "/FdoSpatialContextList/SpatialContext/@IsActive" => self::XML_DATA_TYPE_BOOLEAN,
        "/FdoSpatialContextList/SpatialContext/XYTolerance" => self::XML_DATA_TYPE_NUMBER,
        "/FdoSpatialContextList/SpatialContext/ZTolerance" => self::XML_DATA_TYPE_NUMBER,
        "/FdoSpatialContextList/SpatialContext/Extent/LowerLeftCoordinate/X" => self::XML_DATA_TYPE_NUMBER,
        "/FdoSpatialContextList/SpatialContext/Extent/LowerLeftCoordinate/Y" => self::XML_DATA_TYPE_NUMBER,
        "/FdoSpatialContextList/SpatialContext/Extent/UpperRightCoordinate/X" => self::XML_DATA_TYPE_NUMBER,
        "/FdoSpatialContextList/SpatialContext/Extent/UpperRightCoordinate/Y" => self::XML_DATA_TYPE_NUMBER,
        //FdoProviderCapabilities-1.1.0.xsd
        "/FeatureProviderCapabilities/Geometry/Dimensionality" => self::XML_DATA_TYPE_NUMBER,
        "/FeatureProviderCapabilities/Connection/SupportsLocking" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsTimeout" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsTransactions" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsLongTransactions" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsSQL" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsConfiguration" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Connection/SupportsSavePoint" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsInheritance" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsMultipleSchemas" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsObjectProperties" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsAssociationProperties" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsSchemaOverrides" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsNetworkModel" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsAutoIdGeneration" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsDataStoreScopeUniqueIdGeneration" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Schema/SupportsSchemaModification" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsParameters" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsTimeout" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsSelectExpressions" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsSelectFunctions" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsSelectDistinct" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsSelectOrdering" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Command/SupportsSelectGrouping" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Filter/SupportsGeodesicDistance" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Filter/SupportsNonLiteralGeometricOperations" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Raster/SupportsRaster" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Raster/SupportsStitching" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Raster/SupportsSubsampling" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Topology/SupportsTopology" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Topology/SupportsTopologicalHierarchy" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Topology/BreaksCurveCrossingsAutomatically" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Topology/ActivatesTopologyByArea" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderCapabilities/Topology/ConstrainsFeatureMovements" => self::XML_DATA_TYPE_BOOLEAN,
        //FeatureProviderRegistry-1.0.0.xsd
        "/FeatureProviderRegistry/FeatureProvider/ConnectionProperties/ConnectionProperty/@Required" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderRegistry/FeatureProvider/ConnectionProperties/ConnectionProperty/@Protected" => self::XML_DATA_TYPE_BOOLEAN,
        "/FeatureProviderRegistry/FeatureProvider/ConnectionProperties/ConnectionProperty/@Enumerable" => self::XML_DATA_TYPE_BOOLEAN,
        //FeatureSource-1.0.0.xsd
        "/FeatureSource/Extension/AttributeRelate/ForceOneToOne" => self::XML_DATA_TYPE_BOOLEAN,
        //LayerDefinition-2.4.0.xsd
        "/LayerDefinition/DrawingLayerDefinition/Opacity" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/DrawingLayerDefinition/MinScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/DrawingLayerDefinition/MaxScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/Opacity" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/MinScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/MaxScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/AreaTypeStyle/AreaRule/Label/AdvancedPlacement/ScaleLimit" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/AreaTypeStyle/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/LineTypeStyle/LineRule/Label/AdvancedPlacement/ScaleLimit" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/LineTypeStyle/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/Label/AdvancedPlacement/ScaleLimit" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/DisplayAsText" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/AllowOverpost" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/Label/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Mark/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Image/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Font/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Font/Bold" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Font/Italic" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Font/Underlined" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/W2D/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule/PointSymbolization2D/Block/MaintainAspect" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/CompositeTypeStyle/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerDefinition/GridLayerDefinition/Opacity" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Label/AdvancedPlacement/ScaleLimit" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/RedBand/LowBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/RedBand/HighBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/RedBand/LowChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/RedBand/HighChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/GreenBand/LowBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/GreenBand/HighBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/GreenBand/LowChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/GreenBand/HighChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/BlueBand/LowBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/BlueBand/HighBand" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/BlueBand/LowChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/Color/Bands/BlueBand/HighChannel" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/HillShade/Azimuth" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/HillShade/Altitude" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/HillShade/ScaleFactor" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/BrightnessFactor" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule/ContrastFactor" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/SurfaceStyle/ZeroValue" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/SurfaceStyle/ScaleFactor" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/MinScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/MaxScale" => self::XML_DATA_TYPE_NUMBER,
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/RebuildFactor" => self::XML_DATA_TYPE_NUMBER,
        //LoadProcedure-2.2.0.xsd
        "/LoadProcedure/RasterLoadProcedure/GeoReferenceOverride/LocationX" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/RasterLoadProcedure/GeoReferenceOverride/LocationY" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/RasterLoadProcedure/GeoReferenceOverride/ScaleX" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/RasterLoadProcedure/GeoReferenceOverride/ScaleY" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/RasterLoadProcedure/SubsampleFactor" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/RasterLoadProcedure/GenerateSpatialDataSources" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/RasterLoadProcedure/GenerateLayers" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/RasterLoadProcedure/GenerateMaps" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/RasterLoadProcedure/GenerateSymbolLibraries" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/Generalization" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/DwgLoadProcedure/ClosedPolylinesToPolygons" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/GenerateSpatialDataSources" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/GenerateLayers" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/GenerateMaps" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/GenerateSymbolLibraries" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/DwgLoadProcedure/LayerComponents/LayerComponent/Selected" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SdfLoadProcedure/Generalization" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/SdfLoadProcedure/GenerateSpatialDataSources" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SdfLoadProcedure/GenerateLayers" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SdfLoadProcedure/GenerateMaps" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SdfLoadProcedure/GenerateSymbolLibraries" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SQLiteLoadProcedure/Generalization" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/SQLiteLoadProcedure/GenerateSpatialDataSources" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SQLiteLoadProcedure/GenerateLayers" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SQLiteLoadProcedure/GenerateMaps" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/SQLiteLoadProcedure/GenerateSymbolLibraries" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/ShpLoadProcedure/Generalization" => self::XML_DATA_TYPE_NUMBER,
        "/LoadProcedure/ShpLoadProcedure/GenerateSpatialDataSources" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/ShpLoadProcedure/GenerateLayers" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/ShpLoadProcedure/GenerateMaps" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/ShpLoadProcedure/GenerateSymbolLibraries" => self::XML_DATA_TYPE_BOOLEAN,
        "/LoadProcedure/ShpLoadProcedure/ConvertToSdf" => self::XML_DATA_TYPE_BOOLEAN,
        //MapDefinition-2.4.0.xsd
        "/MapDefinition/Extents/MinX" => self::XML_DATA_TYPE_NUMBER,
        "/MapDefinition/Extents/MinY" => self::XML_DATA_TYPE_NUMBER,
        "/MapDefinition/Extents/MaxX" => self::XML_DATA_TYPE_NUMBER,
        "/MapDefinition/Extents/MaxY" => self::XML_DATA_TYPE_NUMBER,
        "/MapDefinition/BaseMapDefinition/FiniteDisplayScale" => self::XML_DATA_TYPE_NUMBER,
        "/MapDefinition/MapLayer/Selectable" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayer/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayer/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayer/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayerGroup/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayerGroup/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/MapLayerGroup/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/BaseMapLayer/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/BaseMapLayer/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/MapDefinition/BaseMapDefinition/BaseMapLayerGroup/BaseMapLayer/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        //PrintLayout-1.0.0.xsd
        "/PrintLayout/PageProperties/BackgroundColor/Red" => self::XML_DATA_TYPE_NUMBER,
        "/PrintLayout/PageProperties/BackgroundColor/Blue" => self::XML_DATA_TYPE_NUMBER,
        "/PrintLayout/PageProperties/BackgroundColor/Green" => self::XML_DATA_TYPE_NUMBER,
        "/PrintLayout/LayoutProperties/ShowTitle" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowScaleBar" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowNorthArrow" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowURL" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowDateTime" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowCustomLogos" => self::XML_DATA_TYPE_BOOLEAN,
        "/PrintLayout/LayoutProperties/ShowCustomText" => self::XML_DATA_TYPE_BOOLEAN,
        //ProfileResult-2.4.0.xsd
        "/ProfileResult/ProfileRenderMap/LayerCount" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/Scale" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/CreateImageTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/Extents/MinX" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/Extents/MinY" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/Extents/MaxX" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderMap/Extents/MaxY" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderDynamicOverlay/LayerCount" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderDynamicOverlay/Scale" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderDynamicOverlay/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderDynamicOverlay/CreateImageTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderLayer/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderLayer/ScaleRange/MinScale" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderLayer/ScaleRange/MaxScale" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderLayers/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderSelection/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderWatermark/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderWatermarks/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        "/ProfileResult/ProfileRenderLabels/RenderTime" => self::XML_DATA_TYPE_NUMBER,
        //ResourceList-1.0.0.xsd
        "/ResourceList/ResourceFolder/Depth" => self::XML_DATA_TYPE_NUMBER,
        "/ResourceList/ResourceFolder/NumberOfFolders" => self::XML_DATA_TYPE_NUMBER,
        "/ResourceList/ResourceFolder/NumberOfDocuments" => self::XML_DATA_TYPE_NUMBER,
        "/ResourceList/ResourceDocument/Depth" => self::XML_DATA_TYPE_NUMBER,
        "/ResourceList/ResourceFolder/ResourceFolderHeader/Security/Inherited" => self::XML_DATA_TYPE_BOOLEAN,
        "/ResourceList/ResourceDocument/ResourceDocumentHeader/Security/Inherited" => self::XML_DATA_TYPE_BOOLEAN,
        //RuntimeMap-2.6.0.xsd
        "/RuntimeMap/DisplayDpi" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Group/Type" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Group/DisplayInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Group/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Group/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Group/ActuallyVisible" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Layer/Type" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Layer/ScaleRange/MinScale" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Layer/ScaleRange/MaxScale" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Layer/ScaleRange/FeatureStyle/Type" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Layer/Selectable" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Layer/DisplayInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Layer/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Layer/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/Layer/ActuallyVisible" => self::XML_DATA_TYPE_BOOLEAN,
        "/RuntimeMap/FiniteDisplayScale" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/CoordinateSystem/MetersPerUnit" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Extents/LowerLeftCoordinate/X" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Extents/LowerLeftCoordinate/Y" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Extents/UpperRightCoordinate/X" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/Extents/UpperRightCoordinate/Y" => self::XML_DATA_TYPE_NUMBER,
        //RuntimeMap-3.0.0.xsd
        "/RuntimeMap/TileWidth" => self::XML_DATA_TYPE_NUMBER,
        "/RuntimeMap/TileHeight" => self::XML_DATA_TYPE_NUMBER,
        //SiteInformation-1.0.0.xsd
        "/SiteInformation/SiteServer/OperatingSystem/AvailablePhysicalMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/SiteServer/OperatingSystem/TotalPhysicalMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/SiteServer/OperatingSystem/AvailableVirtualMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/SiteServer/OperatingSystem/TotalVirtualMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/AdminOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/ClientOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/SiteOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/AverageOperationTime" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/CpuUtilization" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/TotalOperationTime" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/ActiveConnections" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/TotalConnections" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/TotalOperationsProcessed" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/TotalOperationsReceived" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Statistics/Uptime" => self::XML_DATA_TYPE_NUMBER,
        //SiteInformation-2.2.0.xsd
        "/SiteInformation/Server/OperatingSystem/AvailablePhysicalMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/OperatingSystem/TotalPhysicalMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/OperatingSystem/AvailableVirtualMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/OperatingSystem/TotalVirtualMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/AdminOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/ClientOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/SiteOperationsQueueCount" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/AverageOperationTime" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/CpuUtilization" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/WorkingSet" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/VirtualMemory" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/TotalOperationTime" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/ActiveConnections" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/TotalConnections" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/TotalOperationsProcessed" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/TotalOperationsReceived" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/Uptime" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/CacheSize" => self::XML_DATA_TYPE_NUMBER,
        "/SiteInformation/Server/Statistics/CacheDroppedEntries" => self::XML_DATA_TYPE_NUMBER,
        //UnmanagedDataList-1.0.0.xsd
        "/UnmanagedDataList/UnmanagedDataFolder/NumberOfFolders" => self::XML_DATA_TYPE_NUMBER,
        "/UnmanagedDataList/UnmanagedDataFolder/NumberOfFiles" => self::XML_DATA_TYPE_NUMBER,
        "/UnmanagedDataList/UnmanagedDataFolder/NumberOfFolders" => self::XML_DATA_TYPE_NUMBER,
        "/UnmanagedDataList/UnmanagedDataFile/Size" => self::XML_DATA_TYPE_NUMBER,
        //WebLayout-2.6.0.xsd
        "/WebLayout/PointSelectionBuffer" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/InformationPane/Width" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/InformationPane/LegendVisible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/InformationPane/PropertiesVisible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/TaskPane/Width" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/CommandSet/Command/MatchLimit" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/CommandSet/Command/DisableIfSelectionEmpty" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/Map/InitialView/CenterX" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/Map/InitialView/CenterY" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/Map/InitialView/Scale" => self::XML_DATA_TYPE_NUMBER,
        "/WebLayout/EnablePingServer" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/ToolBar/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/InformationPane/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/ContextMenu/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/TaskPane/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/TaskPane/TaskBar/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/StatusBar/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/WebLayout/ZoomControl/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        //WatermarkDefinition-2.4.0.xsd
        "/WatermarkDefinition/Position/XYPosition/XPosition/Offset" => self::XML_DATA_TYPE_NUMBER,
        "/WatermarkDefinition/Position/XYPosition/YPosition/Offset" => self::XML_DATA_TYPE_NUMBER,
        "/WatermarkDefinition/Position/TilePosition/TileWidth" => self::XML_DATA_TYPE_NUMBER,
        "/WatermarkDefinition/Position/TilePosition/TileHeight" => self::XML_DATA_TYPE_NUMBER,
        "/WatermarkDefinition/Appearance/Transparency" => self::XML_DATA_TYPE_NUMBER,
        "/WatermarkDefinition/Appearance/Rotation" => self::XML_DATA_TYPE_NUMBER,
        //TileSetDefinition-3.0.0.xsd
        "/TileSetDefinition/Extents/MinX" => self::XML_DATA_TYPE_NUMBER,
        "/TileSetDefinition/Extents/MinY" => self::XML_DATA_TYPE_NUMBER,
        "/TileSetDefinition/Extents/MaxX" => self::XML_DATA_TYPE_NUMBER,
        "/TileSetDefinition/Extents/MaxY" => self::XML_DATA_TYPE_NUMBER,
        "/TileSetDefinition/BaseMapLayerGroup/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/TileSetDefinition/BaseMapLayerGroup/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/TileSetDefinition/BaseMapLayerGroup/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/TileSetDefinition/BaseMapLayerGroup/BaseMapLayer/Selectable" => self::XML_DATA_TYPE_BOOLEAN,
        "/TileSetDefinition/BaseMapLayerGroup/BaseMapLayer/ShowInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/TileSetDefinition/BaseMapLayerGroup/BaseMapLayer/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        //Miscellaneous MapGuide response types that don't have a formal schema
        "/SessionTimeout/Value" => self::XML_DATA_TYPE_NUMBER,
        "/FeatureInformation/SelectedFeatures/SelectedLayer/LayerMetadata/Property/Type" => self::XML_DATA_TYPE_NUMBER,
        //Response types unique to mapguide-rest
        "/AggregateResult/Total" => self::XML_DATA_TYPE_NUMBER,
        "/AggregateResult/BoundingBox/LowerLeft/X" => self::XML_DATA_TYPE_NUMBER,
        "/AggregateResult/BoundingBox/LowerLeft/Y" => self::XML_DATA_TYPE_NUMBER,
        "/AggregateResult/BoundingBox/UpperRight/X" => self::XML_DATA_TYPE_NUMBER,
        "/AggregateResult/BoundingBox/UpperRight/Y" => self::XML_DATA_TYPE_NUMBER,
        "/SelectedLayerCollection/SelectedLayer/Count" => self::XML_DATA_TYPE_NUMBER,
        "/LayerCollection/Layer/Type" => self::XML_DATA_TYPE_NUMBER,
        "/LayerCollection/Layer/Selectable" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerCollection/Layer/DisplayInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerCollection/Layer/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerCollection/Layer/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/LayerCollection/Layer/ActuallyVisible" => self::XML_DATA_TYPE_BOOLEAN,
        "/GroupCollection/Group/Type" => self::XML_DATA_TYPE_NUMBER,
        "/GroupCollection/Group/DisplayInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/GroupCollection/Group/ExpandInLegend" => self::XML_DATA_TYPE_BOOLEAN,
        "/GroupCollection/Group/Visible" => self::XML_DATA_TYPE_BOOLEAN,
        "/GroupCollection/Group/ActuallyVisible" => self::XML_DATA_TYPE_BOOLEAN
    );

    //This is the definitive list of XML element paths where the leaf element can exist in multiples (according to its respective)
    //XML schema. In new JSON output mode, we check if the current DOMNode path is in this list. If so, we array-ify the resulting
    //JSON property. Otherwise we output that property as-is (whether it be a JSON primitive or JSON object value)
    //
    //Note that the value here is insignificant. We want want to key on the path so this array can function
    //like a set
    static $MULTI_ELEMENT_PATHS = array(
        //FeatureSource-1.0.0.xsd
        "/FeatureSource/Parameter" => "abcd1234",
        "/FeatureSource/SupplementalSpatialContextInfo" => "abcd1234",
        "/FeatureSource/Extension" => "abcd1234",
        "/FeatureSource/Extension/CalculatedProperty" => "abcd1234",
        "/FeatureSource/Extension/AttributeRelate" => "abcd1234",
        "/FeatureSource/Extension/AttributeRelate/RelateProperty" => "abcd1234",
        //DrawingSource-1.0.0.xsd
        "/DrawingSource/Sheet" => "abcd1234",
        //LayerDefinition-2.4.0.xsd (schema has been additive, so this includes older versions as well)
        "/LayerDefinition/VectorLayerDefinition/PropertyMapping" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/AreaTypeStyle/AreaRule" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/LineTypeStyle/LineRule" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/LineTypeStyle/LineRule/LineSymbolization2D" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/PointTypeStyle/PointRule" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/CompositeTypeStyle/CompositeRule" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/CompositeTypeStyle/CompositeRule/CompositeSymbolization/SymbolInstance" => "abcd1234",
        "/LayerDefinition/VectorLayerDefinition/VectorScaleRange/CompositeTypeStyle/CompositeRule/CompositeSymbolization/SymbolInstance/ParameterOverrides/Override" => "abcd1234",
        "/LayerDefinition/GridLayerDefinition/GridScaleRange" => "abcd1234",
        "/LayerDefinition/GridLayerDefinition/GridScaleRange/ColorStyle/ColorRule" => "abcd1234",
        //SymbolDefinition-2.4.0.xsd (schema has been additive, so this includes older versions as well)
        "/SimpleSymbolDefinition/ParameterDefinition/Parameter" => "abcd1234",
        "/CompoundSymbolDefinition/SimpleSymbol" => "abcd1234",
        "/SimpleSymbolDefinition/Graphics/Path" => "abcd1234",
        "/SimpleSymbolDefinition/Graphics/Image" => "abcd1234",
        "/SimpleSymbolDefinition/Graphics/Text" => "abcd1234",
        //MapDefinition-2.4.0.xsd (schema has been additive, so this includes older versions as well)
        "/MapDefinition/MapLayer" => "abcd1234",
        "/MapDefinition/MapLayerGroup" => "abcd1234",
        "/MapDefinition/BaseMapDefinition/BaseLayerGroup" => "abcd1234",
        "/MapDefinition/BaseMapDefinition/BaseLayerGroup/BaseMapLayer" => "abcd1234",
        "/MapDefinition/Watermarks/Watermark" => "abcd1234",
        //WebLayout-2.6.0.xsd (schema has been additive, so this includes older versions as well)
        "/WebLayout/ToolBar/Button" => "abcd1234",
        "/WebLayout/ToolBar/Button/SubItem" => "abcd1234",
        "/WebLayout/ContextMenu/MenuItem" => "abcd1234",
        "/WebLayout/ContextMenu/MenuItem/SubItem" => "abcd1234",
        "/WebLayout/TaskPane/TaskBar/MenuButton" => "abcd1234",
        "/WebLayout/CommandSet/Command" => "abcd1234",
        //LoadProcedure-2.2.0.xsd (schema has been additive, so this includes older versions as well)
        "/LoadProcedure/SdfLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/SdfLoadProcedure/ResourceId" => "abcd1234",
        "/LoadProcedure/DwfLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/DwfLoadProcedure/ResourceId" => "abcd1234",
        "/LoadProcedure/ShpLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/ShpLoadProcedure/ResourceId" => "abcd1234",
        "/LoadProcedure/DwgLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/DwgLoadProcedure/ResourceId" => "abcd1234",
        "/LoadProcedure/DwgLoadProcedure/FileComponents/FileComponent" => "abcd1234",
        "/LoadProcedure/DwgLoadProcedure/LayerComponents/LayerComponent" => "abcd1234",
        "/LoadProcedure/RasterLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/RasterLoadProcedure/ResourceId" => "abcd1234",
        "/LoadProcedure/RasterLoadProcedure/GeoReferenceOverride" => "abcd1234",
        "/LoadProcedure/SQLiteLoadProcedure/SourceFile" => "abcd1234",
        "/LoadProcedure/SQLiteLoadProcedure/ResourceId" => "abcd1234",
        //PrintLayout-1.0.0.xsd
        "/PrintLayout/CustomLogos/Logo" => "abcd1234",
        "/PrintLayout/CustomText/Text" => "abcd1234",
        //ApplicationDefinition-1.0.0.xsd
        "/ApplicationDefinition/WidgetSet" => "abcd1234",
        "/ApplicationDefinition/WidgetSet/Container" => "abcd1234",
        "/ApplicationDefinition/WidgetSet/Container/Item" => "abcd1234",
        "/ApplicationDefinition/WidgetSet/Container/Item/Item" => "abcd1234",
        //"/ApplicationDefinition/WidgetSet/Widget" => "abcd1234",
        "/ApplicationDefinition/MapSet/MapGroup" => "abcd1234",
        "/ApplicationDefinition/MapSet/MapGroup/Map" => "abcd1234",
        //ApplicationDefinitionInfo-1.0.0.xsd
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo" => "abcd1234",
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo/ContainableBy" => "abcd1234",
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo/Parameter" => "abcd1234",
        "/ApplicationDefinitionWidgetInfoSet/WidgetInfo/Parameter/AllowedValue" => "abcd1234",
        "/ApplicationDefinitionContainerInfoSet/ContainerInfo" => "abcd1234",
        "/ApplicationDefinitionTemplateInfoSet/TemplateInfo" => "abcd1234",
        "/ApplicationDefinitionTemplateInfoSet/TemplateInfo/Panel" => "abcd1234",
        //BatchPropertyCollection-1.0.0.xsd
        "/BatchPropertyCollection/PropertyCollection" => "abcd1234",
        "/BatchPropertyCollection/PropertyCollection/Property" => "abcd1234",
        //DataStoreList-1.0.0.xsd
        "/DataStoreList/DataStore" => "abcd1234",
        //DrawingSectionList-1.0.0.xsd
        "/DrawingSectionList/Section" => "abcd1234",
        //DrawingSectionResourceList-1.0.0.xsd
        "/DrawingSectionResourceList/SectionResource" => "abcd1234",
        //FdoLongTransactionList-1.0.0.xsd
        "/FdoLongTransactionList/LongTransaction" => "abcd1234",
        //FdoProviderCapabilities-1.0.0.xsd
        "/FeatureProviderCapabilities/Connection/SpatialContextExtent/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Schema/Class/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Schema/Data/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Command/SupportedCommands/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Filter/Condition/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Filter/Spatial/Operation" => "abcd1234",
        "/FeatureProviderCapabilities/Filter/Distance/Operation" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/Type/Name" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/FunctionDefinitionList/FunctionDefinition" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/FunctionDefinitionList/FunctionDefinition/ArgumentDefinitionList/ArgumentDefinition" => "abcd1234",
        "/FeatureProviderCapabilities/Geometry/Types/Type" => "abcd1234",
        "/FeatureProviderCapabilities/Geometry/Components/Type" => "abcd1234",
        //FdoProviderCapabilities-1.1.0.xsd
        "/FeatureProviderCapabilities/Schema/SupportedAutoGeneratedTypes" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/FunctionDefinitionList/FunctionDefinition/SignatureDefinitionCollection/SignatureDefinition" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/FunctionDefinitionList/FunctionDefinition/SignatureDefinitionCollection/SignatureDefinition/ArgumentDefinitionList/ArgumentDefinition" => "abcd1234",
        "/FeatureProviderCapabilities/Expression/FunctionDefinitionList/FunctionDefinition/SignatureDefinitionCollection/SignatureDefinition/ArgumentDefinitionList/ArgumentDefinition/PropertyValueConstraintList/Value" => "abcd1234",
        //FdoSpatialContextList-1.0.0.xsd
        "/FdoSpatialContextList/SpatialContext" => "abcd1234",
        //FeatureProviderRegistry-1.0.0.xsd
        "/FeatureProviderRegistry/FeatureProvider" => "abcd1234",
        "/FeatureProviderRegistry/FeatureProvider/ConnectionProperties/ConnectionProperty" => "abcd1234",
        "/FeatureProviderRegistry/FeatureProvider/ConnectionProperties/ConnectionProperty/Value" => "abcd1234",
        //Group-1.0.0.xsd
        "/Group/Users/User" => "abcd1234",
        //GroupList-1.0.0.xsd
        "/GroupList/Group" => "abcd1234",
        //ProfileResult-2.4.0.xsd
        "/ProfileResult/ProfileRenderMap/ProfileRenderLayers/ProfileRenderLayer" => "abcd1234",
        "/ProfileResult/ProfileRenderMap/ProfileRenderSelection/ProfileSelectedRenderLayer" => "abcd1234",
        "/ProfileResult/ProfileRenderMap/ProfileRenderWatermarks/ProfileRenderWatermark" => "abcd1234",
        //RepositoryList-1.0.0.xsd
        "/RepositoryList/Repository" => "abcd1234",
        //ResourceDataList-1.0.0.xsd
        "/ResourceDataList/ResourceData" => "abcd1234",
        //ResourceDocumentHeader-1.0.0.xsd
        "/ResourceDocumentHeader/Metadata/Simple/Property" => "abcd1234",
        //ResourceList-1.0.0.xsd
        "/ResourceList/ResourceFolder" => "abcd1234",
        "/ResourceList/ResourceFolder/ResourceFolderHeader/Security/Users/User" => "abcd1234", //ResourceSecurity-1.0.0.xsd
        "/ResourceList/ResourceFolder/ResourceFolderHeader/Security/Groups/Group" => "abcd1234", //ResourceSecurity-1.0.0.xsd
        "/ResourceList/ResourceDocument" => "abcd1234",
        "/ResourceList/ResourceDocument/ResourceDocumentHeader/Security/Users/User" => "abcd1234", //ResourceSecurity-1.0.0.xsd
        "/ResourceList/ResourceDocument/ResourceDocumentHeader/Security/Groups/Group" => "abcd1234", //ResourceSecurity-1.0.0.xsd
        "/ResourceList/ResourceDocument/ResourceDocumentHeader/Metadata/Simple/Property" => "abcd1234",
        //ResourcePackageManifest-1.0.0.xsd
        "/ResourcePackageManifest/Operations/Operation" => "abcd1234",
        "/ResourcePackageManifest/Operations/Operation/Parameters/Parameter" => "abcd1234",
        //ResourceReferenceList-1.0.0.xsd
        "/ResourceReferenceList/ResourceId" => "abcd1234",
        //RuntimeMap-2.6.0.xsd
        "/RuntimeMap/Group" => "abcd1234",
        "/RuntimeMap/Layer" => "abcd1234",
        "/RuntimeMap/Layer/ScaleRange" => "abcd1234",
        "/RuntimeMap/Layer/ScaleRange/FeatureStyle" => "abcd1234",
        "/RuntimeMap/Layer/ScaleRange/FeatureStyle/Rule" => "abcd1234",
        "/RuntimeMap/FiniteDisplayScale" => "abcd1234",
        //SelectAggregate-1.0.0.xsd
        "/PropertySet/PropertyDefinitions/PropertyDefinition" => "abcd1234",
        "/PropertySet/Properties/PropertyCollection" => "abcd1234",
        "/PropertySet/Properties/PropertyCollection/Property" => "abcd1234",
        //ServerList-1.0.0.xsd
        "/ServerList/Server" => "abcd1234",
        //SqlSelect-1.0.0.xsd
        "/RowSet/ColumnDefinitions" => "abcd1234",
        "/RowSet/ColumnDefinitions/Column" => "abcd1234",
        "/RowSet/Rows/Row" => "abcd1234",
        "/RowSet/Rows/Row/Column" => "abcd1234",
        //StringCollection-1.0.0.xsd
        "/StringCollection/Item" => "abcd1234",
        //TileSetDefinition-3.0.0.xsd
        "/TileSetDefinition/TileStoreParameters/Parameter" => "abcd1234",
        "/TileSetDefinition/BaseMapLayerGroup" => "abcd1234",
        "/TileSetDefinition/BaseMapLayerGroup/BaseMapLayer" => "abcd1234",
        //UnmanagedDataList-1.0.0.xsd
        "/UnmanagedDataList/UnmanagedDataFolder" => "abcd1234",
        "/UnmanagedDataList/UnmanagedDataFile" => "abcd1234",
        //UserList-1.0.0.xsd
        "/UserList/User" => "abcd1234",
        "/UserList/Group" => "abcd1234",
        //Miscellaneous MapGuide response types that don't have a formal schema
        "/FeatureInformation/FeatureSet/Layer" => "abcd1234",
        "/FeatureInformation/FeatureSet/Layer/Class/ID" => "abcd1234",
        "/FeatureInformation/SelectedFeatures/SelectedLayer" => "abcd1234",
        "/FeatureInformation/SelectedFeatures/SelectedLayer/LayerMetadata/Property" => "abcd1234",
        "/FeatureInformation/SelectedFeatures/SelectedLayer/Feature" => "abcd1234",
        "/FeatureInformation/SelectedFeatures/SelectedLayer/Feature/Property" => "abcd1234",
        "/SelectedLayerCollection/SelectedLayer" => "abcd1234",
        "/LayerCollection/Layer" => "abcd1234",
        "/FeatureSourceParams/FeatureSchema/ClassDefinition" => "abcd1234",
        "/DataConfigurationList/Configuration" => "abcd1234"
    );

    private static function GetXmlPath($domElement, $suffix = "") {
        $path = "/" . $domElement->nodeName . $suffix;
        $currNode = $domElement->parentNode;
        while ($currNode != null) {
            if ($currNode->nodeType != XML_DOCUMENT_NODE) {
                $path = "/" . $currNode->nodeName . $path;
                $currNode = $currNode->parentNode;
                if ($currNode == null)
                    break;
            } else {
                break;
            }
        }
        return $path;
    }

    public static function DeEscape($str) {
        //Need to de-escape any escaped ' characters because an escaped ' is an illegal character under a double-quoted string in JSON
        return str_replace("\\'", "'", $str);
    }

    private static function GetXmlType($domElement, $suffix = "") {
        $path = self::GetXmlPath($domElement, $suffix);
        if (array_key_exists($path, self::$XML_ELEMENT_TYPES)) {
            return self::$XML_ELEMENT_TYPES[$path];
        }
        return self::XML_DATA_TYPE_STRING;
    }

    public static function GetAttributeValue($domAttr) {
        $parent = $domAttr->ownerElement;
        $type = self::GetXmlType($parent, "/@" . $domAttr->name);
        $result = null;
        $text = addslashes($domAttr->value);
        switch($type)
        {
            case self::XML_DATA_TYPE_BOOLEAN:
                {
                    $bv = strtolower($text);
                    if ($bv == "1")
                        $bv = "true";
                    if ($bv == "0")
                        $bv = "false";

                    $result = $bv;
                }
                break;
            case self::XML_DATA_TYPE_NUMBER:
                {
                    $result = $text;
                }
                break;
            default:
                {
                    if ($text != '') {
                        $result = '"'.self::DeEscape($text).'"';
                    } else {
                        $text = '""';
                    }
                }
                break;
        }
        return $result;
    }

    public static function GetValue($domElement) {
        $result = null;
        $type = null;
        //If text node, we must walk up the parent to get the actual node path
        if ($domElement->nodeType == XML_TEXT_NODE) {
            $type = self::GetXmlType($domElement->parentNode);
        } else {
            $type = self::GetXmlType($domElement);
        }
        /* text node, just return content */
        $text = trim($domElement->textContent);
        $text = addslashes($text);
        switch($type)
        {
            case self::XML_DATA_TYPE_BOOLEAN:
                {
                    $bv = strtolower($text);
                    if ($bv == "1")
                        $bv = "true";
                    if ($bv == "0")
                        $bv = "false";

                    $result = $bv;
                }
                break;
            case self::XML_DATA_TYPE_NUMBER:
                {
                    $result = $text;
                }
                break;
            default:
                {
                    if ($text != '') {
                        $result = '"'.self::DeEscape($text).'"';
                    } else {
                        $text = '""';
                    }
                }
                break;
        }
        return $result;
    }

    public static function IsMultiple($domElement, $suffix = "") {
        $path = self::GetXmlPath($domElement, $suffix);
        $result = array_key_exists($path, self::$MULTI_ELEMENT_PATHS);

        return $result;
    }
}

?>