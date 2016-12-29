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

//
// This is the english text bundle for mapguide-rest
// 
// Use this as the reference bundle when doing your own translations. Due to the use of sprintf
// placeholder substitution, make sure your translated text can still use placeholders in the
// specified order here

return array(
    "L_RETURNS_DATA_AS_TYPE" => "Returns data as %s",
    "L_PRODUCT_API_REFERENCE" => "%s API Reference",
    "L_ABOUT_MAPGUIDE_REST" => "mapguide-rest is a RESTful web extension for MapGuide Open Source and Autodesk Infrastructure Map Server.<br/><br/><strong>NOTE:</strong> Basic HTTP authentication credentials will generally be cached by the web browser for a short period should you choose to use this method instead of passing in session ids",
    "L_REST_GET_ID_DESC" => "The ID of the feature to return",
    "L_REST_PAGE_NO_DESC" => "The page number to switch to. Only applies if pagination is configured for the data source",
    "L_REST_GET_BBOX_DESC" => "A quartet of x1,y1,x2,y2",
    "L_REST_GET_FILTER_DESC" => "The url-encoded FDO filter string",
    "L_REST_POST_BODY_DESC" => "The XML envelope describing the features to be inserted",
    "L_REST_PUT_BODY_DESC" => "The XML envelope describing the features to be updated",
    "L_REST_DELETE_FILTER_DESC" => "The FDO Filter string that will determine what features are deleted",
    "E_DATA_CONFIGURATION_NOT_JSON" => "The uploaded data configuration is not valid JSON",
    "E_DATA_FILE_NOT_FOUND" => "File not found: %s",
    "E_DATA_FILE_NAME_NOT_ALLOWED" => "Invalid file name: %s",
    "E_UNSUPPORTED_PROPERTY_TYPE" => "Unsupported property type",
    "E_NOT_SUPPORTED_FOR_SESSION_RESOURCES" => "This operation is not supported for session resources",
    "E_MALFORMED_JSON_BODY" => "Malformed JSON body",
    "E_DONT_KNOW_HOW_TO_OUTPUT" => "Could not determine how to output: %s",
    "E_FAILED_TO_GENERATE_TILE_AFTER_N_ATTEMPTS" => "Failed to generate tile after %d attempts",
    "E_FAILED_TO_CREATE_DIR_AFTER_N_ATTEMPTS" => "Failed to create directory after %d attempts",
    "E_OPERATION_NOT_ALLOWED" => "Operation Not Allowed",
    "E_FEATURE_SOURCE_NOT_CONFIGURED_TO_ALLOW_UPDATES" => "The resource '%s' is not configured to allow feature updates",
    "E_PHP_FILE_UPLOAD_ERROR" => "File upload error (code: %s). See <a href='http://www.php.net/manual/en/features.file-upload.errors.php'>http://www.php.net/manual/en/features.file-upload.errors.php</a> for more information",
    "L_STACK_TRACE" => "Stack Trace",
    "E_UNHANDLED_EXCEPTION" => "Unhandled Exception",
    "E_PHP_EXCEPTION_DETAILS" => "%s at %s:%s",
    "E_SERVICE_UNAVAILABLE" => "Service Unavailable",
    "E_SERVER_ERROR" => "Server Error",
    "E_FORBIDDEN" => "Forbidden",
    "E_NOT_FOUND" => "Not Found",
    "E_RESOURCE_NOT_FOUND" => "Resource not found: %s",
    "E_UNPREVIEWABLE_RESOURCE_TYPE" => "Resource type is not previewable",
    "E_BAD_REQUEST" => "Bad Request",
    "E_CZML_PAGINATION_NOT_SUPPORTED" => "Pagination of czml output not supported",
    "E_FORBIDDEN_ACCESS" => "You are not authorized to access this resource",
    "E_UNAUTHORIZED" => "Unauthorized",
    "E_UNAUTHORIZED_DESC" => "You must enter a valid login ID and password to access this site",
    "E_MISSING_REQUIRED_PARAMETER" => "Missing required parameter: %s",
    "E_UNRECOGNIZED_VALUE_IN_DOMAIN" => "Unrecognized value: %s. Allowed values are (%s)",
    "E_METHOD_NOT_SUPPORTED" => "Method not supported",
    "E_METHOD_NOT_SUPPORTED_DESC" => "Method not supported: %s",
    "E_UNSUPPORTED_SOURCE_TYPE" => "Unsupported source type: %s",
    "E_UNSUPPORTED_REPRESENTATION" => "Unsupported representation: %s",
    "E_ADAPTER_NOT_REGISTERED" => "Adapter (%s) is not defined or registered",
    "E_NO_DATA_CONFIGURATION_FOR_URI" => "No data configuration found for URI part: %s",
    "E_NOT_GEOMETRY_PROPERTY" => "Not a geometry property: %s",
    "E_LAYER_HAS_INVALID_FEATURE_CLASS" => "Layer '%s' has an invalid feature class",
    "E_LAYER_HAS_INVALID_FEATURE_SOURCE" => "Layer '%s' has an invalid feature source",
    "E_LAYER_NOT_IN_SELECTION" => "Layer (%s) not found in selection",
    "E_GROUP_NOT_FOUND" => "Group not found: %s",
    "E_INVALID_DATE_STRING" => "Invalid date string: %s",
    "E_CANNOT_PARSE_DATE_STRING_INVALID_COMPONENT" => "Invalid date string. '%s' has invalid date component '%s'",
    "E_UNKNOWN_FORMATTER" => "No formatter named '%s' registered",
    "E_UNKNOWN_DATETIME_FORMATTER" => "No DateTime formatter named '%s' registered",
    "E_UNKNOWN_GEOMETRY_FORMATTER" => "No Geometry formatter named '%s' registered",
    "E_TEMPLATE_MISSING_DEFINITION" => "Missing definition for '%s' template",
    "E_INVALID_MAP_DEFINITION_PARAMETER" => "Parameter '%s' is not a valid Map Definition resource id",
    "E_RELATION_CANNOT_HAVE_SPACES" => "Relation '%s' cannot have spaces",
    "E_RELATION_MISSING_PROPERTY" => "Configuration for relation '%s' is missing '%s' property",
    "E_RELATION_MISSING_SOURCE_PROPERTY" => "Source configuration for relation '%s' is missing '%s' property",
    "E_CANNOT_APPLY_UPDATE_CANNOT_UNIQUELY_IDENTIFY" => "Cannot apply update. The value (%s) is not enough to uniquely identify the feature to be updated. Class definition has %d identity properties",
    "E_QUERY_SETUP" => "Error setting up related query. Filter was: %s, Details: %s",
    "E_INVALID_COORDINATE_PAIR" => "Encountered invalid coordinate pair: '%s'. Found %d tokens. Expected 2",
    "E_NO_FEATURE_ID_SET" => "No feature ID set",
    "E_NO_REPRESENTATIONS_DEFINED_IN_CONFIGURATION" => "No representations defined in configuration document",
    "E_REPRESENTATION_NOT_HANDLED_OR_SUPPORTED" => "This configuration does not support or handle the given representation: %s",
    "E_REPRESENTATION_CONFIGURATION_MISSING_PROPERTY" => "Missing '%s' property in configuration of representation: %s",
    "E_METHOD_NOT_SUPPORTED_ON_REPRESENTATION" => "The configured representation '%s' is not configured to handle '%s' requests",
    "E_CANNOT_DELETE_NO_ID_PROPS" => "Cannot delete (%s) in class '%s' by ID. Class has no identity properties",
    "E_CANNOT_DELETE_MULTIPLE_ID_PROPS" => "Cannot delete (%s) in class '%s' by ID. Class has more than one identity property",
    "E_CANNOT_QUERY_NO_ID_PROPS" => "Cannot query (%s) in class '%s' by ID. Class has no identity properties",
    "E_CANNOT_QUERY_MULTIPLE_ID_PROPS" => "Cannot query (%s) in class '%s' by ID. Class has more than one identity property",
    "E_ID_PROP_NOT_DATA" => "Specified identity property (%s) is not a data property",
    "E_ID_PROP_NOT_FOUND" => "Specified identity property (%s) not found in class definition",
    "E_MISSING_ROOT_PROPERTY" => "Missing root property '%s' in configuration",
    "E_MISSING_PROPERTY_IN_SECTION" => "Missing property '%s' in configuration section '%s'",
    "E_MISSING_REQUIRED_ADAPTER_PROPERTY" => "Missing required adapter property '%s'",
    "E_LAYER_NOT_FOUND_IN_MAP" => "No layer named (%s) found in map",
    "E_LAYER_NOT_POINTING_TO_EXPECTED_FEATURE_SOURCE" => "Layer (%s) does not point to the expected feature source of (%s). It instead points to: %s",
    "E_UNKNOWN_PAPER_SIZE" => "Unknown paper size: %s",
    "E_INVALID_DOCUMENT" => "Invalid document",
    "E_PROPERTY_NOT_FOUND_IN_CLASS" => "Property (%s) not found in class: %s",
    "E_MULTIPLE_IDENTITY_PROPS_NOT_SUPPORTED" => "Multiple identity properties not supported"
);