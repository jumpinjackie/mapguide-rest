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
 * @SWG\Definition(@SWG\Xml(name="RestEditCapabilities"))
 */
class RestEditCapabilities
{
    /**
     * @SWG\Property()
     * @var RestCapabilities
     */
    public $RestCapabilities;
}

/**
 * @SWG\Definition(@SWG\Xml(name="RestCapabilities"))
 */
class RestCapabilities
{
    /**
     * @SWG\Property(description="Allow inserts on this feature source")
     * @var boolean
     */
    public $AllowInsert;
    
    /**
     * @SWG\Property(description="Allow deletes on this feature source")
     * @var boolean
     */
    public $AllowDelete;
    
    /**
     * @SWG\Property(description="Allow updates on this feature source")
     * @var boolean
     */
    public $AllowUpdate;
    
    /**
     * @SWG\Property(description="Use transactions for inserts/updates/deletes. The feature source must support transactions for this to have any effect")
     * @var boolean
     */
    public $UseTransaction;
}

/**
 * @SWG\Definition(@SWG\Xml(name="PrimitiveValueEnvelope"))
 */
class PrimitiveValueEnvelope
{
    /**
     * @SWG\Property()
     * @var PrimitiveValue
     */
    public $PrimitiveValue;
}

/**
 * @SWG\Definition(@SWG\Xml(name="PrimitiveValue"))
 */
class PrimitiveValue
{
    /**
     * @SWG\Property(description="The type of the primitive value", enum={"Boolean", "Int32", "Int64", "String"})
     * @var string
     */
    public $Type;
    
    /**
     * @SWG\Property(description="The underlying value")
     * @var string
     */
    public $Value;
}

/**
 * @SWG\Definition(@SWG\Xml(name="CreateFeatureSourceEnvelope"))
 */
class CreateFeatureSourceEnvelope
{
    /**
     * @SWG\Property()
     * @var FeatureSourceParams
     */
    public $FeatureSourceParams;
}

/**
 * @SWG\Definition(@SWG\Xml(name="FeatureSourceParams"))
 */
class FeatureSourceParams
{
    /**
     * @SWG\Property()
     * @var FeatureSourceFile
     */
    public $File;
    
    /**
     * @SWG\Property()
     * @var FeatureSourceSpatialContext
     */
    public $SpatialContext;
    
    /**
     * @SWG\Property()
     * @var FeatureSourceSchema
     */
    public $FeatureSchema;
}

/**
 * @SWG\Definition(@SWG\Xml(name="FeatureSourceFile"))
 */
class FeatureSourceFile
{
    /**
     * @SWG\Property(description="The FDO provider", enum={"OSGeo.SDF", "OSGeo.SHP", "OSGeo.SQLite"})
     * @var string
     */
    public $Provider;
    
    /**
     * @SWG\Property(description="The file name")
     * @var string
     */
    public $FileName;
}

/**
 * @SWG\Definition(@SWG\Xml(name="SchemaElement"))
 */
class SchemaElement
{
    /**
     * @SWG\Property()
     * @var string
     */
    public $Name;
    
    /**
     * @SWG\Property()
     * @var string
     */
    public $Description;
}

/**
 * @SWG\Definition(@SWG\Xml(name="FeatureSourceSpatialContext"))
 */
class FeatureSourceSpatialContext extends SchemaElement
{  
    /**
     * @SWG\Property()
     * @var string
     */
    public $CoordinateSystem;
    
    /**
     * @SWG\Property()
     * @var double
     */
    public $XYTolerance;
    
    /**
     * @SWG\Property()
     * @var double
     */
    public $ZTolerance;
}

/**
 * @SWG\Definition(@SWG\Xml(name="FeatureSourceSchema"))
 */
class FeatureSourceSchema extends SchemaElement
{
    /**
     * @SWG\Property()
     * @var ClassDefinition[]
     */
    public $ClassDefinition;
}

/**
 * @SWG\Definition(@SWG\Xml(name="ClassDefinition"))
 */
class ClassDefinition extends SchemaElement
{
    /**
     * @SWG\Property()
     * @var PropertyDefinition[]
     */
    public $PropertyDefinition;
    
    /**
     * @SWG\Property(description="The name of the default geometry property. This must exist int the PropertyDefinition collection")
     * @var string
     */
    public $DefaultGeometryPropertyName;
}

/**
 * @SWG\Definition(@SWG\Xml(name="PropertyDefinition"))
 */
class PropertyDefinition extends SchemaElement
{
    /**
     * @SWG\Property()
     * @var boolean
     */
    public $IsIdentity;

    /**
     * @SWG\Property()
     * @var int
     */
    public $PropertyType;

    /**
     * @SWG\Property()
     * @var int
     */
    public $DataType;
    
    /**
     * @SWG\Property()
     * @var boolean
     */
    public $Nullable;
    
    /**
     * @SWG\Property()
     * @var boolean
     */
    public $IsAutoGenerated;
    
    /**
     * @SWG\Property()
     * @var string
     */
    public $DefaultValue;
    
    /**
     * @SWG\Property()
     * @var int
     */
    public $Length;
    
    /**
     * @SWG\Property()
     * @var int
     */
    public $Precision;
    
    /**
     * @SWG\Property()
     * @var int
     */
    public $Scale;
    
    /**
     * @SWG\Property()
     * @var int
     */
    public $GeometryType;
    
    /**
     * @SWG\Property()
     * @var boolean
     */
    public $HasElevation;
    
    /**
     * @SWG\Property()
     * @var string
     */
    public $SpatialContextAssociation;
    
    /**
     * @SWG\Property()
     * @var boolean
     */
    public $ReadOnly;
}

/**
 * @SWG\Definition(@SWG\Xml(name="InsertFeaturesEnvelope"))
 */
class InsertFeaturesEnvelope
{
    /**
     * @SWG\Property()
     * @var InsertFeatureSet
     */
    public $FeatureSet;
}

/**
 * @SWG\Definition(@SWG\Xml(name="InsertFeatureSet"))
 */
class InsertFeatureSet
{
    /**
     * @SWG\Property()
     * @var InsertFeatureCollection
     */
    public $Features;
}

/**
 * @SWG\Definition(@SWG\Xml(name="InsertFeatureCollection"))
 */
class InsertFeatureCollection
{
    /**
     * @SWG\Property()
     * @var FeatureToInsert[]
     */
    public $Feature;
}

/**
 * @SWG\Definition(@SWG\Xml(name="FeatureToInsert"))
 */
class FeatureToInsert
{
    /**
     * @SWG\Property()
     * @var NameValuePair[]
     */
    public $Property;
}

/**
 * @SWG\Definition(@SWG\Xml(name="NameValuePair"))
 */
class NameValuePair
{
    /**
     * @SWG\Property()
     * @var string
     */
    public $Name;
    
    /**
     * @SWG\Property()
     * @var string
     */
    public $Value;
}

/**
 * @SWG\Definition(@SWG\Xml(name="UpdateFeaturesEnvelope"))
 */
class UpdateFeaturesEnvelope
{
    /**
     * @SWG\Property()
     * @var UpdateOperation
     */
    public $UpdateOperation;
}

/**
 * @SWG\Definition(@SWG\Xml(name="UpdateOperations"))
 */
class UpdateOperation
{
    /**
     * @SWG\Property()
     * @var string
     */
    public $Filter;
    
    /**
     * @SWG\Property()
     * @var FeatureToInsert
     */
    public $UpdateProperties;
}