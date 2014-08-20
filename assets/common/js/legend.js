//A simple legend to toggle MapGuide Layer visbility for a OpenLayers.Layer.MapGuide instance
//
//NOTE: Only tested with the Sheboygan dataset. Probably doesn't handle all corner cases that can be possible with a MapGuide Map Definition

//Requires: OpenLayers, jQuery

function Legend(options)
{
    var rtMapInfo = options.runtimeMap;
    var legendSelector = options.legendSelector;
    this.map = options.map;
    this.mgLayer = options.mgLayerOL;
    this.mgTiledLayers = options.mgTiledLayers || {};
    this.debug = false;

    this.iconMimeType = rtMapInfo.RuntimeMap.IconMimeType[0];
    
    this.stdIconRoot = options.stdIconRoot || "../../stdicons";
    this.rootEl = $(legendSelector);
    
    var _self = this;
    
    this.map.events.register("zoomend", this, OpenLayers.Function.bind(this.update, this));
    
    var groupElMap = {};
    if (rtMapInfo.RuntimeMap.Group) {
        var remainingGroups = {};
        //1st pass, un-parented groups
        for (var i = 0; i < rtMapInfo.RuntimeMap.Group.length; i++) {
            var group = rtMapInfo.RuntimeMap.Group[i];
            if (group.ParentId) {
                remainingGroups[group.ObjectId[0]] = group;
                continue;
            }
            var el = this.createGroupElement(group);
            groupElMap[group.ObjectId[0]] = el;
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
                if (typeof(groupElMap[group.ParentId[0]]) != 'undefined') {
                    var el = this.createGroupElement(group);
                    groupElMap[group.ParentId[0]].find("ul.groupChildren").append(el);
                    removeIds.push(group.ObjectId[0]);
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
                    groupElMap[layer.ParentId[0]].find("ul.groupChildren").append(els[j]);
                } else {
                    this.rootEl.append(els[j]);
                }
            }
        }
    }
    
    $("input.group-checkbox", this.rootEl).change(function() {
        var el = $(this);
        var bShow = el.is(":checked");
        if (el.attr("data-is-tiled") == "true") {
            var name = el.attr("data-group-name");
            if (typeof(_self.mgTiledLayers[name]) != 'undefined') {
                _self.mgTiledLayers[name].setVisibility(bShow);
            }
        } else {
            var objId = el.val();
            _self.showGroup(objId, bShow);
        }
    });
    
    $("input.layer-checkbox", this.rootEl).change(function() {
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

Legend.prototype.resetRequest = function() {
    this.req.showgroups = null;
    this.req.showlayers = null;
    this.req.hidegroups = null;
    this.req.hidelayers = null;
};

Legend.prototype.showGroup = function(groupId, bShow) {
    this.resetRequest();
    if (bShow)
        this.req.showgroups = groupId;
    else
        this.req.hidegroups = groupId;
    this.mgLayer.mergeNewParams(this.req);
};

Legend.prototype.showLayer = function(layerId, bShow) {
    this.resetRequest();
    if (bShow)
        this.req.showlayers = layerId;
    else
        this.req.hidelayers = layerId;
    this.mgLayer.mergeNewParams(this.req);
};

Legend.prototype.update = function() {
    var scale = this.map.getScale();
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
    return $("<li><input type='checkbox' class='group-checkbox' data-is-tiled='" + (group.Type[0] == 2) + "' data-group-name='" + group.Name[0] + "' value='" + group.ObjectId[0] + "' " + ((group.Visible[0] == "true") ? "checked='checked'" : "") + " /><img src='" + this.stdIconRoot + "/lc_group.gif' /> " + group.LegendLabel[0] + "<ul class='groupChildren'></ul></li>");
};

Legend.prototype.getIconUri = function(iconBase64) {
    return "data:" + this.iconMimeType + ";base64," + iconBase64;
};

Legend.prototype.createLayerElements = function(layer) {
    var icon = "legend-layer.png";
    var label = layer.LegendLabel ? layer.LegendLabel[0] : "";
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
                    text = label + " (" + scaleRange.MinScale[0] + " - " + scaleRange.MaxScale[0] + ")";
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
                        childHtml += "<li><img src='" + this.getIconUri(fts.Rule[0].Icon[0]) + "' /> " + (fts.Rule[0].LegendLabel ? fts.Rule[0].LegendLabel[0] : "") + "</li>";
                        childHtml += "<li>... (" + (ruleCount - 2) + " other theme rules)</li>";
                        childHtml += "<li><img src='" + this.getIconUri(fts.Rule[ruleCount-1].Icon[0]) + "' /> " + (fts.Rule[ruleCount-1].LegendLabel ? fts.Rule[ruleCount-1].LegendLabel[0] : "") + "</li>";
                    } else {
                        for (var i = 0; i < ruleCount; i++) {
                            var rule = fts.Rule[i];
                            childHtml += "<li><img src='" + this.getIconUri(rule.Icon[0]) + "' /> " + (rule.LegendLabel ? rule.LegendLabel[0] : "") + "</li>";
                        }
                    }
                    childHtml += "</ul>";
                } else {
                    icon = this.getIconUri(fts.Rule[0].Icon[0]);
                }
                var chkBoxHtml = "";
                if (layer.Type[0] == 1) //Dynamic
                    chkBoxHtml = "<input type='checkbox' class='layer-checkbox' value='" + layer.ObjectId[0] + "' " + ((layer.Visible[0] == "true") ? "checked='checked'" : "") + " />";
                els.push($("<li class='layer-node' data-layer-min-scale='" + scaleRange.MinScale[0] + "' data-layer-max-scale='" + scaleRange.MaxScale[0] + "'>" + chkBoxHtml + "<img src='" + icon + "' /> " + text + childHtml + "</li>"));
            }
        }
    }
    return els;
};