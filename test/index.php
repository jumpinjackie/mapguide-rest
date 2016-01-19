<?php

include dirname(__FILE__)."/../../mapadmin/constants.php";
include(dirname(__FILE__)."/bootstrap.php");

$prolog = "<?xml";

$selfUrl = "/mapguide/rest";
$dump = false;
if (array_key_exists("dump", $_GET) && $_GET["dump"] == "1") {
    $dump = true;
}

$loadOnly = false;
if (array_key_exists("loadonly", $_GET) && $_GET["loadonly"] == "1") {
    $loadOnly = true;
}

$namespace = null;
if (array_key_exists("namespace", $_GET)) {
    $namespace = $_GET["namespace"];
}
if (array_key_exists("no_url_rewrite", $_GET) && $_GET["no_url_rewrite"] == "1") {
    $selfUrl = "/mapguide/rest/index.php";
}

if (!$dump) {
    try {    
        SetupTestData();
        if ($loadOnly) {
            echo "Test data loaded";
            die;
        }
    } catch (MgException $ex) {
        echo "Failed to bootstrap the test suite. Exception was: ".$ex->GetDetails();
        die;
    }
}

$emptyFeatureSourceXml = '<?xml version="1.0" encoding="UTF-8"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%Empty.sdf</Value></Parameter></FeatureSource>';

