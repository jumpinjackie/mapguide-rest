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

require_once "utils.php";

class MgBoxedValue
{
    private static function BoxValue($value, $type, $fmt = "xml") {
        $resp = "";
        if ($fmt == "xml") {
            $resp .= '<?xml version="1.0" encoding="utf-8"?>';
            $resp .= "<PrimitiveValue>";
            $resp .= "<Type>$type</Type>";
            if ($type == "String")
                $resp .= "<Value>".MgUtils::EscapeXmlChars($value)."</Value>";
            else
                $resp .= "<Value>$value</Value>";
            $resp .= "</PrimitiveValue>";
        } else { //json
            $val = $value;
            if ($type == "String")
                $val = '"'.MgUtils::EscapeJsonString($val).'"';
            $resp = '{"PrimitiveValue":{"Type":"'.$type.'","Value":'.$val.'}}';
        }
        return $resp;
    }

    public static function Boolean($value, $fmt = "xml") {
        return self::BoxValue($value ? "true" : "false", "Boolean", $fmt);
    }

    public static function Int32($value, $fmt = "xml") {
        return self::BoxValue($value, "Int32", $fmt);
    }

    public static function Int64($value, $fmt = "xml") {
        return self::BoxValue($value, "Int64", $fmt);
    }

    public static function String($value, $fmt = "xml") {
        return self::BoxValue($value, "String", $fmt);
    }
}