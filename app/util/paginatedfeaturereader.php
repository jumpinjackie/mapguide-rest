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

// MgPaginatedFeatureReader provides a paginated view of a MgFeatureReader, this is
// used to restrict the range of the MgFeatureReader to the specified page size and
// page number
class MgPaginatedFeatureReader
{
    private $innerReader;
    private $pageSize;
    private $pageNo;
    private $read;

    private $lowerBound;
    private $upperBound;

    private $total;
    private $hasMorePages;

    private $actuallyEndOfReader;

    public function __construct($reader, $pageSize, $pageNo, $total = -1) {
        $this->innerReader = $reader;
        $this->pageSize = $pageSize;
        $this->pageNo = $pageNo;
        $this->read = 0;
        $this->total = $total;
        $this->actuallyEndOfReader = false;

        $this->lowerBound = $this->pageSize * ($this->pageNo - 1);
        $this->upperBound = $this->pageSize * $this->pageNo;

        $this->hasMorePages = true;
        //Then total is -1, the reader is unbounded. However, if the total is known, we embed
        //this reader with extra intelligence about whether there is another "page" worth of
        //features after this one
        if ($this->total >= 0) {
            $this->hasMorePages = ($this->total > $this->upperBound);
        }
    }

    public function GetPageSize() {
        return $this->pageSize;
    }

    public function GetPageNo() {
        return $this->pageNo;
    }

    public function HasMorePages() {
        return $this->hasMorePages;
    }

    public function GetTotal() {
        return $this->total;
    }

    public function GetMaxPages() {
        if ($this->total >= 0) {
            return ceil($this->total / $this->pageSize);
        } else {
            return -1;
        }
    }

    public function EndOfReader() {
        return $this->actuallyEndOfReader;
    }

    public function ReadNext() {
        $bResult = $this->innerReader->ReadNext();
        //End of reader
        if ($bResult === FALSE) {
            $this->actuallyEndOfReader = true;
            return FALSE;
        } else {
            $this->read++;
            //Advance the reader to the specified range first.
            while ($this->read <= $this->lowerBound && $bResult === TRUE) {
                $bResult = $this->innerReader->ReadNext();
                $this->read++;
                if ($bResult === FALSE)
                    return FALSE;
            }
            //Then return true if the reader is in the range
            return ($this->read > $this->lowerBound) &&
                   ($this->read <= $this->upperBound);
        }
    }

    public function GetClassDefinition() {
        return $this->innerReader->GetClassDefinition();
    }

    public function GetFeatureObject($propNameOrIndex) {
        return $this->innerReader->GetFeatureObject($propNameOrIndex);
    }

    public function Close() {
        $this->innerReader->Close();
    }

    public function GetBLOB($propNameOrIndex) {
        return $this->innerReader->GetBLOB($propNameOrIndex);
    }

    public function GetBoolean($propNameOrIndex) {
        return $this->innerReader->GetBoolean($propNameOrIndex);
    }

    public function GetByte($propNameOrIndex) {
        return $this->innerReader->GetByte($propNameOrIndex);
    }

    public function GetCLOB($propNameOrIndex) {
        return $this->innerReader->GetCLOB($propNameOrIndex);
    }

    public function GetDateTime($propNameOrIndex) {
        return $this->innerReader->GetDateTime($propNameOrIndex);
    }

    public function GetDouble($propNameOrIndex) {
        return $this->innerReader->GetDouble($propNameOrIndex);
    }

    public function GetGeometry($propNameOrIndex) {
        return $this->innerReader->GetGeometry($propNameOrIndex);
    }

    public function GetInt16($propNameOrIndex) {
        return $this->innerReader->GetInt16($propNameOrIndex);
    }

    public function GetInt32($propNameOrIndex) {
        return $this->innerReader->GetInt32($propNameOrIndex);
    }

    public function GetInt64($propNameOrIndex) {
        return $this->innerReader->GetInt64($propNameOrIndex);
    }

    public function GetPropertyCount() {
        return $this->innerReader->GetPropertyCount();
    }

    public function GetPropertyIndex($propName) {
        return $this->innerReader->GetPropertyIndex($propName);
    }

    public function GetPropertyName($propIndex) {
        return $this->innerReader->GetPropertyName($propIndex);
    }

    public function GetPropertyType($propNameOrIndex) {
        return $this->innerReader->GetPropertyType($propNameOrIndex);
    }

    public function GetRaster($propNameOrIndex) {
        return $this->innerReader->GetRaster($propNameOrIndex);
    }

    public function GetReaderType() {
        return $this->innerReader->GetReaderType();
    }

    public function GetSingle($propNameOrIndex) {
        return $this->innerReader->GetSingle($propNameOrIndex);
    }

    public function GetString($propNameOrIndex) {
        return $this->innerReader->GetString($propNameOrIndex);
    }

    public function IsNull($propNameOrIndex) {
        return $this->innerReader->IsNull($propNameOrIndex);
    }
}