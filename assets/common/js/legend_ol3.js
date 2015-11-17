//A simple legend to toggle MapGuide Layer visbility for a OpenLayers.Layer.MapGuide instance
//
//NOTE: Only tested with the Sheboygan dataset. Probably doesn't handle all corner cases that can be possible with a MapGuide Map Definition
//NOTE: This uses the "clean" mapguide-rest JSON APIs which does not blindly array-ify every JSON property
//Requires: OpenLayers, jQuery

function Legend(options)
{
    var legendSelector = options.legendSelector;
    this.map = options.map;
    this.mgLayer = options.mgLayerOL;
    this.mgTiledLayers = options.mgTiledLayers || {};
    this.debug = false;

    var rtMapInfo = options.runtimeMap;
    this.dpi = rtMapInfo.RuntimeMap.DisplayDpi;
    this.inPerUnit = rtMapInfo.RuntimeMap.CoordinateSystem.MetersPerUnit * 39.37;

    this.iconMimeType = rtMapInfo.RuntimeMap.IconMimeType;
    
    this.stdIconRoot = options.stdIconRoot || "../../stdicons";
    this.rootEl = $(legendSelector);
    
    var _self = this;
    
    this.map.getView().on("change:resolution", this.update, this);
    
    this.updateLayersAndGroups(rtMapInfo);
    
    //$("input.group-checkbox", this.rootEl).change(function() {
    this.rootEl.on("change", "input.group-checkbox", function() {
        var el = $(this);
        var bShow = el.is(":checked");
        if (el.attr("data-is-tiled") == "true") {
            var name = el.attr("data-group-name");
            if (typeof(_self.mgTiledLayers[name]) != 'undefined') {
                _self.mgTiledLayers[name].setVisible(bShow);
            }
        } else {
            var objId = el.val();
            _self.showGroup(objId, bShow);
        }
    });
    
    //$("input.layer-checkbox", this.rootEl).change(function() {
    this.rootEl.on("change", "input.layer-checkbox", function() {
        var el = $(this);
        var bShow = el.is(":checked");
        var objId = el.val();
        _self.showLayer(objId, bShow);
    });
    
    this.req = {
        showgroups: null,
        showlayers: null,
        hidegroups: null,
        hidelayers: null
    };
}

Legend.prototype.updateLayersAndGroups = function(rtMapInfo) {
    this.mapInfo = rtMapInfo;
    var groupElMap = {};
    this.rootEl.empty();
    if (rtMapInfo.RuntimeMap.Group) {
        var remainingGroups = {};
        //1st pass, un-parented groups
        for (var i = 0; i < rtMapInfo.RuntimeMap.Group.length; i++) {
            var group = rtMapInfo.RuntimeMap.Group[i];
            if (group.ParentId) {
                remainingGroups[group.ObjectId] = group;
                continue;
            }
            var el = this.createGroupElement(group);
            groupElMap[group.ObjectId] = el;
            this.rootEl.append(el);
        }
        //2nd pass, parented groups
        var itemCount = 0;
        for (var objId in remainingGroups) {
            itemCount++;
        }
        //Whittle down
        while(itemCount > 0) {
            var removeIds = [];
            for (var objId in remainingGroups) {
                var group = remainingGroups[objId];
                //Do we have a parent?
                if (typeof(groupElMap[group.ParentId]) != 'undefined') {
                    var el = this.createGroupElement(group);
                    groupElMap[group.ObjectId] = el;
                    groupElMap[group.ParentId].find("ul.groupChildren").append(el);
                    removeIds.push(group.ObjectId);
                }
            }
            for (var i = 0; i < removeIds.length; i++) {
                delete remainingGroups[removeIds[i]];
            }
        
            itemCount = 0;
            for (var objId in remainingGroups) {
                itemCount++;
            }
        }
    }
    if (rtMapInfo.RuntimeMap.Layer) {
        for (var i = 0; i < rtMapInfo.RuntimeMap.Layer.length; i++) {
            var layer = rtMapInfo.RuntimeMap.Layer[i];
            var els = this.createLayerElements(layer);
            for (var j = 0; j < els.length; j++) {
                if (layer.ParentId) {
                    groupElMap[layer.ParentId].find("ul.groupChildren").append(els[j]);
                } else {
                    this.rootEl.append(els[j]);
                }
            }
        }
    }
};

Legend.prototype.resetRequest = function() {
    this.req.showgroups = null;
    this.req.showlayers = null;
    this.req.hidegroups = null;
    this.req.hidelayers = null;
};

Legend.prototype.refreshLayer = function() {
    this.mgLayer.getSource().updateParams({ seq: (new Date()).getTime() });
};

Legend.prototype.updateMgLayer_ = function() {
    //this.mgLayer.mergeNewParams(this.req);
    this.mgLayer.getSource().updateParams(this.req);
};

