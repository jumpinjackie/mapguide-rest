/* ======================================================================
    OpenLayers/Layer/MapGuideREST.js
   ====================================================================== */

/* Copyright (c) 2013 by Jackie Ng. Published under the 2-clause BSD license.
 * See license.txt in the OpenLayers distribution or repository for the
 * full text of the license. */

/**
 * @requires OpenLayers/Request/XMLHttpRequest.js
 * @requires OpenLayers/Layer/Grid.js
 */

/**
 * Class: OpenLayers.Layer.MapGuideREST
 * Instances of OpenLayers.Layer.MapGuideREST are used to display
 * data from a MapGuide OS instance via the REST API
 *
 * Inherits from:
 *  - <OpenLayers.Layer.Grid>
 */
OpenLayers.Layer.MapGuideREST = OpenLayers.Class(OpenLayers.Layer.Grid, {

    /** 
     * APIProperty: isBaseLayer
     * {Boolean} Treat this layer as a base layer.  Default is true.
     **/
    isBaseLayer: true,
    
    /** 
     * APIProperty: singleTile
     * {Boolean} use tile server or request single tile image. 
     **/
    singleTile: false,
    
    /** 
     * APIProperty: useOverlay
     * {Boolean} flag to indicate if the layer should be retrieved using
     * GETMAPIMAGE (default) or using GETDYNAMICOVERLAY requests.
     **/
    useOverlay: false,
    
    /** 
     * Property: defaultSize
     * {<OpenLayers.Size>} Tile size as produced by MapGuide server
     **/
    defaultSize: new OpenLayers.Size(300,300),

    /** 
     * Property: tileOriginCorner
     * {String} MapGuide tile server uses top-left as tile origin
     **/
    tileOriginCorner: "tl",

    tileUrl: "${rest}/library/${resourcepath}/tile.img/${group}/${scale}/${row}/${col}",
    overlayUrl: "${rest}/session/${session}/${mapname}.Map/overlayimage.${imgformat}?width=${width}&height=${height}&x=${x}&y=${y}&scale=${scale}&dpi=${dpi}&behavior=${behavior}",

    /**
     * Constructor: OpenLayers.Layer.MapGuideREST
     * Create a new Mapguide layer, either tiled or untiled.  
     *
     * NOTE: MapGuide OS uses a DPI value and degrees to meters conversion 
     * factor that are different than the defaults used in OpenLayers, 
     * so these must be adjusted accordingly in your application.  
     * See the MapGuide example for how to set these values for MGOS.
     *
     * Parameters:
     * name - {String} Name of the layer displayed in the interface
     * url - {String} Location of the MapGuide REST endpoint
     *            (e.g. http://localhost/mapguide/rest)
     * options - {Object} Hashtable of extra options to tag onto the layer; 
     *          will vary depending if tiled or untiled maps are being requested
     */
    initialize: function(name, url, options) {
        
        this._initParent(name, url, {}, options);
        this.restRootPath = url;
        
        // unless explicitly set in options, if the layer is transparent, 
        // it will be an overlay
        if (options == null || options.isBaseLayer == null) {
            this.isBaseLayer = ((this.transparent != "true") && 
                                (this.transparent != true));
        }

        //initialize for untiled layers
        if (!this.singleTile) {
            this.setTileSize(this.defaultSize);
        }
        if (this.mapdefinition && this.mapdefinition.indexOf("Library://") >= 0) {
            this.resourcePath = this.mapdefinition.substring("Library://".length);
        }
        if (typeof(this.imgformat) == 'undefined')
            this.imgformat = "png";
    },

    _initParent: function(name, url, params, options) {
        OpenLayers.Layer.Grid.prototype.initialize.apply(this, arguments);
    },

    /**
     * Method: clone
     * Create a clone of this layer
     *
     * Returns:
     * {<OpenLayers.Layer.MapGuide>} An exact clone of this layer
     */
    clone: function (obj) {
        if (obj == null) {
            obj = new OpenLayers.Layer.MapGuide(this.name,
                                                this.url,
                                                this.params,
                                                this.getOptions());
        }
        //get all additions from superclasses
        obj = OpenLayers.Layer.Grid.prototype.clone.apply(this, [obj]);
        return obj;
    },

    /**
     * Method: getURL
     * Return a query string for this layer
     *
     * Parameters:
     * bounds - {<OpenLayers.Bounds>} A bounds representing the bbox 
     *                                for the request
     *
     * Returns:
     * {String} A string with the layer's url and parameters and also 
     *          the passed-in bounds and appropriate tile size specified 
     *          as parameters.
     */
    getURL: function (bounds) {
        var url;
        var center = bounds.getCenterLonLat();
        var mapSize = this.map.getSize();

        if (this.singleTile) {

            var params = {
                rest: this.restRootPath,
                session: this.session,
                mapname: this.mapname,
                imgformat: this.imgformat,
                behavior: this.behavior,
                dpi: OpenLayers.DOTS_PER_INCH,
                width: mapSize.w * this.ratio,
                height: mapSize.h * this.ratio,
                x: center.lon,
                y: center.lat,
                scale: this.map.getScale()
            };

            params.selectioncolor = this.selectioncolor;

            var url = OpenLayers.String.format(this.overlayUrl, params);
            if (typeof(this.selectioncolor) != 'undefined')
                url += "&selectioncolor=" + this.selectioncolor;

            //Needed for force redraw to work
            if (typeof(this.params._olSalt) != 'undefined')
                url += "&seq=" + this.params._olSalt;

            if (typeof(this.params.hidegroups) != 'undefined' && this.params.hidegroups != null)
                url += "&hidegroups=" + this.params.hidegroups;
            if (typeof(this.params.showgroups) != 'undefined' && this.params.showgroups != null)
                url += "&showgroups=" + this.params.showgroups;
            if (typeof(this.params.hidelayers) != 'undefined' && this.params.hidelayers != null)
                url += "&hidelayers=" + this.params.hidelayers;
            if (typeof(this.params.showlayers) != 'undefined' && this.params.showlayers != null)
                url += "&showlayers=" + this.params.showlayers;

            return url;
        } else {

            //tiled version
            var currentRes = this.map.getResolution();
            var colidx = Math.floor((bounds.left-this.maxExtent.left)/currentRes);
            colidx = Math.round(colidx/this.tileSize.w);
            var rowidx = Math.floor((this.maxExtent.top-bounds.top)/currentRes);
            rowidx = Math.round(rowidx/this.tileSize.h);

            var params = {
                rest: this.restRootPath,
                resourcepath: this.resourcePath,
                group: this.basemaplayergroupname,
                row: rowidx,
                col: colidx,
                scale: this.resolutions.length - this.map.zoom - 1
            };

            return OpenLayers.String.format(this.tileUrl, params);
        }
    },
    
    CLASS_NAME: "OpenLayers.Layer.MapGuideREST"
});