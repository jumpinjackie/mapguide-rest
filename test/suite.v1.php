<?php

?>
            module("REST publishing", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.wfsSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.wmsSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.authorSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.user1SessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.user2SessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.wfsSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected wfs session to be destroyed");
                        delete this.wfsSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.wmsSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected session session to be destroyed");
                        delete this.wmsSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.authorSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected author session to be destroyed");
                        delete this.authorSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.user1SessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.user1SessionId;
                    });
                    api_test(rest_root_url + "/session/" + this.user2SessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.user2SessionId;
                    });
                }
            });
            test("ACL Anonymous", function() {
                var self = this;
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

                function createInsertJson(text, geom, session) {
                    var json = {
                        "FeatureSet": {
                            "Features": {
                                "Feature": [
                                    { 
                                        "Property": [
                                            { "Name": "RNAME", "Value": text },
                                            { "Name": "SHPGEOM", "Value": geom }
                                        ] 
                                    }
                                ]
                            }
                        }
                    };
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        json.FeatureSet.SessionID = session;
                    }
                    return JSON.stringify(json);
                }

                function createUpdateJson(filter, text, geom, session) {
                    var json = {
                        "UpdateOperation": {
                            "UpdateProperties": {
                                "Property": [
                                    { "Name": "RNAME", "Value": text },
                                    { "Name": "SHPGEOM", "Value": geom }
                                ]
                            }
                        }
                    };
                    if (typeof(session) != 'undefined' && session != null && session != "") {
                        json.UpdateOperation.SessionID = session;
                    }
                    if (filter != null && filter != "") {
                        json.UpdateOperation.Filter = filter;
                    }
                    return JSON.stringify(json);
                }

                var testID1 = 42;
                var testID2 = 43;
                var testID3 = 1234;

                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_anonymous/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                ///////////////////////////// JSON VERSION ////////////////////////////////////
                testID1 = 47;
                testID2 = 48;
                testID3 = 2345;

                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID3 + ".json", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "POST", createInsertJson("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "PUT", createUpdateJson("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/" + testID2 + ".json", "PUT", createUpdateJson("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/data/test_anonymous/.json", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/data/test_anonymous/.json", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

            });
            test("ACL Administrator", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_administrator/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_administrator/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("ACL Author", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_author/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_author/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("ACL WfsUser", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wfsuser/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("ACL WmsUser", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_wmsuser/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });

            test("ACL RestUser group", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Single access
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", {}, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.wfsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.wmsSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.authorSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.user1SessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID3 + ".xml", "GET", { session: this.user2SessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Insert
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "POST", createInsertXml("user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Update
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "PUT", createUpdateXml("Autogenerated_SDF_ID = " + testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Update - single access
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.anonymousSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.adminSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wfsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.wmsSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.authorSessionId), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user1SessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/" + testID2 + ".xml", "PUT", createUpdateXml("", "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", this.user2SessionId), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Delete
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wfsUser ?>", "<?= $wfsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $wmsUser ?>", "<?= $wmsPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $authorUser ?>", "<?= $authorPass ?>", function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user1User ?>", "<?= $user1Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/data/test_group/.xml", "DELETE", { filter: "Autogenerated_SDF_ID = " + testID1 }, "<?= $user2User ?>", "<?= $user2Pass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.anonymousSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.wfsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.wmsSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.authorSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.adminSessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.user1SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/data/test_group/.xml", "DELETE", { session: this.user2SessionId, filter: "Autogenerated_SDF_ID = " + testID2 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") Expected success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });

            module("REST Session", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                },
                teardown: function() {
                }
            });
            test("/session", function() {
                var self = this;
                api_test(rest_root_url + "/session", "GET", {}, function(status, result, mimeType) {
                    self.ok(result == null, "Non-null result");
                    self.ok(status == 404, "(" + status+ ") - Route should not be legal");
                });
                api_test(rest_root_url + "/session", "PUT", {}, function(status, result, mimeType) {
                    self.ok(result == null, "Non-null result");
                    self.ok(status == 404, "(" + status+ ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/session.xml", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    self.ok(status == 201, "(" + status+ ") - Expected created response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/session.xml", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    self.ok(status == 201, "(" + status+ ") - Expected created response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    self.ok(status == 201, "(" + status+ ") - Expected created response");
                    var sessionId = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(sessionId.match(/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}_[A-Za-z]{2}_\w+[A-Fa-f0-9]{12}$/g) != null, "Expected session id pattern");

                    api_test(rest_root_url + "/session/" + sessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "Expected OK on session destruction");
                    });
                });
                api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                    self.ok(status == 201, "(" + status+ ") - Expected created response");
                    var sessionId = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(sessionId.match(/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}_[A-Za-z]{2}_\w+[A-Fa-f0-9]{12}$/g) != null, "Expected session id pattern");
                    api_test(rest_root_url + "/session/" + sessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "Expected OK on session destruction");
                    });
                });
            });

            module("Resource Service - Library", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Enumerate Resources", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/list", "POST", {}, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/list", "POST", { depth: -1, type: "FeatureSource" }, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/list.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/list.xml", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/list.foo", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.foo", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.foo", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.foo", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got an xml list back");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a json list back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/list.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/list.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a html list back");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Resource Content", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Get Resource Header", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", "depth=-1&type=FeatureSource", "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("JSON resource content roundtripping", function() {
                var self = this;
                var test = null;
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    test = JSON.parse(result);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/content.json", "POST", JSON.stringify(test), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've posted resource JSON");
                });
            });
            test("Enumerate Resource Data", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "POST", null, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist", "PUT", null, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Set/Delete Resource Data", function() {
                var self = this;
                var params = {
                    type: "File",
                    data: makeXmlBlob("<Test></Test>")
                };
                //Various bad requests
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", params, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", params, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expected forbidden");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad parameters response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                //Load the data item
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $.extend(params, { session: this.anonymousSessionId }), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "POST", $.extend(params, { session: this.adminSessionId }), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                //Check the data item is on the list
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                //Delete the item
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expected denial");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/data/test.xml", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                });
                //Now check the data item is no longer there
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(!bFound, "Expected test.xml to not be in data list");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(!bFound, "Expected test.xml to not be in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(!bFound, "Expected test.xml to not be in data list");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(!bFound, "Expected test.xml to not be in data list");
                });
            });
            test("Enumerate Resource References", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Set/Get/Delete resource", function() {
                var self = this;
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_with_credentials(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content.xml", "POST", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content.xml", "POST", xml, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to save to library repo");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource/content.xml", "POST", xml, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to delete library resources");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });
            test("Set/Get/Delete resource alt", function() {
                var self = this;
                function createHeaderXml() {
                    var xml = '<ResourceDocumentHeader xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="ResourceDocumentHeader-1.0.0.xsd">'
                    xml += '<Security><Inherited>true</Inherited></Security>';
                    xml += '<Metadata><Simple>';
                    xml += "<Property><Name>HelloWorld</Name><Value>1</Value></Property>";
                    xml += '</Simple></Metadata>';
                    xml += '</ResourceDocumentHeader>';
                    return xml;
                }
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_with_credentials(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/contentorheader.xml", "POST", {}, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/contentorheader.xml", "POST", { content: makeXmlBlob(xml) }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to save to library repo");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/contentorheader.xml", "POST", { content: makeXmlBlob(xml) }, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    var header = result || "";
                    self.ok(header.indexOf("<Name>HelloWorld</Name>") < 0, "Expected no header");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/contentorheader.xml", "POST", { content: makeXmlBlob(xml), header: makeXmlBlob(createHeaderXml()) }, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource content and header");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    var header = result || "";
                    self.ok(header.indexOf("<Name>HelloWorld</Name>") >= 0, "Expected header set");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous shouldn't be able to delete library resources");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/Empty2.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });
            module("Feature Service - Library", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Spatial Contexts", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Get Schemas", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schemas.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("DescribeSchema - SHP_Schema", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Classes - SHP_Schema", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels alternate route", function() {
                var self = this;
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get FDO Providers", function() {
                var self = this;
                api_test(rest_root_url + "/providers.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/providers.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/providers.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("SDF Provider Capabilities", function() {
                var self = this;
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            //List Data Stores test case excluded as that requires a SQL Server feature source set up. Can always manually verify
            test("SDF Provider - Connection Property Values for ReadOnly", function() {
                var self = this;
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - count", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/count/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - bbox", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - bbox (with xform)", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transform: "WGS84.PseudoMercator", session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - distinctvalues", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels by Layer", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels by Layer - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Parcels owned by SCHMITT", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Parcels owned by SCHMITT - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Parcels owned by SCHMITT by Layer", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Parcels owned by SCHMITT by Layer - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels with projected property list", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels with projected property list - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels by layer with projected property list", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels by layer with projected property list - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator)", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator)", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("BBOX select (with and without transform)", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { bbox: "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { bbox: "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                //Raw credentials with WGS84.PseudoMercator bbox. Expect no results
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { transformto: "WGS84.PseudoMercator", bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length != 1, "Expected (n != 1) results");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { transformto: "WGS84.PseudoMercator", bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length != 1, "Expected (n != 1) results");
                });
                //Raw credentials with WGS84.PseudoMercator bbox with the target cs hint flag
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { transformto: "WGS84.PseudoMercator", bboxistargetcs: 1, bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { transformto: "WGS84.PseudoMercator", bboxistargetcs: 1, bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });

                //With session id
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.anonymousSessionId, bbox: "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.adminSessionId, bbox: "-87.71342839441816,43.74687173218348,-87.70806397638839,43.7499718637344" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                //Raw credentials with WGS84.PseudoMercator bbox. Expect no results
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.anonymousSessionId, transformto: "WGS84.PseudoMercator", bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length != 1, "Expected (n != 1) results");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.adminSessionId, transformto: "WGS84.PseudoMercator", bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length != 1, "Expected (n != 1) results");
                });
                //Raw credentials with WGS84.PseudoMercator bbox with the target cs hint flag
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.anonymousSessionId, transformto: "WGS84.PseudoMercator", bboxistargetcs: 1, bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Data/VotingDistricts.FeatureSource/features.geojson/Default/VotingDistricts", "GET", { session: this.adminSessionId, transformto: "WGS84.PseudoMercator", bboxistargetcs: 1, bbox: "-9764214.1845989,5426353.1981194,-9763617.0203154,5426830.9295462" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var json = JSON.parse(result);
                    self.ok(json.features.length == 1, "Expected 1 result");
                    self.ok(json.features[0].id == 6, "Expected feature with id = 6");
                });
            });
            test("Insert/Update/Delete Features", function() {
                var self = this;
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
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(false, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(false, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect anon insert denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin insert denial");
                });

                //Enable insert
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, false, false, true), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable insert/transactions");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable insert. Disable transactions");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect anon insert success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin insert success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect anon update denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin update denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Enable update
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, false, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, false, true), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success - Enable insert/update/transactions");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon update failure - Transactions not supported");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin update failure - Transactions not supported");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, false, false), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success - Enable insert/update. Disable transactions");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect anon update success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin update success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin delete denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin delete denial");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //Enable everything
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, true, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, true, true), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable everything");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin delete failure. Transactions not supported");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon delete failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/header.xml", "POST", createHeaderXml(true, true, true, false), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin setresourceheader success. Enable everything except transactions");
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    self.ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect anon delete success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });
            test("Insert/Update/Delete Features - JSON", function() {
                var self = this;
                function createEditCapsJson(bInsert, bUpdate, bDelete, bUseTransaction) {
                    var caps = {
                        "RestCapabilities": {
                            "AllowInsert": bInsert,
                            "AllowUpdate": bUpdate,
                            "AllowDelete": bDelete,
                            "UseTransaction": bUseTransaction
                        }
                    };
                    return JSON.stringify(caps);
                }

                function createInsertJson(text, geomWkt) {
                    var json = {
                        "FeatureSet": {
                            "Features": {
                                "Feature": [
                                    {
                                        "Property": [
                                            { "Name": "Text", "Value": text },
                                            { "Name": "Geometry", "Value": geomWkt }
                                        ]
                                    }
                                ]
                            }
                        }
                    };
                    return JSON.stringify(json);
                }

                function createUpdateJson(filter, text, geomWkt) {
                    var json = {
                        "UpdateOperation": {
                            "Filter": filter,
                            "UpdateProperties": {
                                "Property": [
                                    { "Name": "Text", "Value": text },
                                    { "Name": "Geometry", "Value": geomWkt }
                                ]
                            }
                        }
                    };
                    return JSON.stringify(json);
                }

                //Disable everything
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(false, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon editcapabilities denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(false, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect anon insert denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin insert denial");
                });

                //Enable insert
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, false, false, true), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon editcapabilities denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, false, false, true), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin insert failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, false, false, false), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect anon insert success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin insert success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect anon update denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin update denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Enable update
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, false, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon editcapabilities denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, false, true), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success - Enable insert/update/transactions");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon update failure - Transactions not supported");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin update failure - Transactions not supported");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, false, false), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success - Enable insert/update. Disable transactions");
                });

                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect anon update success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin update success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin delete denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 403, "(" + status + ") - Expect admin delete denial");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //Enable everything
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, true, false), function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Expect anon setresourceheader denial");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, true, true), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin setresourceheader success. Enable everything");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect admin delete failure. Transactions not supported");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expect anon delete failure. Transactions not supported");
                });
                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/editcapabilities.json", "POST", createEditCapsJson(true, true, true, false), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expect admin editcapabilities success. Enable everything except transactions");
                });

                api_test_admin(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    self.ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect anon delete success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/library/RestUnitTests/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });
            module("Site Service", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Status", function() {
                var self = this;
                api_test(rest_root_url + "/site/status.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/site/status.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/site/status.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/status.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/site/status.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                
                //With raw credentials
                api_test_anon(rest_root_url + "/site/status.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/site/status.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/site/status.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/site/status.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Get Version", function() {
                var self = this;
                api_test(rest_root_url + "/site/version.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/site/version.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/site/version.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/version.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/version.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                
                //With raw credentials
                api_test_anon(rest_root_url + "/site/version.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test_admin(rest_root_url + "/site/version.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With session id
                api_test(rest_root_url + "/site/version.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/site/version.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
            });
            test("List Groups", function() {
                var self = this;
                api_test(rest_root_url + "/site/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous access denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/groups.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test(rest_root_url + "/site/groups.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                
                //With raw credentials
                api_test_admin(rest_root_url + "/site/groups.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/site/groups.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                api_test(rest_root_url + "/site/groups.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("List Roles - Anonymous", function() {
                var self = this;
                api_test(rest_root_url + "/site/user/Anonymous/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/user/Anonymous/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Anonymous/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/user/Anonymous/roles.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //TODO: Review. Should anonymous be allowed to snoop its own groups and roles?
                api_test(rest_root_url + "/site/user/Anonymous/roles.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("List Roles - Administrator", function() {
                var self = this;
                api_test(rest_root_url + "/site/user/Administrator/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/user/Administrator/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous access denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Administrator/roles.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/user/Administrator/roles.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/site/user/Administrator/roles.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("List Groups - Anonymous", function() {
                var self = this;
                api_test(rest_root_url + "/site/user/Anonymous/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/user/Anonymous/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Anonymous/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/user/Anonymous/groups.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                   self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                     self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //TODO: Review. Should anonymous be allowed to snoop its own groups and roles?
                api_test(rest_root_url + "/site/user/Anonymous/groups.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("List Groups - Administrator", function() {
                var self = this;
                api_test(rest_root_url + "/site/user/Administrator/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/user/Administrator/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous access denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/user/Administrator/groups.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/user/Administrator/groups.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/site/user/Administrator/groups.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("List users under everyone", function() {
                var self = this;
                api_test(rest_root_url + "/site/groups/Everyone/users.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/site/groups/Everyone/users.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous access denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_admin(rest_root_url + "/site/groups/Everyone/users.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/site/groups/Everyone/users.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/site/groups/Everyone/users.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Anonymous session id should've been denied");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });

            module("REST Services", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("CopyResource", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") copy operation should've been denied");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels.LayerDefinition/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
            });
            test("CopyResource - Library to admin session", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.adminSessionId + "//Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.adminSessionId + "//Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") copy operation should've been denied");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Parcels.LayerDefinition/content.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Parcels2.LayerDefinition/content.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - the parcels2 layerdef should not exist");
                });
            });
            test("CopyResource - Library to anon session", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Parcels.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - the parcels layerdef should exist");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels2.LayerDefinition/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - the parcels2 layerdef should exist");
                });
            });
            test("MoveResource", function() {
                var self = this;
                api_test(rest_root_url + "/services/moveresource", "POST", {
                    session: this.adminSessionId,
                    source: "Library://RestUnitTests/Parcels.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") move operation should've succeeded");
                });
                api_test(rest_root_url + "/services/moveresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://RestUnitTests/Parcels2.LayerDefinition",
                    destination: "Library://RestUnitTests/Parcels3.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") move operation should've been denied");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels2.LayerDefinition/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - the parcels2 layerdef should exist");
                });
                api_test(rest_root_url + "/library/RestUnitTests/Parcels.LayerDefinition/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - the parcels layerdef shouldn't exist");
                });
            });

            module("Resource Service - Session", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.FeatureSource",
                        overwrite: 1
                    }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Get Resource Content - anon session", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.bar", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/content.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource content back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            /*
            //Need to confirm if like EnumerateResources, this is not permitted on session repos
            test("Get Resource Header - anon session", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", "depth=-1&type=FeatureSource", "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.sdfjkdsg", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/header.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource header back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            */
            test("Enumerate Resource Data - anon session", function() {
                var self = this;
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "POST", null, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "PUT", null, function(status, result, mimeType) {
                    self.ok(status == 404, "(" + status + ") - Route should not be legal");
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.jsdhf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected a bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/datalist.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Enumerate Resource References - anon session", function() {
                var self = this;
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", null, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "FeatureSource" }, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.sdjf", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.xml", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as xml");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.json", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as json");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.anonymousSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/references.html", "GET", { session: this.adminSessionId, depth: -1, type: "LayerDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got resource data list back as html");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Set/Get/Delete resource - anon session", function() {
                var self = this;
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/content.xml", "POST", xml, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource by anon");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/content.xml", "POST", xml, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource by admin");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(status == 200, "(" + status + ") - Empty fs should exist");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(status == 200, "(" + status + ") - Empty2 fs should exist");
                });
                //Even if admin saved it, controller always uses session id as first priority so this should be a delete on anon's behalf
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });
            test("Set/Get/Delete resource - anon session alt", function() {
                var self = this;
                function createHeaderXml(str) {
                    var xml = '<ResourceDocumentHeader xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="ResourceDocumentHeader-1.0.0.xsd">'
                    xml += '<Security><Inherited>true</Inherited></Security>';
                    xml += '<Metadata><Simple>';
                    xml += "<Property><Name>" + str + "</Name><Value>1</Value></Property>";
                    xml += '</Simple></Metadata>';
                    xml += '</ResourceDocumentHeader>';
                    return xml;
                }
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/contentorheader.xml", "POST", { content: makeXmlBlob(xml) }, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource by anon");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/contentorheader.xml", "POST", { content: makeXmlBlob(xml) }, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource by admin");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(status == 200, "(" + status + ") - Empty fs should exist");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource/content.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(status == 200, "(" + status + ") - Empty2 fs should exist");
                });
                //Even if admin saved it, controller always uses session id as first priority so this should be a delete on anon's behalf
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty2.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Empty.FeatureSource", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've deleted resource");
                });
            });
            test("Set/Delete Resource Data", function() {
                var self = this;
                var params = {
                    type: "File",
                    data: makeXmlBlob("<Test></Test>")
                };
                var xml = '<?= $emptyFeatureSourceXml ?>';
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/content.xml", "POST", xml, function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Should've saved resource by anon");
                });
                //Various bad requests
                api_test_admin(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "POST", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad parameters response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                //Load the data item - All tests should work because the session id in the resource id is enough to provide valid credentials and is first under consideration
                api_test_with_credentials(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "POST", params, "Foo", "Bar", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "POST", params, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "POST", params, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "POST", params, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                //Check the data item is on the list
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test_admin(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") >= 0, "Expected test.xml in data list");
                });
                api_test_admin(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var bFound = false;
                    for (var i = 0; i < resp.ResourceDataList.ResourceData.length; i++) {
                        var data = resp.ResourceDataList.ResourceData[i];
                        if (data.Name == "test.xml") {
                            bFound = true;
                            break;
                        }
                    }
                    self.ok(bFound, "Expected test.xml in data list");
                });
                //Delete the item
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/data/test.xml", "DELETE", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                });
                //Now check the data item is no longer there
                api_test_admin(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                    self.ok(result.indexOf(">test.xml<") < 0, "Expected test.xml to not be in data list");
                });
                api_test_admin(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var resData = resp.ResourceDataList.ResourceData;
                    self.ok(typeof(resData) == 'undefined', "Expected undefined resource data list (no elements)");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Empty.FeatureSource/datalist.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var resp = JSON.parse(result);
                    var resData = resp.ResourceDataList.ResourceData;
                    self.ok(typeof(resData) == 'undefined', "Expected undefined resource data list (no elements)");
                });
            });

            module("Feature Service - Session", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Data/Parcels.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.FeatureSource",
                        overwrite: 1
                    }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://Samples/Sheboygan/Layers/Parcels.LayerDefinition",
                        destination: "Session:" + this.anonymousSessionId + "//Parcels.LayerDefinition",
                        overwrite: 1
                    }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                    api_test(rest_root_url + "/services/copyresource", "POST", {
                        session: this.anonymousSessionId,
                        source: "Library://RestUnitTests/RedlineLayer.FeatureSource",
                        destination: "Session:" + this.anonymousSessionId + "//RedlineLayer.FeatureSource",
                        overwrite: 1
                    }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Create Feature Source", function() {
                var self = this;
                var fsXml = "<FeatureSourceParams>";
                       fsXml += "<File>";
                            fsXml += "<Provider>OSGeo.SDF</Provider>";
                            fsXml += "<FileName>Test.sdf</FileName>";
                        fsXml += "</File>";
                        fsXml += "<SpatialContext>";
                            fsXml += "<Name>Default</Name>";
                            fsXml += "<Description>Default Spatial Context</Description>";
                            fsXml += "<CoordinateSystem>LL84</CoordinateSystem>";
                            fsXml += "<XYTolerance>0.00001</XYTolerance>";
                            fsXml += "<ZTolerance>0.00001</ZTolerance>";
                        fsXml += "</SpatialContext>";
                        fsXml += "<FeatureSchema>";
                            fsXml += "<Name>Default</Name>";
                            fsXml += "<Description>Default Feature Schema</Description>";
                            fsXml += "<ClassDefinition>";
                                fsXml += "<Name>Class1</Name>";
                                fsXml += "<Description>First feature class</Description>";
                                fsXml += "<DefaultGeometryPropertyName>Geometry</DefaultGeometryPropertyName>";
                                fsXml += "<PropertyDefinition>";
                                    fsXml += "<Name>ID</Name>";
                                    fsXml += "<Description>Autogenerated ID property</Description>";
                                    fsXml += "<PropertyType>100</PropertyType>";
                                    fsXml += "<IsIdentity>true</IsIdentity>";
                                    fsXml += "<DataType>7</DataType>";
                                    fsXml += "<Nullable>false</Nullable>";
                                    fsXml += "<ReadOnly>false</ReadOnly>";
                                    fsXml += "<IsAutoGenerated>true</IsAutoGenerated>";
                                fsXml += "</PropertyDefinition>";
                                fsXml += "<PropertyDefinition>";
                                    fsXml += "<Name>Geometry</Name>";
                                    fsXml += "<Description>Geometry Property</Description>";
                                    fsXml += "<PropertyType>102</PropertyType>";
                                    fsXml += "<GeometryTypes>4</GeometryTypes>";
                                    fsXml += "<HasElevation>false</HasElevation>";
                                    fsXml += "<HasMeasure>false</HasMeasure>";
                                    fsXml += "<ReadOnly>false</ReadOnly>";
                                    fsXml += "<SpatialContextAssociation>Default</SpatialContextAssociation>";
                                fsXml += "</PropertyDefinition>";
                            fsXml += "</ClassDefinition>";
                        fsXml += "</FeatureSchema>";
                    fsXml += "</FeatureSourceParams>";

                var fsJson = {"FeatureSourceParams":{"@xmlns:xsi":"http://www.w3.org/2001/XMLSchema-instance","File":{"Provider":"OSGeo.SDF","FileName":"Test.sdf"},"SpatialContext":{"Name":"Default","Description":"Default Spatial Context","CoordinateSystem":"LL84","XYTolerance":"0.00001","ZTolerance":"0.00001"},"FeatureSchema":{"Name":"Default","Description":"Default Feature Schema","ClassDefinition":[{"Name":"Class1","Description":"First feature class","DefaultGeometryPropertyName":"Geometry","PropertyDefinition":[{"Name":"ID","Description":"Autogenerated ID property","PropertyType":"100","IsIdentity":"true","DataType":"7","Nullable":"false","ReadOnly":"false","IsAutoGenerated":"true"},{"Name":"Geometry","Description":"Geometry Property","PropertyType":"102","GeometryTypes":"4","HasElevation":"false","HasMeasure":"false","ReadOnly":"false","SpatialContextAssociation":"Default"}]}]}}};

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/CreateXml.FeatureSource/xml", "POST", fsXml, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected success");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/CreateJson.FeatureSource/json", "POST", JSON.stringify(fsJson), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected success");
                });
            });
            test("Get Spatial Contexts", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.sdigud", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/spatialcontexts.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Get Schemas", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.ksjdg", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schemas.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("DescribeSchema - SHP_Schema", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/schema.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Classes - SHP_Schema", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.sdgudkf/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.xml/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.json/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classes.html/SHP_Schema", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema/Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get Class Definition - SHP_Schema:Parcels alternate route", function() {
                var self = this;
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.jsdfjkdf/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.xml/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.json/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/classdef.html/SHP_Schema:Parcels", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected unsupported html representation");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Get FDO Providers", function() {
                var self = this;
                api_test_anon(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers.sdgfdsf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/providers.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/providers.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("SDF Provider Capabilities", function() {
                var self = this;
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.ksdjgdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/capabilities.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            //List Data Stores test case excluded as that requires a SQL Server feature source set up. Can always manually verify
            test("SDF Provider - Connection Property Values for ReadOnly", function() {
                var self = this;
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.skdjfkd/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.xml/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/providers/OSGeo.SDF/connectvalues.json/ReadOnly", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                /*
                api_test_anon(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/providers/OSGeo.SDF/connectvalues.html/ReadOnly", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                */
            });
            test("Aggregates - count", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/count/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - bbox", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - bbox (with xform)", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/bbox/SHP_Schema/Parcels", "GET", { transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Aggregates - distinctvalues", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected error. Missing required parameter.");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.xml/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/aggregates.json/distinctvalues/SHP_Schema/Parcels", "GET", { property: "RTYPE" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels by layer", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels by layer - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Parcels owned by SCHMITT", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Parcels owned by SCHMITT - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Parcels owned by SCHMITT by layer", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Parcels owned by SCHMITT by layer - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, filter: encodeURIComponent("RNAME LIKE 'SCHMITT%'") }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels with projected property list", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels with projected property list - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels with projected property list by layer", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels with projected property list by layer - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, properties: "Autogenerated_SDF_ID,RNAME,SHPGEOM", maxfeatures: 100 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator)", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.xml/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.FeatureSource/features.geojson/SHP_Schema/Parcels", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator)", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.xml", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
            });
            test("Select 100 Parcels by layer (xformed to WGS84.PseudoMercator) - GeoJSON", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Parcels.LayerDefinition/features.geojson", "GET", { session: this.adminSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Insert/Update/Delete Features", function() {
                var self = this;
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
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect anon insert success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "POST", createInsertXml("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin insert success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect anon update success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "PUT", createUpdateXml("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin update success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    self.ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.xml/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });

            test("Insert/Update/Delete Features - JSON", function() {
                var self = this;
                function createInsertJson(text, geomWkt) {
                    var json = {
                        "FeatureSet": {
                            "Features": {
                                "Feature": [
                                    {
                                        "Property": [
                                            { "Name": "Text", "Value": text },
                                            { "Name": "Geometry", "Value": geomWkt }
                                        ]
                                    }
                                ]
                            }
                        }
                    };
                    return JSON.stringify(json);
                }

                function createUpdateJson(filter, text, geomWkt) {
                    var json = {
                        "UpdateOperation": {
                            "Filter": filter,
                            "UpdateProperties": {
                                "Property": [
                                    { "Name": "Text", "Value": text },
                                    { "Name": "Geometry", "Value": geomWkt }
                                ]
                            }
                        }
                    };
                    return JSON.stringify(json);
                }

                //With raw credentials
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("anon credential insert", "POINT (0 0)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect anon insert success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "POST", createInsertJson("admin credential insert", "POINT (1 1)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin insert success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential insert", "expected correct feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential insert", "expected correct feature text for ID 2");
                        }
                    }
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 1", "anon credential update", "POINT (2 2)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect anon update success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "PUT", createUpdateJson("ID = 2", "admin credential update", "POINT (3 3)"), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin update success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 2, "Expected 2 inserted features");
                    for (var i = 0; i < gj.features.length; i++) {
                        if (gj.features[i].id == 1) {
                            self.ok(gj.features[i].properties.Text == "anon credential update", "expected correct updated feature text for ID 1");
                        } else if (gj.features[i].id == 2) {
                            self.ok(gj.features[i].properties.Text == "admin credential update", "expected correct updated feature text for ID 2");
                        }
                    }
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 2" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 1, "Expected 1 inserted features. Got " + gj.features.length);
                    self.ok(gj.features[0].id == 1, "expected feature ID 2 to be deleted");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.json/MarkupSchema/Markup", "DELETE", { filter: "ID = 1" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expect admin delete success");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/RedlineLayer.FeatureSource/features.geojson/MarkupSchema/Markup", "GET", { session: this.anonymousSessionId, maxfeatures: 100, transformto: "WGS84.PseudoMercator" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var gj = JSON.parse(result);
                    self.ok(gj.features.length == 0, "Expected 0 inserted features. Got " + gj.features.length);
                });
            });

            module("Rendering Service - Library", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("RenderMap", function() {
                var self = this;
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                });

                //PNG
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });

                //PNG8
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.png8", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Png);
                });

                //JPG
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Jpeg);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Jpeg);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Jpeg);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.jpg", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Jpeg);
                });

                //GIF
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Gif);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Gif);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Gif);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/image.gif", "GET", { x: -87.73, y: 43.74, scale: 8000, width: 320, height: 200, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected image response");
                    self.assertMimeType(mimeType, MgMimeType.Gif);
                });
            });
            test("RenderDynamicOverlay", function() {

            });

            module("KML Service", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("GetMapKml - Library", function() {
                var self = this;
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });

                if (!CORS_TESTING) {
                    //Pass thru
                    api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                }
            });
            test("GetLayerKml - Library", function() {
                var self = this;
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });
            if (!CORS_TESTING) {
            test("GetFeaturesKml - Library", function() {
                var self = this;
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Layers/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });
            }
            test("GetMapKml - Session", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Sheboygan.MapDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });

                api_test(rest_root_url + "/services/createmap.json", "POST", {
                    session: this.anonymousSessionId,
                    mapdefinition: "Library://Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition",
                    targetmapname: "Sheboygan"
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") createmap should've succeeded");
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });

                if (!CORS_TESTING) {
                    //Pass thru
                    api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                }

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });

                if (!CORS_TESTING) {
                    //Pass thru
                    api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.Map/kml", "GET", { "native": 1 }, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected KML response");
                        self.assertMimeType(mimeType, MgMimeType.Kml);
                        self.ok(result.indexOf("mapagent/mapagent.fcgi") >= 0, "Expected mapagent callback urls in response");
                    });
                }
            });
            test("GetLayerKml - Session", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Districts.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Districts.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });

                //Various missing parameters
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kml", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });
            if (!CORS_TESTING) {
            test("GetFeaturesKml - Session", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/Layers/Districts.LayerDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Districts.LayerDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });

                //Various missing parameters
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                });

                //The actual valid requests
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Districts.LayerDefinition/kmlfeatures", "GET", { width: 640, height: 480, draworder: 1, bbox: "-87.8779085915893,43.63163894079797,-87.58662241010836,43.81974480009569" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected KML response");
                    self.assertMimeType(mimeType, MgMimeType.Kml);
                    self.ok(result.indexOf("mapagent/mapagent.fcgi") < 0, "Expected no mapagent callback urls in response");
                });
            });
            }
            module("Runtime Map", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Layer/Group Modification (XML)", function() {
                var reqFeatures = (1|2|4);
                var anonMapName = null;
                var adminMapName = null;
                
                function createLayerXml(fsId, className, geom) {
                    var xml = '<LayerDefinition xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" version="1.0.0" xsi:noNamespaceSchemaLocation="LayerDefinition-1.0.0.xsd">';
                    xml += "<VectorLayerDefinition>";
                        xml += "<ResourceId>" + fsId + "</ResourceId>";
                        xml += "<FeatureName>" + className + "</FeatureName>";
                        xml += "<FeatureNameType>FeatureClass</FeatureNameType>";
                        xml += "<Geometry>" + geom + "</Geometry>";
                        xml += "<VectorScaleRange>";
                        xml += "<PointTypeStyle>";
                            xml += "<DisplayAsText>false</DisplayAsText>";
                            xml += "<AllowOverpost>false</AllowOverpost>";
                            xml += "<PointRule>";
                            xml += "<LegendLabel />";
                            xml += "<PointSymbolization2D>";
                                xml += "<Mark>";
                                xml += "<Unit>Points</Unit>";
                                xml += "<SizeContext>DeviceUnits</SizeContext>";
                                xml += "<SizeX>10</SizeX>";
                                xml += "<SizeY>10</SizeY>";
                                xml += "<Rotation>0</Rotation>";
                                xml += "<Shape>Square</Shape>";
                                xml += "<Fill>";
                                    xml += "<FillPattern>Solid</FillPattern>";
                                    xml += "<ForegroundColor>ffffffff</ForegroundColor>";
                                    xml += "<BackgroundColor>ffffffff</BackgroundColor>";
                                xml += "</Fill>";
                                xml += "<Edge>"
                                    xml += "<LineStyle>Solid</LineStyle>";
                                    xml += "<Thickness>1</Thickness>";
                                    xml += "<Color>ff000000</Color>";
                                    xml += "<Unit>Points</Unit>";
                                xml += "</Edge>";
                                xml += "</Mark>";
                            xml += "</PointSymbolization2D>";
                            xml += "</PointRule>";
                        xml += "</PointTypeStyle>";
                        xml += "<LineTypeStyle>";
                            xml += "<LineRule>";
                            xml += "<LegendLabel />";
                            xml += "<LineSymbolization2D>";
                                xml += "<LineStyle>Solid</LineStyle>";
                                xml += "<Thickness>1</Thickness>";
                                xml += "<Color>ff000000</Color>";
                                xml += "<Unit>Points</Unit>";
                            xml += "</LineSymbolization2D>";
                            xml += "</LineRule>";
                        xml += "</LineTypeStyle>";
                        xml += "<AreaTypeStyle>";
                            xml += "<AreaRule>";
                            xml += "<LegendLabel />";
                            xml += "<AreaSymbolization2D>";
                                xml += "<Fill>";
                                xml += "<FillPattern>Solid</FillPattern>";
                                xml += "<ForegroundColor>ffffffff</ForegroundColor>";
                                xml += "<BackgroundColor>ffffffff</BackgroundColor>";
                                xml += "</Fill>";
                                xml += "<Stroke>";
                                xml += "<LineStyle>Solid</LineStyle>";
                                xml += "<Thickness>1</Thickness>";
                                xml += "<Color>ff000000</Color>";
                                xml += "<Unit>Points</Unit>";
                                xml += "</Stroke>";
                            xml += "</AreaSymbolization2D>";
                            xml += "</AreaRule>";
                        xml += "</AreaTypeStyle>";
                        xml += "</VectorScaleRange>";
                    xml += "</VectorLayerDefinition>";
                    xml += "</LayerDefinition>";
                    return xml;
                }
                
                function createModificationXml() {
                    var xml = "<UpdateMap>";
                    xml += "<Operation>";
                    xml += "<Type>RemoveLayer</Type>";
                    xml += "<Name>Trees</Name>";
                    xml += "</Operation>";
                    xml += "<Operation>";
                    xml += "<Type>RemoveGroup</Type>";
                    xml += "<Name>Base Map</Name>";
                    xml += "</Operation>";
                    xml += "</UpdateMap>";
                    return xml;
                }
                
                function createInsertLayerXml(name, ldfId, label, bVisible, bSelectable, bShowInLegend) {
                    var xml = "<UpdateMap>";
                    xml += "<Operation>";
                    xml += "<Type>AddGroup</Type>";
                    xml += "<Name>Session-based Layers</Name>";
                    xml += "<SetExpandInLegend>true</SetExpandInLegend>";
                    xml += "<SetDisplayInLegend>true</SetDisplayInLegend>";
                    xml += "<SetVisible>true</SetVisible>";
                    xml += "<SetLegendLabel>Session Layers</SetLegendLabel>";
                    xml += "</Operation>";
                    xml += "<Operation>";
                    xml += "<Type>AddLayer</Type>";
                    xml += "<Name>" + name + "</Name>";
                    xml += "<ResourceId>" + ldfId + "</ResourceId>";
                    xml += "<SetLegendLabel>" + label + "</SetLegendLabel>";
                    xml += "<SetSelectable>" + bSelectable + "</SetSelectable>";
                    xml += "<SetVisible>" + bVisible + "</SetVisible>";
                    xml += "<SetDisplayInLegend>" + bShowInLegend + "</SetDisplayInLegend>";
                    xml += "<SetGroup>Session-based Layers</SetGroup>";
                    xml += "</Operation>";
                    xml += "</UpdateMap>";
                    return xml;
                }

                api_test(rest_root_url + "/services/createmap.json", "POST", { session: this.anonymousSessionId, requestedfeatures: reqFeatures, mapdefinition: "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result);
                    anonMapName = map.RuntimeMap.Name;
                });
                api_test(rest_root_url + "/services/createmap.json", "POST", { session: this.adminSessionId, requestedfeatures: reqFeatures, mapdefinition: "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result);
                    adminMapName = map.RuntimeMap.Name;
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/layersandgroups.xml", "PUT", createModificationXml(), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/layersandgroups.xml", "PUT", createModificationXml(), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Verify by re-querying layer structure
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bGroupRemoved = true;
                    var bLayerRemoved = true;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Base Map") {
                            bGroupRemoved = false;
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bLayerRemoved = false;
                        }
                    }
                    self.ok(bGroupRemoved, "Expected 'Base Map' group to be removed");
                    self.ok(bLayerRemoved, "Expected 'Trees' layer to be removed");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bGroupRemoved = true;
                    var bLayerRemoved = true;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Base Map") {
                            bGroupRemoved = false;
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bLayerRemoved = false;
                        }
                    }
                    self.ok(bGroupRemoved, "Expected 'Base Map' group to be removed");
                    self.ok(bLayerRemoved, "Expected 'Trees' layer to be removed");
                });
                var fsId = "Library://Samples/Sheboygan/Data/Trees.FeatureSource";
                var cls = "SHP_Schema:Trees";
                var geom = "SHPGEOM";
                //Insert a session-based layer
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Trees.LayerDefinition/content.xml", "POST", createLayerXml(fsId, cls, geom), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expected created status");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Trees.LayerDefinition/content.xml", "POST", createLayerXml(fsId, cls, geom), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expected created status");
                });
                var anonTreesXml = createInsertLayerXml("Trees", "Session:" + this.anonymousSessionId + "//Trees.LayerDefinition", "Trees (Session-based)", true, false, true);
                var adminTreesXml = createInsertLayerXml("Trees", "Session:" + this.adminSessionId + "//Trees.LayerDefinition", "Trees (Session-based)", false, true, false);
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/layersandgroups.xml", "PUT", anonTreesXml, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/layersandgroups.xml", "PUT", adminTreesXml, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                //Verify by re-querying layer structure
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bFoundTrees = false;
                    var bFoundGroup = false;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Session-based Layers") {
                            bFoundGroup = true;
                            self.ok(map.Group[i].Visible == true, "Expected group Visible = true");
                            self.ok(map.Group[i].ExpandInLegend == true, "Expected group ExpandInLegend = true");
                            self.ok(map.Group[i].DisplayInLegend == true, "Expected group DisplayInLegend = true");
                            self.ok(map.Group[i].LegendLabel == "Session Layers", "Expected group label: Session Layers");
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bFoundTrees = true;
                            self.ok(map.Layer[i].Visible == true, "Expected layer Visible = true");
                            self.ok(map.Layer[i].Selectable == false, "Expected layer Selectable = false");
                            self.ok(map.Layer[i].DisplayInLegend == true, "Expected layer DisplayInLegend = true");
                            self.ok(map.Layer[i].LegendLabel == "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
                        }
                    }
                    self.ok(bFoundGroup, "Expected 'Session-based Layers' group to be added");
                    self.ok(bFoundTrees, "Expected 'Trees' layer to be re-added");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bFoundTrees = false;
                    var bFoundGroup = false;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Session-based Layers") {
                            bFoundGroup = true;
                            self.ok(map.Group[i].Visible == true, "Expected group Visible = true");
                            self.ok(map.Group[i].ExpandInLegend == true, "Expected group ExpandInLegend = true");
                            self.ok(map.Group[i].DisplayInLegend == true, "Expected group DisplayInLegend = true");
                            self.ok(map.Group[i].LegendLabel == "Session Layers", "Expected group label: Session Layers");
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bFoundTrees = true;
                            self.ok(map.Layer[i].Visible == false, "Expected layer Visible = false");
                            self.ok(map.Layer[i].Selectable == true, "Expected layer Selectable = true");
                            self.ok(map.Layer[i].DisplayInLegend == false, "Expected layer DisplayInLegend = false");
                            self.ok(map.Layer[i].LegendLabel == "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
                        }
                    }
                    self.ok(bFoundGroup, "Expected 'Session-based Layers' group to be added");
                    self.ok(bFoundTrees, "Expected 'Trees' layer to be re-added");
                });
            });
            test("Layer/Group Modification (JSON)", function() {
                var reqFeatures = (1|2|4);
                var anonMapName = null;
                var adminMapName = null;
                
                function createLayerJson(fsId, className, geom) {
                    return {
                        "LayerDefinition": {
                            "@xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
                            "@version": "1.0.0",
                            "@xsi:noNamespaceSchemaLocation": "LayerDefinition-1.0.0.xsd",
                            "VectorLayerDefinition": {
                                "ResourceId": fsId,
                                "FeatureName": className,
                                "FeatureNameType": "FeatureClass",
                                "Geometry": geom,
                                "VectorScaleRange": [
                                    {
                                        "PointTypeStyle": {
                                            "DisplayAsText": false,
                                            "AllowOverpost": false,
                                            "PointRule": [
                                                {
                                                    "LegendLabel": null,
                                                    "PointSymbolization2D": {
                                                        "Mark": {
                                                            "Unit": "Points",
                                                            "SizeContext": "DeviceUnits",
                                                            "SizeX": "10",
                                                            "SizeY": "10",
                                                            "Rotation": "0",
                                                            "Shape": "Square",
                                                            "Fill": {
                                                                "FillPattern": "Solid",
                                                                "ForegroundColor": "ffffffff",
                                                                "BackgroundColor": "ffffffff"
                                                            },
                                                            "Edge": {
                                                                "LineStyle": "Solid",
                                                                "Thickness": "1",
                                                                "Color": "ff000000",
                                                                "Unit": "Points"
                                                            }
                                                        }
                                                    }
                                                }
                                            ]
                                        },
                                        "LineTypeStyle": {
                                            "LineRule": [
                                                {
                                                    "LegendLabel": null,
                                                    "LineSymbolization2D": [
                                                        {
                                                            "LineStyle": "Solid",
                                                            "Thickness": "1",
                                                            "Color": "ff000000",
                                                            "Unit": "Points"
                                                        }
                                                    ]
                                                }
                                            ]
                                        },
                                        "AreaTypeStyle": {
                                            "AreaRule": [
                                                {
                                                    "LegendLabel": null,
                                                    "AreaSymbolization2D": {
                                                        "Fill": {
                                                            "FillPattern": "Solid",
                                                            "ForegroundColor": "ffffffff",
                                                            "BackgroundColor": "ffffffff"
                                                        },
                                                        "Stroke": {
                                                            "LineStyle": "Solid",
                                                            "Thickness": "1",
                                                            "Color": "ff000000",
                                                            "Unit": "Points"
                                                        }
                                                    }
                                                }
                                            ]
                                        }
                                    }
                                ]
                            }
                        }
                    };
                }
                
                function createModificationJson() {
                    return {
                        "UpdateMap": {
                            "Operation": [
                                {
                                    "Type": "RemoveLayer",
                                    "Name": "Trees"
                                },
                                {
                                    "Type": "RemoveGroup",
                                    "Name": "Base Map"
                                }
                            ]
                        }
                    };
                }
                
                function createInsertLayerJson(name, ldfId, label, bVisible, bSelectable, bShowInLegend) {
                    return {
                        "UpdateMap": {
                            "Operation": [
                                {
                                    "Type": "AddGroup",
                                    "Name": "Session-based Layers",
                                    "SetExpandInLegend": true,
                                    "SetDisplayInLegend": true,
                                    "SetVisible": true,
                                    "SetLegendLabel": "Session Layers"
                                },
                                {
                                    "Type": "AddLayer",
                                    "Name": name,
                                    "ResourceId": ldfId,
                                    "SetLegendLabel": label,
                                    "SetSelectable": bSelectable,
                                    "SetVisible": bVisible,
                                    "SetDisplayInLegend": bShowInLegend,
                                    "SetGroup": "Session-based Layers"
                                }
                            ]
                        }
                    };
                }

                api_test(rest_root_url + "/services/createmap.json", "POST", { session: this.anonymousSessionId, requestedfeatures: reqFeatures, mapdefinition: "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result);
                    anonMapName = map.RuntimeMap.Name;
                });
                api_test(rest_root_url + "/services/createmap.json", "POST", { session: this.adminSessionId, requestedfeatures: reqFeatures, mapdefinition: "Library://Samples/Sheboygan/Maps/Sheboygan.MapDefinition" }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result);
                    adminMapName = map.RuntimeMap.Name;
                });
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/layersandgroups.json", "PUT", JSON.stringify(createModificationJson()), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/layersandgroups.json", "PUT", JSON.stringify(createModificationJson()), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                //Verify by re-querying layer structure
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bGroupRemoved = true;
                    var bLayerRemoved = true;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Base Map") {
                            bGroupRemoved = false;
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bLayerRemoved = false;
                        }
                    }
                    self.ok(bGroupRemoved, "Expected 'Base Map' group to be removed");
                    self.ok(bLayerRemoved, "Expected 'Trees' layer to be removed");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bGroupRemoved = true;
                    var bLayerRemoved = true;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Base Map") {
                            bGroupRemoved = false;
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bLayerRemoved = false;
                        }
                    }
                    self.ok(bGroupRemoved, "Expected 'Base Map' group to be removed");
                    self.ok(bLayerRemoved, "Expected 'Trees' layer to be removed");
                });
                var fsId = "Library://Samples/Sheboygan/Data/Trees.FeatureSource";
                var cls = "SHP_Schema:Trees";
                var geom = "SHPGEOM";
                //Insert a session-based layer
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Trees.LayerDefinition/content.json", "POST", JSON.stringify(createLayerJson(fsId, cls, geom)), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expected created status");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/Trees.LayerDefinition/content.json", "POST", JSON.stringify(createLayerJson(fsId, cls, geom)), function(status, result, mimeType) {
                    self.ok(status == 201, "(" + status + ") - Expected created status");
                });
                var anonTreesJson = createInsertLayerJson("Trees", "Session:" + this.anonymousSessionId + "//Trees.LayerDefinition", "Trees (Session-based)", true, false, true);
                var adminTreesJson = createInsertLayerJson("Trees", "Session:" + this.adminSessionId + "//Trees.LayerDefinition", "Trees (Session-based)", false, true, false);
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/layersandgroups.json", "PUT", JSON.stringify(anonTreesJson), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/layersandgroups.json", "PUT", JSON.stringify(adminTreesJson), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                //Verify by re-querying layer structure
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/" + anonMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bFoundTrees = false;
                    var bFoundGroup = false;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Session-based Layers") {
                            bFoundGroup = true;
                            self.ok(map.Group[i].Visible == true, "Expected group Visible = true");
                            self.ok(map.Group[i].ExpandInLegend == true, "Expected group ExpandInLegend = true");
                            self.ok(map.Group[i].DisplayInLegend == true, "Expected group DisplayInLegend = true");
                            self.ok(map.Group[i].LegendLabel == "Session Layers", "Expected group label: Session Layers");
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bFoundTrees = true;
                            self.ok(map.Layer[i].Visible == true, "Expected layer Visible = true");
                            self.ok(map.Layer[i].Selectable == false, "Expected layer Selectable = false");
                            self.ok(map.Layer[i].DisplayInLegend == true, "Expected layer DisplayInLegend = true");
                            self.ok(map.Layer[i].LegendLabel == "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
                        }
                    }
                    self.ok(bFoundGroup, "Expected 'Session-based Layers' group to be added");
                    self.ok(bFoundTrees, "Expected 'Trees' layer to be re-added");
                });
                api_test(rest_root_url + "/session/" + this.adminSessionId + "/" + adminMapName + ".Map/description.json", "GET", { requestedfeatures: reqFeatures }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected OK status");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    var map = JSON.parse(result).RuntimeMap;
                    var bFoundTrees = false;
                    var bFoundGroup = false;
                    for (var i = 0; i < map.Group.length; i++) {
                        if (map.Group[i].Name == "Session-based Layers") {
                            bFoundGroup = true;
                            self.ok(map.Group[i].Visible == true, "Expected group Visible = true");
                            self.ok(map.Group[i].ExpandInLegend == true, "Expected group ExpandInLegend = true");
                            self.ok(map.Group[i].DisplayInLegend == true, "Expected group DisplayInLegend = true");
                            self.ok(map.Group[i].LegendLabel == "Session Layers", "Expected group label: Session Layers");
                        }
                    }
                    for (var i = 0; i < map.Layer.length; i++) {
                        if (map.Layer[i].Name == "Trees") {
                            bFoundTrees = true;
                            self.ok(map.Layer[i].Visible == false, "Expected layer Visible = false");
                            self.ok(map.Layer[i].Selectable == true, "Expected layer Selectable = true");
                            self.ok(map.Layer[i].DisplayInLegend == false, "Expected layer DisplayInLegend = false");
                            self.ok(map.Layer[i].LegendLabel == "Trees (Session-based)", "Expected layer label: Trees (Session-based)");
                        }
                    }
                    self.ok(bFoundGroup, "Expected 'Session-based Layers' group to be added");
                    self.ok(bFoundTrees, "Expected 'Trees' layer to be re-added");
                });
            });
            module("Plotting - Library", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(this);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("PDF Plot", function() {
                var self = this;
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
            });
            test("Layered PDF Plot", function() {
                var self = this;
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.pdf", "GET", { x: -87.73, y: 43.74, scale: 8000, layeredpdf: 1, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected pdf response");
                    self.ok(mimeType.indexOf(MgMimeType.Pdf) >= 0, "(" + mimeType + ") expected PDF mime type");
                });
            });
            test("DWF Plot", function() {
                var self = this;
                //Various missing parameters
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected missing parameter response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected dwf response");
                    self.ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000 }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected dwf response");
                    self.ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected dwf response");
                    self.ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
                api_test(rest_root_url + "/library/Samples/Sheboygan/Maps/Sheboygan.MapDefinition/plot.dwf", "GET", { x: -87.73, y: 43.74, scale: 8000, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Expected dwf response");
                    self.ok(mimeType.indexOf(MgMimeType.Dwf) >= 0, "(" + mimeType + ") expected DWF mime type");
                });
            });

            module("Tile Service", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });
                }
            });
            test("GetTile", function() {
                var self = this;
                //With raw credentials
                api_test(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_anon(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_admin(rest_root_url + "/library/Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
            });
            test("GetTile - session copy", function() {
                var self = this;
                api_test(rest_root_url + "/services/copyresource", "POST", {
                    session: this.anonymousSessionId,
                    source: "Library://Samples/Sheboygan/MapsTiled/Sheboygan.MapDefinition",
                    destination: "Session:" + this.anonymousSessionId + "//Sheboygan.MapDefinition",
                    overwrite: 1
                }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") copy operation should've succeeded");
                });
                //With raw credentials
                api_test(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_anon(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
                api_test_admin(rest_root_url + "/session/" + this.anonymousSessionId + "/Sheboygan.MapDefinition/tile.img/Base Layer Group/6/1/0", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Should've got a tile at 6,1,0");
                });
            });

            module("Coordinate System", {
                setup: function() {
                    var self = this;
                    prepareEnvironment(self);
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "Anonymous", "", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.anonymousSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                    api_test_with_credentials(rest_root_url + "/session.json", "POST", {}, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                        self.ok(status != 401, "(" + status+ ") - Request should've been authenticated");
                        self.adminSessionId = JSON.parse(result).PrimitiveValue.Value;
                    });
                },
                teardown: function() {
                    api_test(rest_root_url + "/session/" + this.anonymousSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected anonymous session to be destroyed");
                        delete this.anonymousSessionId;
                    });

                    api_test(rest_root_url + "/session/" + this.adminSessionId, "DELETE", null, function(status, result, mimeType) {
                        self.ok(status == 200, "(" + status + ") - Expected admin session to be destroyed");
                        delete this.adminSessionId;
                    });
                }
            });
            test("Transform batch coordinates", function() {
                var self = this;
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

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected server error. Bogus target coord sys.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected server error. Bogus target coord sys.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error. Bogus target coord sys.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsBogusCs, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error. Bogus target coord sys.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete1, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status+ ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsIncomplete2, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad response. Incomplete params specified.");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", params, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", params, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", params, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", params, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsWithPadding, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", paramsBadCoords, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsWithPadding, { format: "json" }), function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status+ ") - Expected success with list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), "Anonymous", "", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_with_credentials(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), "<?= $adminUser ?>", "<?= $adminPass ?>", function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_anon(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/services/transformcoords", "POST", $.extend(paramsBadCoords, { format: "json" }), function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status+ ") - Expected success with partial list of transformed coordinates");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Get Base Library", function() {
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/baselibrary.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/baselibrary.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/baselibrary.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/baselibrary.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/baselibrary.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/baselibrary.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/baselibrary.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/baselibrary.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Validate WKT", function() {
                var GOOD_WKT = 'GEOGCS["LL84",DATUM["WGS84",SPHEROID["WGS84",6378137.000,298.25722293]],PRIMEM["Greenwich",0],UNIT["Degree",0.01745329251994]]';
                var BAD_WKT = 'This is not a valid coordinate system wkt';

                // --------------------------------- GOOD WKT ----------------------------------- //
            
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value, "Expected valid = true");
                });
                api_test_admin(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value, "Expected valid = true");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value, "Expected valid = true");
                });
                api_test(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value, "Expected valid = true");
                });
                
                // --------------------------------- BAD WKT ----------------------------------- //
                
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/validatewkt.xml", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == false, "Expected valid = false");
                });
                api_test_admin(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == false, "Expected valid = false");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == false, "Expected valid = false");
                });
                api_test(rest_root_url + "/coordsys/validatewkt.json", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == false, "Expected valid = false");
                });
            });
            test("WKT to Mentor", function() {
                var GOOD_WKT = 'GEOGCS["LL84",DATUM["WGS84",SPHEROID["WGS84",6378137.000,298.25722293]],PRIMEM["Greenwich",0],UNIT["Degree",0.01745329251994]]';
                var BAD_WKT = 'This is not a valid coordinate system wkt';

                // --------------------------------- GOOD WKT ----------------------------------- //
            
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == "LL84", "Expected LL84");
                });
                api_test_admin(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == "LL84", "Expected LL84");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == "LL84", "Expected LL84");
                });
                api_test(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == "LL84", "Expected LL84");
                });
                
                // --------------------------------- BAD WKT ----------------------------------- //
                
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/wkttomentor.xml", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/wkttomentor.json", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("WKT to EPSG", function() {
                var GOOD_WKT = 'GEOGCS["LL84",DATUM["WGS84",SPHEROID["WGS84",6378137.000,298.25722293]],PRIMEM["Greenwich",0],UNIT["Degree",0.01745329251994]]';
                var BAD_WKT = 'This is not a valid coordinate system wkt';

                // --------------------------------- GOOD WKT ----------------------------------- //
            
                var self = this;
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == 4326, "Expected 4326");
                });
                api_test_admin(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: GOOD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == 4326, "Expected 4326");
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: GOOD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == 4326, "Expected 4326");
                });
                api_test(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: GOOD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                    self.ok(JSON.parse(result).PrimitiveValue.Value == 4326, "Expected 4326");
                });
                
                // --------------------------------- BAD WKT ----------------------------------- //
                
                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/wkttoepsg.xml", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: BAD_WKT }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: BAD_WKT, session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/wkttoepsg.json", "POST", { wkt: BAD_WKT, session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 500, "(" + status + ") - Expected server error");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Enum categories", function() {
                var self = this;
                api_test(rest_root_url + "/coordsys/categories.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/coordsys/categories.sadgdsfd", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/coordsys/categories.sadgdsfd", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/categories.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/categories.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/categories.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/categories.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/categories.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/coordsys/categories.html", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/categories.html", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/coordsys/categories.html", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("Enum categories - Australia", function() {
                var self = this;
                api_test(rest_root_url + "/coordsys/category.xml/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                api_test_anon(rest_root_url + "/coordsys/category.sdgfd/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/coordsys/category.sdgfd/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 400, "(" + status + ") - Expected bad representation response");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.xml/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/category.xml/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.xml/Australia", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/category.xml/Australia", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(result.indexOf(XML_PROLOG) == 0, "Expected XML prolog in XML response");
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.json/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/category.json/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.json/Australia", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/category.json/Australia", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/category.html/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test_admin(rest_root_url + "/coordsys/category.html/Australia", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/category.html/Australia", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
                api_test(rest_root_url + "/coordsys/category.html/Australia", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.assertMimeType(mimeType, MgMimeType.Html);
                });
            });
            test("EPSG for LL84", function() {
                var self = this;
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg.xml", "GET", null, function(status, result, mimeType) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/epsg.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf('4326') >= 0, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/epsg.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf('4326') >= 0, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/epsg.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == 4326, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/epsg.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == 4326, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf('4326') >= 0, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf('4326') >= 0, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == 4326, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/epsg.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == 4326, "Expected EPSG of 4326. Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("WKT for LL84", function() {
                var self = this;
                var expect = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt.xml", "GET", null, function(status, result, mimeType) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/wkt.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/wkt.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/coordsys/mentor/LL84/wkt.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/mentor/LL84/wkt.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/mentor/LL84/wkt.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("Mentor code for EPSG:4326", function() {
                var self = this;
                var expect = "LL84";
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor.xml", "GET", null, function(status, result, mimeType) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/mentor.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(expect) >= 0, "Expected code of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/mentor.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(expect) >= 0, "Expected code of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/mentor.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected code of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/mentor.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected code of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(expect) >= 0, "Expected code of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(expect) >= 0, "Expected code of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected code of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/mentor.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected code of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            test("WKT for EPSG:4326", function() {
                var self = this;
                var expect = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt.xml", "GET", null, function(status, result, mimeType) {
                    //ok(status == 401, "(" + status + ") - Request should've required authentication");
                    self.ok(status == 200, "(" + status + ") - Response shouldn't require authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/wkt.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/wkt.xml", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test_anon(rest_root_url + "/coordsys/epsg/4326/wkt.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test_admin(rest_root_url + "/coordsys/epsg/4326/wkt.json", "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt.xml", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt.xml", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected WKT of " + expect + ". Got: " + result);
                    self.assertMimeType(mimeType, MgMimeType.Xml);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt.json", "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
                api_test(rest_root_url + "/coordsys/epsg/4326/wkt.json", "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    var value = JSON.parse(result).PrimitiveValue.Value;
                    self.ok(value == expect, "Expected WKT of " + expect + ". Got: " + value);
                    self.assertMimeType(mimeType, MgMimeType.Json);
                });
            });
            /*
            test("WKT to mentor", function() {
                var self = this;
                var wkt = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                var expect = "LL84";
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected code of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected code of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected code of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/tomentor/" + wkt, "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected code of " + expect + ". Got: " + result);
                });
            });
            test("WKT to epsg", function() {
                var self = this;
                var wkt = "GEOGCS[\"LL84\",DATUM[\"WGS84\",SPHEROID[\"WGS84\",6378137.000,298.25722293]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.01745329251994]]";
                var expect = "4326";
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 401, "(" + status + ") - Request should've required authentication");
                });

                //With raw credentials
                api_test_anon(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected EPSG of " + expect + ". Got: " + result);
                });
                api_test_admin(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", null, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected EPSG of " + expect + ". Got: " + result);
                });

                //With session id
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", { session: this.anonymousSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected EPSG of " + expect + ". Got: " + result);
                });
                api_test(rest_root_url + "/coordsys/toepsg/" + wkt, "GET", { session: this.adminSessionId }, function(status, result, mimeType) {
                    self.ok(status == 200, "(" + status + ") - Response should've been ok");
                    self.ok(result.indexOf(encodeHTML(expect)) >= 0, "Expected EPSG of " + expect + ". Got: " + result);
                });
            });
            */