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

require_once dirname(__FILE__)."/Config.php";
require_once dirname(__FILE__)."/ApiResponse.php";

abstract class IntegrationTest extends PHPUnit_Framework_TestCase
{
    protected function apiTestAnon($url, $type, $data) {
        return $this->apiTestWithCredentials($url, $type, $data, "Anonymous", "");
    }

    protected function apiTestAdmin($url, $type, $data) {
        $admin = Configuration::getAdminLogin();
        return $this->apiTestWithCredentials($url, $type, $data, $admin->user, $admin->pass);
    }

    protected function apiTest($url, $type, $data) {
        return $this->apiTestWithCredentials($url, $type, $data, null, null);
    }

    protected function apiTestWithCredentials($url, $type, $data, $username, $password) {
        $origType = $type;
        if ($type == "PUT")
            $type = "POST";
        else if ($type == "DELETE")
            $type = "POST";
        
        $absUrl = Configuration::getRestUrl($url);

        if ($type == "GET") {
            //form the http request for GET requests
            //encode all non-alphanumeric characters except -_
            if (is_array($data))
            {
                $absUrl .= "?";
                foreach ($data as $param => $value)
                {
                    $absUrl .= $param."=".urlencode($value)."&";
                }
            }
        }

        $curl = curl_init($absUrl);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        $headers = array();
        if ($origType == "PUT")
            $headers[] = "X-HTTP-Method-Override: PUT";
        else if ($origType == "DELETE")
            $headers[] = "X-HTTP-Method-Override: DELETE";
        $headers[] = "x-mapguide-test-harness: true";
        if ($username != null) {
            curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if ($type == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        //echo "**** $type ($origType) $absUrl\n";
        //echo "======================= BEGIN RESPONSE =========================\n$response\n";
        //echo "======================== END RESPONSE ==========================\n";

        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return new ApiResponse($status, $contentType, $response, $absUrl, $headers, $origType, $data);
    }

    protected function assertXmlContent($response) {
        $this->assertTrue(strpos($response->getContent(), "<?xml") !== FALSE);
    }

    protected function assertMimeType($expectedMime, $response) {
        $this->assertContains($expectedMime, $response->getContentType());
    }

    protected function assertStatusCodeIs($code, $resp) {
        $this->assertEquals($code, $resp->getStatusCode(), $resp->dump());
    }

    protected function assertStatusCodeIsNot($code, $resp) {
        $this->assertNotEquals($code, $resp->getStatusCode(), $resp->dump());
    }
}

?>