Legend.prototype.showGroup = function(groupId, bShow) {
    this.resetRequest();
    if (bShow)
        this.req.showgroups = groupId;
    else
        this.req.hidegroups = groupId;
    this.updateMgLayer_();
};

Legend.prototype.showLayer = function(layerId, bShow) {
    this.resetRequest();
    if (bShow)
        this.req.showlayers = layerId;
    else
        this.req.hidelayers = layerId;
    this.updateMgLayer_();
};

Legend.prototype.getScale = function() {
    return this.map.getView().getResolution() * this.dpi * this.inPerUnit;
};

Legend.prototype.update = function() {
    var scale = this.getScale();
    var nodes = $("li.layer-node");
    nodes.each(function(i, e) {
        var el = $(e);
        var min = el.attr("data-layer-min-scale");
        var max = el.attr("data-layer-max-scale");
        if ((scale >= min && scale < max) || (scale >= min && max==="infinity")) {
            if (el.is(":hidden"))
                el.show();
        } else {
            if (el.is(":visible"))
                el.hide();
        }
    });
};

Legend.prototype.createGroupElement = function(group) {
    return $("<li><input type='checkbox' class='group-checkbox' data-is-tiled='" + (group.Type == 2 || group.Type == 3) + "' data-group-name='" + group.Name + "' value='" + group.ObjectId + "' " + ((group.Visible) ? "checked='checked'" : "") + " /><img src='" + this.stdIconRoot + "/lc_group.gif' /> " + group.LegendLabel + "<ul class='groupChildren'></ul></li>");
};

Legend.prototype.getIconUri = function(iconBase64) {
    return "data:" + this.iconMimeType + ";base64," + iconBase64;
};

Legend.prototype.createLayerElements = function(layer) {
    var icon = "legend-layer.png";
    var label = layer.LegendLabel ? layer.LegendLabel : "";
    var text = label;
    var childHtml = "";
    //This is using the first scale range and the first geometry type. To do this proper you'd find the matching scale range
    //based on the current map's view scale. Then dynamically, toggle item visibility when the map scale
    //changes
    var els = [];
    if (layer.ScaleRange) {
        for (var i = 0; i < layer.ScaleRange.length; i++) {
            var scaleRange = layer.ScaleRange[i];
            if (scaleRange.FeatureStyle) {
                if (this.debug)
                    text = label + " (" + scaleRange.MinScale + " - " + scaleRange.MaxScale + ")";
                var fts = scaleRange.FeatureStyle[0];
                var ruleCount = fts.Rule.length;
                if (ruleCount > 1) {
                    icon = this.stdIconRoot + "/lc_theme.gif";
                    childHtml = "<ul>";
                    //Test compression
                    var bCompressed = false;
                    if (ruleCount > 3) {
                        bCompressed = !(fts.Rule[1].Icon);
                    }
                    if (bCompressed) {
                        childHtml += "<li><img src='" + this.getIconUri(fts.Rule.Icon) + "' /> " + (fts.Rule.LegendLabel ? fts.Rule.LegendLabel : "") + "</li>";
                        childHtml += "<li>... (" + (ruleCount - 2) + " other theme rules)</li>";
                        childHtml += "<li><img src='" + this.getIconUri(fts.Rule[ruleCount-1].Icon) + "' /> " + (fts.Rule[ruleCount-1].LegendLabel ? fts.Rule[ruleCount-1].LegendLabel : "") + "</li>";
                    } else {
                        for (var i = 0; i < ruleCount; i++) {
                            var rule = fts.Rule[i];
                            childHtml += "<li><img src='" + this.getIconUri(rule.Icon) + "' /> " + (rule.LegendLabel ? rule.LegendLabel : "") + "</li>";
                        }
                    }
                    childHtml += "</ul>";
                } else {
                    icon = this.getIconUri(fts.Rule[0].Icon);
                }
                var chkBoxHtml = "";
                if (layer.Type == 1) //Dynamic
                    chkBoxHtml = "<input type='checkbox' class='layer-checkbox' value='" + layer.ObjectId + "' " + ((layer.Visible == true) ? "checked='checked'" : "") + " />";
                els.push($("<li class='layer-node' data-layer-name='" + layer.Name + "' data-layer-selectable='" + layer.Selectable + "' data-layer-min-scale='" + scaleRange.MinScale + "' data-layer-max-scale='" + scaleRange.MaxScale + "'>" + chkBoxHtml + "<img src='" + icon + "' /> " + text + childHtml + "</li>"));
            }
        }
    }
    return els;
};

Legend.prototype.getSelectedLayerNames = function() {
    var names = [];
    $("li.layer-node:visible").each(function(i, e) {
        var el = $(e);
        if (el.data("layer-selectable") == true)
            names.push(el.data("layer-name"));
    });
    return names;
};