if ($dump) {
    ob_start();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>MapGuide REST API Test Runner</title>
        <link rel="stylesheet" media="screen" href="qunit-1.20.0.css">
        <script src="../assets/common/js/jquery-1.10.2.min.js" type="text/javascript"></script>
    </head>
    <body>
        <div id="qunit"></div>
        <script src="qunit-1.20.0.js" type="text/javascript"></script>
        <script type="text/javascript">

            var MgMimeType = {
                Html: '<?= MgMimeType::Html ?>',
                Xml: '<?= MgMimeType::Xml ?>',
                Json: '<?= MgMimeType::Json ?>',
                Png: '<?= MgMimeType::Png ?>',
                Jpeg: '<?= MgMimeType::Jpeg ?>',
                Gif: '<?= MgMimeType::Gif ?>',
                Kml: '<?= MgMimeType::Kml ?>',
                Kmz: '<?= MgMimeType::Kmz ?>',
                Dwf: '<?= MgMimeType::Dwf ?>',
                Binary: '<?= MgMimeType::Binary ?>',
                Pdf: 'application/pdf'
            };
            
            function makeXmlBlob(xml) {
                return new Blob([xml], { type: "text/xml" });
            }
            
            /* Blob.js
             * A Blob implementation.
             * 2013-06-20
             * 
             * By Eli Grey, http://eligrey.com
             * By Devin Samarin, https://github.com/eboyjr
             * License: X11/MIT
             *   See LICENSE.md
             */
            
            /*global unescape */
            /*jslint bitwise: true, regexp: true, confusion: true, es5: true, vars: true, white: true,
              plusplus: true */
            
            /*! @source http://purl.eligrey.com/github/Blob.js/blob/master/Blob.js */
            
            if ((typeof Blob !== "function" && typeof Blob !== "object") || (Blob && Blob.toString() === '[object BlobConstructor]'))
            this.Blob = (function(view) {
                "use strict";
            
                var BlobBuilder = view.BlobBuilder || view.WebKitBlobBuilder || view.MozBlobBuilder || view.MSBlobBuilder || (function(view) {
                    var
                          get_class = function(object) {
                            return Object.prototype.toString.call(object).match(/^\[object\s(.*)\]$/)[1];
                        }
                        , FakeBlobBuilder = function BlobBuilder() {
                            this.data = [];
                        }
                        , FakeBlob = function Blob(data, type, encoding) {
                            this.data = data;
                            this.size = data.length;
                            this.type = type;
                            this.encoding = encoding;
                        }
                        , FBB_proto = FakeBlobBuilder.prototype
                        , FB_proto = FakeBlob.prototype
                        , FileReaderSync = view.FileReaderSync
                        , FileException = function(type) {
                            this.code = this[this.name = type];
                        }
                        , file_ex_codes = (
                              "NOT_FOUND_ERR SECURITY_ERR ABORT_ERR NOT_READABLE_ERR ENCODING_ERR "
                            + "NO_MODIFICATION_ALLOWED_ERR INVALID_STATE_ERR SYNTAX_ERR"
                        ).split(" ")
                        , file_ex_code = file_ex_codes.length
                        , real_URL = view.URL || view.webkitURL || view
                        , real_create_object_URL = real_URL.createObjectURL
                        , real_revoke_object_URL = real_URL.revokeObjectURL
                        , URL = real_URL
                        , btoa = view.btoa
                        , atob = view.atob
                        
                        , ArrayBuffer = view.ArrayBuffer
                        , Uint8Array = view.Uint8Array
                    ;
                    FakeBlob.fake = FB_proto.fake = true;
                    while (file_ex_code--) {
                        FileException.prototype[file_ex_codes[file_ex_code]] = file_ex_code + 1;
                    }
                    if (!real_URL.createObjectURL) {
                        URL = view.URL = {};
                    }
                    URL.createObjectURL = function(blob) {
                        var
                              type = blob.type
                            , data_URI_header
                        ;
                        if (type === null) {
                            type = "application/octet-stream";
                        }
                        if (blob instanceof FakeBlob) {
                            data_URI_header = "data:" + type;
                            if (blob.encoding === "base64") {
                                return data_URI_header + ";base64," + blob.data;
                            } else if (blob.encoding === "URI") {
                                return data_URI_header + "," + decodeURIComponent(blob.data);
                            } if (btoa) {
                                return data_URI_header + ";base64," + btoa(blob.data);
                            } else {
                                return data_URI_header + "," + encodeURIComponent(blob.data);
                            }
                        } else if (real_create_object_URL) {
                            return real_create_object_URL.call(real_URL, blob);
                        }
                    };
                    URL.revokeObjectURL = function(object_URL) {
                        if (object_URL.substring(0, 5) !== "data:" && real_revoke_object_URL) {
                            real_revoke_object_URL.call(real_URL, object_URL);
                        }
                    };
                    FBB_proto.append = function(data/*, endings*/) {
                        var bb = this.data;
                        // decode data to a binary string
                        if (Uint8Array && (data instanceof ArrayBuffer || data instanceof Uint8Array)) {
                            var
                                  str = ""
                                , buf = new Uint8Array(data)
                                , i = 0
                                , buf_len = buf.length
                            ;
                            for (; i < buf_len; i++) {
                                str += String.fromCharCode(buf[i]);
                            }
                            bb.push(str);
                        } else if (get_class(data) === "Blob" || get_class(data) === "File") {
                            if (FileReaderSync) {
                                var fr = new FileReaderSync;
                                bb.push(fr.readAsBinaryString(data));
                            } else {
                                // async FileReader won't work as BlobBuilder is sync
                                throw new FileException("NOT_READABLE_ERR");
                            }
                        } else if (data instanceof FakeBlob) {
                            if (data.encoding === "base64" && atob) {
                                bb.push(atob(data.data));
                            } else if (data.encoding === "URI") {
                                bb.push(decodeURIComponent(data.data));
                            } else if (data.encoding === "raw") {
                                bb.push(data.data);
                            }
                        } else {
                            if (typeof data !== "string") {
                                data += ""; // convert unsupported types to strings
                            }
                            // decode UTF-16 to binary string
                            bb.push(unescape(encodeURIComponent(data)));
                        }
                    };
                    FBB_proto.getBlob = function(type) {
                        if (!arguments.length) {
                            type = null;
                        }
                        return new FakeBlob(this.data.join(""), type, "raw");
                    };
                    FBB_proto.toString = function() {
                        return "[object BlobBuilder]";
                    };
                    FB_proto.slice = function(start, end, type) {
                        var args = arguments.length;
                        if (args < 3) {
                            type = null;
                        }
                        return new FakeBlob(
                              this.data.slice(start, args > 1 ? end : this.data.length)
                            , type
                            , this.encoding
                        );
                    };
                    FB_proto.toString = function() {
                        return "[object Blob]";
                    };
                    return FakeBlobBuilder;
                }(view));
            
                var Blob = function(blobParts, options) {
                    var type = options ? (options.type || "") : "";
                    var builder = new BlobBuilder();
                    if (blobParts) {
                        for (var i = 0, len = blobParts.length; i < len; i++) {
                            if (Uint8Array && blobParts[i] instanceof Uint8Array) {
                                builder.append(blobParts[i].buffer);
                            }
                            else {
                                builder.append(blobParts[i]);
                            }
                        }
                    }
                    var blob = builder.getBlob(type);
                    if (!blob.slice && blob.webkitSlice) {
                        blob.slice = blob.webkitSlice;
                    }
                    return blob;
                };
                var getPrototypeOf = Object.getPrototypeOf || function(object) {
                    return object.__proto__;
                };
                Blob.prototype = getPrototypeOf(new Blob());
                return Blob;
            }(this));

            /**
             * jQuery plugin to convert a given $.ajax response xml object to json.
             *
             * @example var json = $.xml2json(response);
             */
            (function() {

                // default options based on https://github.com/Leonidas-from-XIV/node-xml2js
                var defaultOptions = {
                    attrkey: '$',
                    charkey: '_',
                    normalize: false
                };

                // extracted from jquery
                function parseXML(data) {
                    var xml, tmp;
                    if (!data || typeof data !== "string") {
                        return null;
                    }
                    try {
                        if (window.DOMParser) { // Standard
                            tmp = new DOMParser();
                            xml = tmp.parseFromString(data, "text/xml");
                        } else { // IE
                            xml = new ActiveXObject("Microsoft.XMLDOM");
                            xml.async = "false";
                            xml.loadXML(data);
                        }
                    } catch (e) {
                        xml = undefined;
                    }
                    if (!xml || !xml.documentElement || xml.getElementsByTagName("parsererror").length) {
                        throw new Error("Invalid XML: " + data);
                    }
                    return xml;
                }
                
                function normalize(value, options){
                    if (!!options.normalize){
                        return (value || '').trim();
                    }
                    return value;
                }

                function xml2jsonImpl(xml, options) {

                    var i, result = {}, attrs = {}, node, child, name;
                    result[options.attrkey] = attrs;

                    if (xml.attributes && xml.attributes.length > 0) {
                        for (i = 0; i < xml.attributes.length; i++){
                            var item = xml.attributes.item(i);
                            attrs[item.nodeName] = item.value;
                        }
                    }

                    // element content
                    if (xml.childElementCount === 0) {
                        result[options.charkey] = normalize(xml.textContent, options);
                    }

                    for (i = 0; i < xml.childNodes.length; i++) {
                        node = xml.childNodes[i];
                        if (node.nodeType === 1) {

                            if (node.attributes.length === 0 && node.childElementCount === 0){
                                child = normalize(node.textContent, options);
                            } else {
                                child = xml2jsonImpl(node, options);
                            }

                            name = node.nodeName;
                            if (result.hasOwnProperty(name)) {
                                // For repeating elements, cast/promote the node to array
                                var val = result[name];
                                if (!Array.isArray(val)) {
                                    val = [val];
                                    result[name] = val;
                                }
                                val.push(child);
                            } else {
                                result[name] = child;
                            }
                        }
                    }

                    return result;
                }

                /**
                 * Converts an xml document or string to a JSON object.
                 *
                 * @param xml
                 */
                function xml2json(xml, options) {
                    if (!xml) {
                        return xml;
                    }

                    options = options || defaultOptions;

                    if (typeof xml === 'string') {
                        xml = parseXML(xml).documentElement;
                    }

                    var root = {};

                    if (xml.attributes.length === 0 && xml.childElementCount === 0){
                        root[xml.nodeName] = normalize(xml.textContent, options);
                    } else {
                        root[xml.nodeName] = xml2jsonImpl(xml, options);
                    }

                    return root;
                }

                if (typeof jQuery !== 'undefined') {
                    jQuery.extend({xml2json: xml2json});
                } else if (typeof module !== 'undefined') {
                    module.exports = xml2json;
                } else if (typeof window !== 'undefined') {
                    window.xml2json = xml2json;
                }
            })();

            var XML_PROLOG = "<?= $prolog ?>";

            //NOTES:
            //
            //This test suite requires the Sheboygan dataset to be loaded first (yeah yeah ... let's not wax lyrical about semantics of unit testing)

            //TODO:
            //
            // - Add tests for JSON and other representations once implemented
            // - Do actual content verification in addition to response status verification

            //If running this test runner from a different domain:port, set this to true and adjust the rest_root_url to match
            var CORS_TESTING = false;
            <? if ($namespace != null) { ?>
            var rest_root_url = "<?= $selfUrl ?>/<?= $namespace ?>";
            <? } else { ?>
            var rest_root_url = "<?= $selfUrl ?>";
            <? } ?>
            var badSessionId = "12345678abcdefgh";

            if (typeof(FormData) == 'undefined') {
                alert("Please use a browser that supports the FormData API");
                throw "Please use a browser that supports the FormData API";
            }
            if (typeof(Blob) == 'undefined') {
                alert("Please use a browser that supports the Blob API");
                throw "Please use a browser that supports the Blob API";
            }

            // Sue me!
            QUnit.config.reorder = false;

            // based heavily off:
            // https://sites.google.com/a/van-steenbeek.net/archive/explorer_domparser_parsefromstring
            if( typeof window.DOMParser === "undefined" ){
                window.DOMParser = function(){};

                window.DOMParser.prototype.parseFromString = function(str, contentType){
                    if(typeof ActiveXObject !== 'undefined'){
                        var xmldata = new ActiveXObject('MSXML.DomDocument');
                        xmldata.async = false;
                        xmldata.loadXML(str);
                        return xmldata;
                    } else if(typeof XMLHttpRequest !== 'undefined'){
                        var xmldata = new XMLHttpRequest;

                        if(!contentType){
                            contentType = 'application/xml';
                        }

                        xmldata.open('GET', 'data:' + contentType + ';charset=utf-8,' + encodeURIComponent(str), false);

                        if(xmldata.overrideMimeType) {
                            xmldata.overrideMimeType(contentType);
                        }

                        xmldata.send(null);
                        return xmldata.responseXML;
                    }
                };
            }

            function stringifyGet(data) {
                if (typeof(data) === 'string')
                    return data;

                var values = [];
                for (var key in data) {
                    values.push(key + "=" + data[key]);
                }
                return values.join("&");
            }
            
            function prepareEnvironment(context) {
                context.ok = function(assertion, message) {
                    ok(assertion, message);
                };
                context.assertMimeType = function(expected, actual) {
                    context.ok(actual == expected, "(" + actual + ") Expected mime type of: " + expected);
                };
            }

            function api_test(url, type, data, callback) {
                var origType = type;
                if (type == "PUT")
                    type = "POST";
                else if (type == "DELETE")
                    type = "POST";

                var dataType = "xml";
                var processData = true;
                if (type == "POST" && typeof(data) == "object") {
                    var fd = new FormData();
                    for (var key in data) {
                        fd.append(key, data[key]);
                    }
                    data = fd;
                }
                $.ajax({
                    url: url,
                    type: type,
                    processData: false,
                    data: (type === "GET") ? stringifyGet(data) : data,
                    //dataType: 'xml',
                    contentType: false,
                    async: false,
                    beforeSend: function(xhr) {
                        if (origType == "PUT")
                            xhr.setRequestHeader("X-HTTP-Method-Override", "PUT");
                        else if (origType == "DELETE")
                            xhr.setRequestHeader("X-HTTP-Method-Override", "DELETE");
                        xhr.setRequestHeader("x-mapguide-test-harness", true);
                        xhr.setRequestHeader("Authorization", "Basic "); //Hmm, this is being set if we don't set it ourselves for some reason. So might as well plug and invalid one in to trigger 401
                    },
                    complete: function(result) {
                        //console.log(type + " - " + result.status + " - " + url);
                        if(result.status == 0) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else if(result.status == 404) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else if(result.status == 401) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else {
                            callback(result.status, result.responseText, result.getResponseHeader("Content-Type"));
                        }
                    }
                });
            }

            function api_test_with_credentials(url, type, data, username, password, callback) {
                var origType = type;
                if (type == "PUT")
                    type = "POST";
                else if (type == "DELETE")
                    type = "POST";

                if (type == "POST" && typeof(data) == "object") {
                    var fd = new FormData();
                    for (var key in data) {
                        fd.append(key, data[key]);
                    }
                    data = fd;
                }
                $.ajax({
                    url: url,
                    type: type,
                    processData: false,
                    data: (type === "GET") ? stringifyGet(data) : data,
                    //dataType: 'xml',
                    contentType: false,
                    beforeSend: function(xhr) {
                        if (origType == "PUT")
                            xhr.setRequestHeader("X-HTTP-Method-Override", "PUT");
                        else if (origType == "DELETE")
                            xhr.setRequestHeader("X-HTTP-Method-Override", "DELETE");
                        xhr.setRequestHeader("x-mapguide-test-harness", true);
                        xhr.setRequestHeader("Authorization", "Basic " + btoa(username + ":" + password));
                    },
                    async: false,
                    complete: function(result) {
                        //console.log(type + " - " + result.status + " - " + url);
                        if(result.status == 0) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else if(result.status == 404) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else if(result.status == 401) {
                            callback(result.status, null, result.getResponseHeader("Content-Type"));
                        } else {
                            callback(result.status, result.responseText, result.getResponseHeader("Content-Type"));
                        }
                    }
                });
            }

            function api_test_anon(url, type, data, callback) {
                return api_test_with_credentials(url, type, data, "Anonymous", "", callback);
            }

            function api_test_admin(url, type, data, callback) {
                return api_test_with_credentials(url, type, data, "<?= $adminUser ?>", "<?= $adminPass ?>", callback);
            }

            function assertMimeType(expected, actual) {
                ok(actual == expected, "(" + actual + ") Expected mime type of: " + expected);
            }

            function encodeHTML(str) {
                return str.replace(/&/g, '&amp;')
                          .replace(/</g, '&lt;')
                          .replace(/>/g, '&gt;')
                          .replace(/"/g, '&quot;')
                          .replace(/'/g, '&apos;');
            }

            <? if ($namespace != null) {
            include "suite.$namespace.php";
            } else {
            include "suite.v1.php"; //Default test suite
            } ?>

        </script>
    </body>
</html>

<?
if ($dump) {
    $content = ob_get_contents();
    ob_end_clean();
    $path = dirname(__FILE__)."/test_static.html";
    file_put_contents($path, $content);
    echo "Static test suite snapshot saved to: $path";
}
?>