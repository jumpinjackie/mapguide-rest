<!DOCTYPE html>
<html>
    <head>
        <title>Building Report - {$model->FeatId}</title>
        <link rel="stylesheet" href="{$helper->GetAssetPath('common/css/bootstrap.min.css')}" />
        <style type="text/css">
            .label-cell { font-weight: bolder; }
        </style>
    </head>
    <body>
        <div class="container">
            <h3>Building Report - {$model->FeatId}</h3>
            <div class="row">
                <div class="col-md-7">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            Building Details
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="label-cell">ID</td>
                                        <td>{$model->FeatId}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-cell">Address</td>
                                        <td>{$model->FMTADDRESS}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-cell">Number of Floors</td>
                                        <td>{$model->FLOORS}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-cell">Year Built</td>
                                        <td>{$model->BUILD_YEAR}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-cell">Height (m)</td>
                                        <td>{$model->HEIGHT}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Image</div>
                        <div class="panel-body">
                            <img src="{$model->FeatId}.png" />
                        </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading">Other Formats</div>
                        <div class="panel-body">
                            <a href="{$model->FeatId}.xml">XML</a>
                            <a href="{$model->FeatId}.geojson">GeoJSON</a>
                            <a href="{$model->FeatId}.kml">KML</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
