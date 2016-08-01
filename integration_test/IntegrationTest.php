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
        if ($type == "POST" && is_array($data)) {
            $request = new Buzz\Message\Form\FormRequest($type);
            $fields = array();
            foreach ($data as $key => $value) {
                $fields[$key] = $value;
            }
            $request->setFields($fields);
        } else {
            $request = new Buzz\Message\Request($type);
        }
        $request->fromUrl($absUrl);

        if ($origType == "PUT")
            $request->addHeader("X-HTTP-Method-Override: PUT");
        else if ($origType == "DELETE")
            $request->addHeader("X-HTTP-Method-Override: DELETE");
        $request->addHeader("x-mapguide-test-harness: true");
        $auth = "Basic ";
        if ($username != null) {
            $auth .= base64_encode($username.":".$password);
        }
        
        $request->addHeader("Authorization: $auth");
        $response = new Buzz\Message\Response();
        //echo "*** $auth (".strlen($auth).")\n";
        $client = new Buzz\Client\Curl();
        /*
        echo "\n===================== BEGIN REQUEST =========================\n";
        echo $request;
        echo "\n====================== END REQUEST ==========================\n";
        */
        $client->send($request, $response);
        /*
        echo "\n===================== BEGIN RESPONSE =========================\n";
        echo $response;
        echo "\n====================== END RESPONSE ==========================\n";
        */
        return $response;
    }
}

?>