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

require_once "datetimeoutputformatter.php";

class MgDefaultDateTimeOutputFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dateTime) {
        return $dateTime->ToString();
    }
}

class MgDMYDateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%02d/%02d/%d", $dt->GetDay(), $dt->GetMonth(), $dt->GetYear());
    }
}

class MgMDYDateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%02d/%02d/%d", $dt->GetMonth(), $dt->GetDay(), $dt->GetYear());
    }
}

class MgISO9601DateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%d-%02d-%02d", $dt->GetYear(), $dt->GetMonth(), $dt->GetDay());
    }
}

class MgDMYFullDateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%02d/%02d/%d %02d:%02d:%02d", $dt->GetDay(), $dt->GetMonth(), $dt->GetYear(), $dt->GetHour(), $dt->GetMinute(), $dt->GetSecond());
    }
}

class MgMDYFullDateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%02d/%02d/%d %02d:%02d:%02d", $dt->GetMonth(), $dt->GetDay(), $dt->GetYear(), $dt->GetHour(), $dt->GetMinute(), $dt->GetSecond());
    }
}

class MgISO9601FullDateTimeFormatter extends MgDateTimeOutputFormatter {
    public function __construct() {
        parent::__construct();
    }

    protected function OutputDateTime($dt) {
        return sprintf("%d-%02d-%02d %02d:%02d:%02d", $dt->GetYear(), $dt->GetMonth(), $dt->GetDay(), $dt->GetHour(), $dt->GetMinute(), $dt->GetSecond());
    }
}

?>