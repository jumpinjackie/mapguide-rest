<?php

//
//  Copyright (C) 2023 by Jackie Ng
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

interface IAppServices {
    public /* internal */ function GetLocalizedText(/*php_string*/ $key);

    public /* internal */ function GetConfig(/*php_string*/ $name);

    public /* internal */ function GetRequestPathInfo();

    public /* internal */ function GetRequestHeader(/*php_string*/ $name);

    public /* internal */ function GetRequestBody();

    public /* internal */ function GetAllRequestParams();

    public /* internal */ function GetRequestParameter(/*php_string*/ $key, /*php_string*/ $defaultValue = "");

    public /* internal */ function SetResponseHeader(/*php_string*/ $name, /*php_string*/ $value);

    public /* internal */ function WriteResponseContent(/*php_string*/ $content);

    public /* internal */ function SetResponseBody(/* string | Psr\Http\Message\StreamInterface */ $content);

    public /* internal */ function SetResponseStatus(/*php_int*/ $statusCode);

    public /* internal */ function LogDebug(/*php_string*/ $message);

    public /* internal */ function SetResponseExpiry(/*php_string*/ $expires);

    public /* internal */ function GetMapGuideVersion();

    public /* internal */ function SetResponseLastModified(/*php_string*/ $mod);

    public /* internal */ function Redirect(/*php_string*/ $url);

    public /* internal */ function Halt(/*php_int*/ $statusCode, /*php_string*/ $body);

    public /* internal */ function HasDependency(/*php_string*/ $name);

    public /* internal */ function GetDependency(/*php_string*/ $name);

    public /* internal */ function RegisterDependency(/*php_string*/ $name, /*php_mixed*/ $value);

    public function Done();
}

interface IFormatterSet {
    public function GetFormatter(/*php_string*/ $formatterName);
}

interface IReader {
    public function ReadNext();
    
    public function GetPropertyType(/*php_int|php_string*/ $indexOrProp);
    
    public function GetPropertyCount();
    
    public function GetPropertyName(/*php_int*/ $index);
    
    public function GetPropertyIndex(/*php_string*/ $name);
    
    //public function GetClassDefinition();
    
    public function GetBoolean(/*php_int|php_string*/ $indexOrProp);

    public function GetByte(/*php_int|php_string*/ $indexOrProp);

    public function GetDateTime(/*php_int|php_string*/ $indexOrProp);

    public function GetDouble(/*php_int|php_string*/ $indexOrProp);

    public function GetGeometry(/*php_int|php_string*/ $indexOrProp);

    public function GetInt16(/*php_int|php_string*/ $indexOrProp);

    public function GetInt32(/*php_int|php_string*/ $indexOrProp);

    public function GetInt64(/*php_int|php_string*/ $indexOrProp);

    public function GetSingle(/*php_int|php_string*/ $indexOrProp);

    public function GetString(/*php_int|php_string*/ $indexOrProp);

    public function IsNull(/*php_int|php_string*/ $indexOrProp);
    
    public function Close();
}