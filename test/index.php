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

            var rest_root_url = "/mapguide/rest";
            var badSessionId = "12345678abcdefgh";

            if (typeof(FormData) == 'undefined') {
                alert("Please use a browser that supports the FormData API");
                throw "Please use a browser that supports the FormData API";
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

            module("REST publishing", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.wfsSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.wmsSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.authorSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.user1SessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.user2SessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.wfsSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected wfs session to be destroyed");
                        delete this.wfsSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.wmsSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected session session to be destroyed");
                        delete this.wmsSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.authorSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected author session to be destroyed");
                        delete this.authorSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.user1SessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.user1SessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.user2SessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.user2SessionId;
                    });
                }
            });
            test("ACL Anonymous", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 42;
                var testID2 = 43;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
            });
            test("ACL Administrator", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 52;
                var testID2 = 53;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
            });
            test("ACL Author", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 62;
                var testID2 = 63;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
            });
            test("ACL WfsUser", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 72;
                var testID2 = 73;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
            });
            test("ACL WmsUser", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 82;
                var testID2 = 83;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
            });

            test("ACL RestUser group", function() {
                function createInsertXml(text, geom, session) {
                    var xml = "<FeatureSet>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    xml += "<Features><Feature>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom, session) {
                    var xml = "<UpdateOperation>";
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        xml += "<SessionID>" + session + "</SessionID>";
                    }
                    if (filter != null && filter != "") {
                        xml += "<Filter>" + filter + "</Filter>";
                    }
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>RNAME</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>SHPGEOM</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                var testID1 = 92;
                var testID2 = 93;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") Expected denial");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(status == 403, "(" + status + ") Expected forbidden");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") Expected success");
                });
            });

            module("REST Session");
            test("/session", function() {
                api_test(rest_root_url + "/session", "GET", {}, function(status, result) {
                    ok(result == null, "Non-null result");
                    ok(status == 404, "(" + status+ ") - Route should not be legal");
                });
                api_test(rest_root_url + "/session", "PUT", {}, function(status, result) {
                    ok(result == null, "Non-null result");
                    ok(status == 404, "(" + status+ ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status+ ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                    ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    ok(status == 201, "(" + status+ ") - Expected created response");
                    ok(result.match(/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}_[A-Za-z]{2}_\w+[A-Fa-f0-9]{12}$/g) != null, "Expected session id pattern");

                    api_test(rest_root_url + "/session/" + result, "DELETE", null, function(status, result) {
                        ok(status == 200, "Expected OK on session destruction");
                    });
                });
                api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    ok(status == 201, "(" + status+ ") - Expected created response");
                    ok(result.match(/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}_[A-Za-z]{2}_\w+[A-Fa-f0-9]{12}$/g) != null, "Expected session id pattern");
                    api_test(rest_root_url + "/session/" + result, "DELETE", null, function(status, result) {
                        ok(status == 200, "Expected OK on session destruction");
                    });
                });
            });

            module("Resource Service - Library", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Enumerate Resources", function() {
                api_test(rest_root_url + "/library/Samples/", "GET", null, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/list", "POST", {}, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/list", "POST", { depth: -1, type: "FeatureSource" }, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/list", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/list", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/list.foo", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.foo", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.foo", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.foo", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/list", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test(rest_root_url + "/library/Samples/list", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test(rest_root_url + "/library/Samples/list", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });
                api_test(rest_root_url + "/library/Samples/list", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got a list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got an xml list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a json list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a html list back");
                });
            });
            test("Get Resource Content", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
            });
            test("Get Resource Header", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", "depth=-1&type=FeatureSource", "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
            });
            test("Enumerate Resource Data", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "POST", null, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "PUT", null, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { depth: -1, type: "LayerDefinition" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
            });
            test("Enumerate Resource References", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
            });
            test("Set/Get/Delete resource", function() {
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_with_credentials(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content", "POST", {}, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content", "POST", xml, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to save to library repo");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content", "POST", xml, function(status, result) {
                    ok(status == 201, "(" + status + ") - Should've saved resource");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource", "DELETE", xml, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to delete library resources");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource", "DELETE", xml, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });

            module("Feature Service - Library", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Spatial Contexts", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Schemas", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("DescribeSchema - SHP_Schema", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Classes - SHP_Schema", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels alternate route", function() {
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
            });
            test("Get FDO Providers", function() {
                api_test(rest_root_url + "/providers", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("SDF Provider Capabilities", function() {
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            //List Data Stores test case excluded as that requires a SQL Server feature source set up. Can always manually verify
            test("SDF Provider - Connection Property Values for ReadOnly", function() {
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                /*
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                */
            });
            test("Aggregates - count", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - bbox", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - bbox (with xform)", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - distinctvalues", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by Layer", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by Layer - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT by Layer", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT by Layer - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer with projected property list", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer with projected property list - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator)", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator)", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Insert/Update/Delete Features", function() {

                function createInsertXml(text, geom) {
                    var xml = "<FeatureSet><Features><Feature>";
                    xml += "<Property><Name>Text</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>Geometry</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom) {
                    var xml = "<UpdateOperation>";
                    xml += "<Filter>" + filter + "</Filter>";
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>Text</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>Geometry</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                function createHeaderXml(bInsert, bUpdate, bDelete, bUseTransaction) {
                    var xml = '<ResourceDocumentHeader xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="ResourceDocumentHeader-1.0.0.xsd">'
                    xml += '<Security><Inherited>true</Inherited></Security>';
                    xml += '<Metadata><Simple>'
                    if (bInsert === true) {
                        xml += "<Property><Name>_MgRestAllowInsert</Name><Value>1</Value></Property>";
                    } else {
                        xml += "<Property><Name>_MgRestAllowInsert</Name><Value>0</Value></Property>";
                    }
                    if (bUpdate === true) {
                        xml += "<Property><Name>_MgRestAllowUpdate</Name><Value>1</Value></Property>";
                    } else {
                        xml += "<Property><Name>_MgRestAllowUpdate</Name><Value>0</Value></Property>";
                    }
                    if (bDelete === true) {
                        xml += "<Property><Name>_MgRestAllowDelete</Name><Value>1</Value></Property>";
                    } else {
                        xml += "<Property><Name>_MgRestAllowDelete</Name><Value>0</Value></Property>";
                    }
                    if (bUseTransaction === true) {
                        xml += "<Property><Name>_MgRestUseTransaction</Name><Value>1</Value></Property>";
                    } else {
                        xml += "<Property><Name>_MgRestUseTransaction</Name><Value>0</Value></Property>";
                    }
                    xml += '</Simple></Metadata></ResourceDocumentHeader>';
                    return xml;
                }

                //Disable everything
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(false, false, false, false), function(status, result) {
                    ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(false, false, false, false), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect anon insert denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect admin insert denial");
                });

                //Enable insert
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, false, false, false), function(status, result) {
                    ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, false, false, true), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable insert/transactions");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect anon insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect admin insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, false, false, false), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable insert. Disable transactions");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect anon insert success");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin insert success");
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect anon update denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect admin update denial");
                });

                //Enable update
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, false, false), function(status, result) {
                    ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, false, true), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success - Enable insert/update/transactions");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect anon update failure - Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect admin update failure - Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, false, false), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success - Enable insert/update. Disable transactions");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect anon update success");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin update success");
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect admin delete denial");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result) {
                    ok(status == 403, "(" + status + ") - Expect admin delete denial");
                });

                //Enable everything
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, true, false), function(status, result) {
                    ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, true, true), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable everything");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect admin delete failure. Transactions not supported");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result) {
                    ok(status == 500, "(" + status + ") - Expect anon delete failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header", "POST", createHeaderXml(true, true, true, false), function(status, result) {
                    ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable everything except transactions");
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin delete success");
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect anon delete success");
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });

            module("Site Service", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Status", function() {
                api_test(rest_root_url + "/site/status", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/site/status", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/site/status", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/status", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/status", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Version", function() {
                api_test(rest_root_url + "/site/version", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/site/version", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/site/version", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/version", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/version", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("List Groups", function() {
                api_test(rest_root_url + "/site/groups", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/groups", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous access denied");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/groups", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/groups", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                api_test(rest_root_url + "/site/groups", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                });
            });
            test("List Roles - Anonymous", function() {
                api_test(rest_root_url + "/site/user/Anonymous/roles", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/user/Anonymous/roles", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Anonymous/roles", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/user/Anonymous/roles", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                //TODO: Review. Should anonymous be allowed to snoop its own groups and roles?
                api_test(rest_root_url + "/site/user/Anonymous/roles", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("List Roles - Administrator", function() {
                api_test(rest_root_url + "/site/user/Administrator/roles", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/user/Administrator/roles", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous access denied");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Administrator/roles", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/user/Administrator/roles", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/user/Administrator/roles", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                });
            });
            test("List Groups - Anonymous", function() {
                api_test(rest_root_url + "/site/user/Anonymous/groups", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/user/Anonymous/groups", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Anonymous/groups", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/user/Anonymous/groups", "GET", { session: this.adminSessionId }, function(status, result) {
                   ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                     ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                //TODO: Review. Should anonymous be allowed to snoop its own groups and roles?
                api_test(rest_root_url + "/site/user/Anonymous/groups", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("List Groups - Administrator", function() {
                api_test(rest_root_url + "/site/user/Administrator/groups", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/user/Administrator/groups", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous access denied");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Administrator/groups", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/user/Administrator/groups", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/user/Administrator/groups", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                });
            });
            test("List users under everyone", function() {
                api_test(rest_root_url + "/site/groups/Everyone/users", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/site/groups/Everyone/users", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous access denied");
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/groups/Everyone/users", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/groups/Everyone/users", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/groups/Everyone/users", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                });
            });

            module("REST Services", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("CopyResource", function() {
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 401, "(" + status + ") copy operation should've been denied");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels.LayerDefinition/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
            });
            test("CopyResource - Library to admin session", function() {
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.adminSessionId + "//Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.adminSessionId + "//Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 401, "(" + status + ") copy operation should've been denied");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Parcels.LayerDefinition/content", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Parcels2.LayerDefinition/content", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 404, "(" + status + ") - the parcels2 layerdef should not exist");
                });
            });
            test("CopyResource - Library to anon session", function() {
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels2.LayerDefinition/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - the parcels2 layerdef should exist");
                });
            });
            test("MoveResource", function() {
                api_test(rest_root_url + "/services/moveresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://RestUnitTests/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") move operation should've succeeded");
                });
                api_test(rest_root_url + "/services/moveresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels3.LayerDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 401, "(" + status + ") move operation should've been denied");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels2.LayerDefinition/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - the parcels2 layerdef should exist");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels.LayerDefinition/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 404, "(" + status + ") - the parcels layerdef shouldn't exist");
                });
            });

            module("Resource Service - Session", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.FeatureSource",
                        overwrite: 1
                    }, function(status, result) {
                        ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Resource Content - anon session", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                });
            });
            /*
            //Need to confirm if like EnumerateResources, this is not permitted on session repos
            test("Get Resource Header - anon session", function() {
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", "depth=-1&type=FeatureSource", "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                });
            });
            */
            test("Enumerate Resource Data - anon session", function() {
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "POST", null, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "PUT", null, function(status, result) {
                    ok(status == 404, "(" + status + ") - Route should not be legal");
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected a bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
            });
            test("Enumerate Resource References - anon session", function() {
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", null, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                });
            });
            test("Set/Get/Delete resource - anon session", function() {
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/content", "POST", xml, function(status, result) {
                    ok(status == 201, "(" + status + ") - Should've saved resource by anon");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/content", "POST", xml, function(status, result) {
                    ok(status == 201, "(" + status + ") - Should've saved resource by admin");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Empty fs should exist");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/content", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Empty2 fs should exist");
                });
                //Even if admin saved it, controller always uses session id as first priority so this should be a delete on anon's behalf
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource", "DELETE", xml, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource", "DELETE", xml, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });

            module("Feature Service - Session", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.FeatureSource",
                        overwrite: 1
                    }, function(status, result) {
                        ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.LayerDefinition",
                        overwrite: 1
                    }, function(status, result) {
                        ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://RestUnitTests/RedlineLayer.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//RedlineLayer.FeatureSource",
                        overwrite: 1
                    }, function(status, result) {
                        ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Spatial Contexts", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Schemas", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("DescribeSchema - SHP_Schema", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Classes - SHP_Schema", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels alternate route", function() {
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                });
            });
            test("Get FDO Providers", function() {
                api_test_anon(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("SDF Provider Capabilities", function() {
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            //List Data Stores test case excluded as that requires a SQL Server feature source set up. Can always manually verify
            test("SDF Provider - Connection Property Values for ReadOnly", function() {
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                /*
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                */
            });
            test("Aggregates - count", function() {
                                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - bbox", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - bbox (with xform)", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Aggregates - distinctvalues", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT by layer", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Parcels owned by SCHMITT by layer - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list by layer", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels with projected property list by layer - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator)", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator)", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Insert/Update/Delete Features", function() {

                function createInsertXml(text, geom) {
                    var xml = "<FeatureSet><Features><Feature>";
                    xml += "<Property><Name>Text</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>Geometry</Name><Value>" + geom + "</Value></Property>";
                    xml += "</Feature></Features></FeatureSet>";
                    return xml;
                }

                function createUpdateXml(filter, text, geom) {
                    var xml = "<UpdateOperation>";
                    xml += "<Filter>" + filter + "</Filter>";
                    xml += "<UpdateProperties>";
                    xml += "<Property><Name>Text</Name><Value>" + text + "</Value></Property>";
                    xml += "<Property><Name>Geometry</Name><Value>" + geom + "</Value></Property>";
                    xml += "</UpdateProperties>";
                    xml += "</UpdateOperation>";
                    return xml;
                }

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect anon insert success");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin insert success");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect anon update success");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin update success");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin delete success");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Expect admin delete success");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    var gj = JSON.parse(result);
                    ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });

            module("Rendering Service - Library", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("RenderMap", function() {
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected image response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected image response");
                });

                //PNG
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });

                //PNG8
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Png, "(" + mimeType + ") expected PNG mime type");
                });

                //JPG
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Jpeg, "(" + mimeType + ") expected JPG mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Jpeg, "(" + mimeType + ") expected JPG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Jpeg, "(" + mimeType + ") expected JPG mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Jpeg, "(" + mimeType + ") expected JPG mime type");
                });

                //GIF
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Gif, "(" + mimeType + ") expected GIF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Gif, "(" + mimeType + ") expected GIF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Gif, "(" + mimeType + ") expected GIF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected image response");
                    ok(mimeType == MgMimeType.Gif, "(" + mimeType + ") expected GIF mime type");
                });
            });
            test("RenderDynamicOverlay", function() {

            });

            module("KML Service - Library", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("GetMapKml", function() {
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });

                //Pass thru
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                });
            });
            test("GetLayerKml", function() {
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });
            test("GetFeaturesKml", function() {
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected KML response");
                    ok(mimeType == MgMimeType.Kml, "(" + mimeType + ") Expected KML mime type");
                    ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });

            module("Plotting - Library", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("PDF Plot", function() {
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
            });
            test("Layered PDF Plot", function() {
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected pdf response");
                    ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
            });
            test("DWF Plot", function() {
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, scale: 8000 }, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected dwf response");
                    ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected dwf response");
                    ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected dwf response");
                    ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.adminSessionId }, function(status, result, mimeType) {
                    ok(status == 200, "(" + status + ") - Expected dwf response");
                    ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
            });

            module("Tile Service", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });
                }
            });
            test("GetTile", function() {
                //With raw credentials
                api_test(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
            });
            test("GetTile - session copy", function() {
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Sheboygan.MapDefinition",
                    overwrite: 1
                }, function(status, result) {
                    ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                //With raw credentials
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile/Base Layer Group/6/1/0", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
            });

            module("Coordinate System", {
                setup: function() {
                    var self = this;
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "Anonymous", "", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = result;
                    });
                    api_test_with_credentials(rest_root_url + "/session", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                        ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = result;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result) {
                        ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Transform batch coordinates", function() {
                var params = {
                    from: "LL84",
                    to: "WGS84.PseudoMercator",
                    coords: "-87.1 43.2,-87.2 43.3,-87.4 43.1"
                };
                var paramsWithPadding = {
                    from: "LL84",
                    to: "WGS84.PseudoMercator",
                    coords: "-87.1 43.2, -87.2 43.3, -87.4 43.1"
                };
                var paramsBadCoords = {
                    from: "LL84",
                    to: "WGS84.PseudoMercator",
                    coords: "-87.1 43.2,-87.2,43.3,-87.4 43.1"
                };
                var paramsBogusCs = {
                    from: "LL84",
                    to: "Foobar",
                    coords: "-87.1 43.2,-87.2 43.3,-87.4 43.1"
                };
                var paramsIncomplete1 = {
                    coords: "-87.1 43.2,-87.2 43.3,-87.4 43.1"
                };
                var paramsIncomplete2 = {
                    from: "LL84",
                    coords: "-87.1 43.2,-87.2 43.3,-87.4 43.1"
                };

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, "Anonymous", "", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected server error. Bogus target coord sys.");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected server error. Bogus target coord sys.");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, function(status, result) {
                    ok(status == 500, "(" + status + ") - Expected server error. Bogus target coord sys.");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, function(status, result) {
                    ok(status == 500, "(" + status + ") - Expected server error. Bogus target coord sys.");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, "Anonymous", "", function(status, result) {
                    ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, "Anonymous", "", function(status, result) {
                    ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", params, "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", params, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", params, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", params, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, "Anonymous", "", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, "Anonymous", "", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), "Anonymous", "", function(status, result) {
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), function(status, result) {
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), function(status, result) {
                    ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), "Anonymous", "", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), function(status, result) {
                    ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                });
            });
            test("Enum categories", function() {
                api_test(rest_root_url + "/coordsys/categories", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/coordsys/categories.sadgdsfd", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/coordsys/categories.sadgdsfd", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/categories", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/categories", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/categories.xml", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.xml", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/categories.xml", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/categories.json", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.json", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/categories.json", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/categories.html", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.html", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/categories.html", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("Enum categories - Australia", function() {
                api_test(rest_root_url + "/coordsys/category/Australia", "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                api_test_anon(rest_root_url + "/coordsys/category.sdgfd/Australia", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });
                api_test_admin(rest_root_url + "/coordsys/category.sdgfd/Australia", "GET", null, function(status, result) {
                    ok(status == 400, "(" + status + ") - Expected bad representation response");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category/Australia", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/category/Australia", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category/Australia", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/category/Australia", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.xml/Australia", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/category.xml/Australia", "GET", null, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.xml/Australia", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/category.xml/Australia", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML respose");
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.json/Australia", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/category.json/Australia", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.json/Australia", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/category.json/Australia", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.html/Australia", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/coordsys/category.html/Australia", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.html/Australia", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/coordsys/category.html/Australia", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("EPSG for LL84", function() {
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg", "GET", null, function(status, result) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/epsg", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == "4326", "Expected EPSG of 4326. Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/epsg", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == "4326", "Expected EPSG of 4326. Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == "4326", "Expected EPSG of 4326. Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == "4326", "Expected EPSG of 4326. Got: " + result);
                });
            });
            test("WKT for LL84", function() {
                var expect = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt", "GET", null, function(status, result) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/wkt", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/wkt", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
            });
            test("Mentor code for EPSG:4326", function() {
                var expect = "LL84";
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor", "GET", null, function(status, result) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/mentor", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/mentor", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
            });
            test("WKT for EPSG:4326", function() {
                var expect = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt", "GET", null, function(status, result) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/wkt", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/wkt", "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt", "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt", "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected WKT of " + expect + ". Got: " + result);
                });
            });
            /*
            test("WKT to mentor", function() {
                var wkt = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                var expect = "LL84";
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected code of " + expect + ". Got: " + result);
                });
            });
            test("WKT to epsg", function() {
                var wkt = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                var expect = "4326";
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result) {
                    ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected EPSG of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected EPSG of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", { session: this.anonymousSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected EPSG of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", { session: this.adminSessionId }, function(status, result) {
                    ok(status == 200, "(" + status + ") - Response should've been ok");
                    ok(result == expect, "Expected EPSG of " + expect + ". Got: " + result);
                });
            });
            */

        </script>
    </body>
</html>
