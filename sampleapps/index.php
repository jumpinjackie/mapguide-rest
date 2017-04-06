<?php

function is_installed($feature)
{
    return file_exists(dirname(__FILE__)."/".$feature);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>MapGuide REST Samples Landing Page</title>
        <link rel="stylesheet" href="../assets/common/css/bootstrap.min.css" />
        <script type="text/javascript" src="../assets/common/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="../assets/common/js/bootstrap.min.js"></script>
        <style type="text/css">
            /* Move down content because we have a fixed navbar that is 50px tall */
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
          <div class="container">
            <div class="navbar-header">
              <a class="navbar-brand" href="index.php">MapGuide REST Samples</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              
            </div><!--/.navbar-collapse -->
          </div>
        </nav>
        <div class="jumbotron">
            <div class="container">
                <h1>MapGuide REST Samples</h1>
                <p>Here you will find an assorted list of samples using the MapGuide REST API and its data publishing framework</p>
                <p>Click on a link below to go to that particular sample</p>
            </div>
        </div>
        <div class="container">
            <div class="alert alert-info">
                <strong>NOTE</strong>
                <p>You should download the <a href="http://download.osgeo.org/mapguide/releases/2.0.0/samples/Sheboygan.mgp">Sheboygan Dataset</a> and load this in via the <a href="mapadmin/login.php">MapGuide Site Administrator</a> before running any of these samples</p>
                <p>Some samples require the <a href="https://github.com/jumpinjackie/mapguide-sample-melbourne/releases">Melbourne dataset</a></p>
                <p>Some of these samples require some sample resources and data to be loaded. <a href="data/load.php">Click here</a> to load these resources</p>
            </div>
        </div>
        <div class="container">
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplePublishedData"><strong>Published Data examples</strong></a>
                    </div>
                    <div id="samplePublishedData" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="alert alert-info">All examples below are configured to return 500 results maximum, at 100 results per page</div>
                            <ul class="list-group">
                                <li class="list-group-item"><a href="../data/property/.html">HTML Property Example</a> <a href="../data/property/.html?page=2">Page 2</a> <a href="../data/property/.html?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/property/.kml">KML Property Example</a> <a href="../data/property/.kml?page=2">Page 2</a> <a href="../data/property/.kml?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/property/.png">Map Image Property Example</a> <a href="../data/property/.png?page=2">Page 2</a> <a href="../data/property/.png?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/property/.georss">GeoRSS Property Example</a> <a href="../data/property/.georss?page=2">Page 2</a> <a href="../data/property/.georss?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/property/.atom">Atom Property Example</a> <a href="../data/property/.atom?page=2">Page 2</a> <a href="../data/property/.atom?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/property/.csv">CSV Property Example</a> <a href="../data/property/.csv?page=2">Page 2</a> <a href="../data/property/.csv?page=2">Page 3</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplePublishedDataWithFilters"><strong>Published Data examples with filters</strong></a>
                    </div>
                    <div id="samplePublishedDataWithFilters" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="alert alert-info">All examples below are configured to return 500 results maximum, at 100 results per page</div>
                            <ul class="list-group">
                                <li class="list-group-item"><a href="../data/property/.html?filter=RNAME%20LIKE%20%27SCHMITT%25%27">HTML Property Example - Owners named SCHMITT</a></li>
                                <li class="list-group-item"><a href="../data/property/.kml?filter=RNAME%20LIKE%20%27SCHMITT%25%27">KML Property Example - Owners named SCHMITT</a></li>
                                <li class="list-group-item"><a href="../data/property/.png?filter=RNAME%20LIKE%20%27SCHMITT%25%27">Map Image Property Example - Owners named SCHMITT</a></li>
                                <li class="list-group-item"><a href="../data/property/.html?bbox=-87.6,43.7,-87.7,43.8">HTML Property Example - Properties intersecting (-87.6,43.7,-87.7,43.8)</a></li>
                                <li class="list-group-item"><a href="../data/property/.kml?bbox=-87.6,43.7,-87.7,43.8">KML Property Example - Properties intersecting (-87.6,43.7,-87.7,43.8)</a></li>
                                <li class="list-group-item"><a href="../data/property/.png?bbox=-87.6,43.7,-87.7,43.8">Map Image Property Example - Properties intersecting (-87.6,43.7,-87.7,43.8)</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#sampleEditableData"><strong>Editable published data</strong></a>
                    </div>
                    <div id="sampleEditableData" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="alert alert-info">You must login as Author (default pwd: author) for property insert/update/delete operations to work</div>
                            <ul class="list-group">
                                <li class="list-group-item"><a href="../../mapviewerajax/?WEBLAYOUT=Library://Samples/Sheboygan/Layouts/SheboyganREST.WebLayout">Sample Web Layout - Editable Properties</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#sampleMelbourne"><strong>Published Data examples (Melbourne Buildings)</strong></a>
                    </div>
                    <div id="sampleMelbourne" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="alert alert-info">All examples below are configured to return 500 results maximum, at 100 results per page</div>
                            <ul class="list-group">
                                <li class="list-group-item"><a href="../data/building/.html">HTML building Example</a> <a href="../data/building/.html?page=2">Page 2</a> <a href="../data/building/.html?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/building/.kml">KML building Example</a> <a href="../data/building/.kml?page=2">Page 2</a> <a href="../data/building/.kml?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/building/.png">Map Image building Example</a> <a href="../data/building/.png?page=2">Page 2</a> <a href="../data/building/.png?page=2">Page 3</a></li>
                                <li class="list-group-item"><a href="../data/building/.csv">CSV building Example</a> <a href="../data/building/.csv?page=2">Page 2</a> <a href="../data/building/.csv?page=2">Page 3</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplesOpenLayers"><strong>MapGuide REST API with OpenLayers</strong></a>
                    </div>
                    <div id="samplesOpenLayers" class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item"><a href="untiled/index.html">Basic Sheboygan un-tiled map example with basic legend</a></li>
                                <li class="list-group-item"><a href="tiled/index.html">Basic Sheboygan tiled map example (OpenLayers 2)</a></li>
                                <li class="list-group-item"><a href="tiled/ol3.html">Basic Sheboygan tiled map example (OpenLayers 3)</a></li>
                                <li class="list-group-item"><a href="selection/index.html">Basic Sheboygan un-tiled map example with selection</a></li>
                                <li class="list-group-item"><a href="selection2/index.html">Basic Sheboygan un-tiled map example with selection property palette</a></li>
                                <li class="list-group-item"><a href="mixed/index.html">Basic Sheboygan mixed map (tiled and untiled) example (OpenLayers 2)</a></li>
                                <li class="list-group-item"><a href="mixed/ol3.html">Basic Sheboygan mixed map (tiled and untiled) example (OpenLayers 3)</a></li>
                                <li class="list-group-item"><a href="commercial/index.html">Sheboygan map with Google/OSM layers</a></li>
                                <li class="list-group-item"><a href="xyz/index.html">Sheboygan map as an XYZ tile layer (OpenLayers 2)</a></li>
                                <li class="list-group-item"><a href="ol3_xyz/index.html">Sheboygan map as an XYZ tile layer (OpenLayers 3)</a></li>
                                <li class="list-group-item"><a href="vector/index.html">Sheboygan map as a vector tile layer</a></li>
                                <li class="list-group-item"><a href="ol3_geojson/index.html">Sheboygan map as a set of dynamic GeoJSON layers (OpenLayers 3)</a></li>
                                <li class="list-group-item"><a href="restsources/index.html">OpenLayers map consuming published data</a></li>
                                <li class="list-group-item"><a href="kitchensink/index.html">Sheboygan map with every possible map/selection RESTful URL available</a></li>
                                <li class="list-group-item"><a href="turf/ol3_clientgeom.html">Basic Sheboygan mixed map (tiled and untiled) example (OpenLayers 3) with turf.js</a></li>
                                <li class="list-group-item"><a href="geoprocessing/index.html">Geo-Processing example (OpenLayers 3)</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplesLeaflet"><strong>MapGuide REST API with Leaflet</strong></a>
                    </div>
                    <div id="samplesLeaflet" class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item"><a href="leaflet_xyz/index.html">Sheboygan map as an XYZ tile layer</a></li>
                                <li class="list-group-item"><a href="leaflet_vectortile/index.html">Sheboygan map as an XYZ vector tiles</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplesCesium"><strong>MapGuide REST API with Cesium</strong></a>
                    </div>
                    <div id="samplesCesium" class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item"><a href="cesium/xyz.html">Sheboygan XYZ tiles (via UrlTemplateImageryProvider)</a></li>
                                <li class="list-group-item"><a href="cesium/index.html">Sheboygan Districts</a></li>
                                <li class="list-group-item"><a href="czml/index.html">Cesium viewer with CZML sources</a></li>
                                <li class="list-group-item"><a href="czml/building.html">Melbourne building footprints</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a data-toggle="collapse" data-parent="#accordion" href="#samplesOl3Cesium"><strong>MapGuide REST API with ol3-Cesium</strong></a>
                    </div>
                    <div id="samplesOl3Cesium" class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item"><a href="ol3_cesium/xyz.html">Sheboygan XYZ tiles</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
