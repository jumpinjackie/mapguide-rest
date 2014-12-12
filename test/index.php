<?php

include dirname(__FILE__)."/../../mapadmin/constants.php";

$prolog = "<?xml";

//If you changed this, change it here too
$adminUser = "Administrator";
$adminPass = "admin";
$authorUser = "Author";
$authorPass = "author";
$wfsUser = "WfsUser";
$wfsPass = "wfs";
$wmsUser = "WmsUser";
$wmsPass = "wms";

$user1User = "User1";
$user1Pass = "user1";
$user2User = "User2";
$user2Pass = "user2";
$userGroup = "RestUsers";

$namespace = null;
if (array_key_exists("namespace", $_GET)) {
    $namespace = $_GET["namespace"];
}

try {
    $webConfigPath = dirname(__FILE__)."/../../webconfig.ini";
    MgInitializeWebTier($webConfigPath);

    $mgp = dirname(__FILE__)."/data/Sheboygan.mgp";
    if (!file_exists($mgp)) {
        echo "Please put Sheboygan.mgp into the /data directory before running this test suite";
        die;
    }

    if (!is_dir(dirname(__FILE__)."/../conf/data/test_anonymous/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_anonymous/");
    copy(dirname(__FILE__)."/data/restcfg_anonymous.json", dirname(__FILE__)."/../conf/data/test_anonymous/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_author/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_author/");
    copy(dirname(__FILE__)."/data/restcfg_author.json", dirname(__FILE__)."/../conf/data/test_author/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_administrator/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_administrator/");
    copy(dirname(__FILE__)."/data/restcfg_administrator.json", dirname(__FILE__)."/../conf/data/test_administrator/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_wfsuser/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_wfsuser/");
    copy(dirname(__FILE__)."/data/restcfg_wfsuser.json", dirname(__FILE__)."/../conf/data/test_wfsuser/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_wmsuser/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_wmsuser/");
    copy(dirname(__FILE__)."/data/restcfg_wmsuser.json", dirname(__FILE__)."/../conf/data/test_wmsuser/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_group/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_group/");
    copy(dirname(__FILE__)."/data/restcfg_group.json", dirname(__FILE__)."/../conf/data/test_group/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_mixed/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_mixed/");
    copy(dirname(__FILE__)."/data/restcfg_mixed.json", dirname(__FILE__)."/../conf/data/test_mixed/restcfg.json");

    $source = new MgByteSource($mgp);
    $br = $source->GetReader();

    $siteConn = new MgSiteConnection();
    $userInfo = new MgUserInformation($adminUser, $adminPass);
    $siteConn->Open($userInfo);

    $site = new MgSite();
    $site->Open($userInfo);
    //Set up any required users
    try {
        $site->AddGroup($userGroup, "Group for mapguide-rest test suite users");
    } catch (MgException $ex) { }
    try {
        $site->AddUser($user1User, $user1User, $user1Pass, "Test user for mapguide-rest test suite");
    } catch (MgException $ex) { }
    try {
        $site->AddUser($user2User, $user2User, $user2Pass, "Test user for mapguide-rest test suite");
    } catch (MgException $ex) { }
    try {
        $groups = new MgStringCollection();
        $users = new MgStringCollection();
        $groups->Add($userGroup);
        $users->Add($user1User);
        $users->Add($user2User);
        $site->GrantGroupMembershipsToUsers($groups, $users);
    } catch (MgException $ex) { }

    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
    $resSvc->ApplyResourcePackage($br);

    $srcId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/Parcels.FeatureSource");
    $dstId = new MgResourceIdentifier("Library://RestUnitTests/Parcels.FeatureSource");
    $resSvc->CopyResource($srcId, $dstId, true);

    $bsWriteable = new MgByteSource(dirname(__FILE__)."/data/Parcels_Writeable.FeatureSource.xml");
    $brWriteable = $bsWriteable->GetReader();
    $resSvc->SetResource($dstId, $brWriteable, null);

    $rdsdfsource = new MgByteSource(dirname(__FILE__)."/data/RedlineLayer.sdf");
    $rdsdfrdr = $rdsdfsource->GetReader();
    $resId = new MgResourceIdentifier("Library://RestUnitTests/RedlineLayer.FeatureSource");

    $rdXml = '<?xml version="1.0"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%RedlineLayer.sdf</Value></Parameter></FeatureSource>';
    $rdXmlSource = new MgByteSource($rdXml, strlen($rdXml));
    $rdXmlRdr = $rdXmlSource->GetReader();

    $resSvc->SetResource($resId, $rdXmlRdr, null);
    $resSvc->SetResourceData($resId, "RedlineLayer.sdf", MgResourceDataType::File, $rdsdfrdr);
} catch (MgException $ex) {
    echo "Failed to bootstrap the test suite. Exception was: ".$ex->GetDetails();
    die;
}

$emptyFeatureSourceXml = '<?xml version="1.0" encoding="UTF-8"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%Empty.sdf</Value></Parameter></FeatureSource>';

?>
<!DOCTYPE html>
<html>
    <head>
        <title>MapGuide REST API Test Runner</title>
        <link rel="stylesheet" media="screen" href="qunit-1.10.0.css">
        <script src="../assets/common/js/jquery-1.10.2.min.js" type="text/javascript"></script>
    </head>
    <body>
        <div id="qunit"></div>
        <script src="qunit-1.10.0.js" type="text/javascript"></script>
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

                /**w
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

            <? if ($namespace != null) { ?>
            var rest_root_url = "/mapguide/rest/<?= $namespace ?>";
            <? } else { ?>
            var rest_root_url = "/mapguide/rest";
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
