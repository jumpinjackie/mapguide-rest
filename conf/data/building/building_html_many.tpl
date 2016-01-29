<!DOCTYPE html>
<html>
    <head>
        <title>Building Results</title>
        <link rel="stylesheet" href="{$helper->GetAssetPath('common/css/bootstrap.min.css')}" />
        <script type="text/javascript">

            //NOTE: All representations in restcfg.json have been set up with the same maxcount and pagesize, ensuring
            //that switching between representations results in the same "page" of data being represented
            //in a different fashion.
            function viewResultsAs(format) {
                var url = window.location.href.replace(".html", "." + format);
                window.location.href = url;
            }

            function downloadAs(format) {
                var url = window.location.href.replace(".html", "." + format);
                window.location.href = updateQueryStringParameter(url, "download", "1");
            }

            function updateQueryStringParameter(uri, key, value) {
                var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
                separator = uri.indexOf('?') !== -1 ? "&" : "?";
                if (uri.match(re)) {
                    return uri.replace(re, '$1' + key + "=" + value + '$2');
                } else {
                    return uri + separator + key + "=" + value;
                }
            }

            function gotoPage(pageNo) {
                window.location.href = updateQueryStringParameter(window.location.href, "page", pageNo);
            }

        </script>
    </head>
    <body>
        <!--
        Values for pagination debugging

        Current Page: {$currentPage}
        Max Pages: {$maxPages}
        End of Reader: {$endOfReader}
        -->
        <div class="container">
            <h3>Building Results</h3>
            <div>
                <span>View result as:</span>
                <a href="javascript:viewResultsAs('xml')">XML</a>
                <a href="javascript:viewResultsAs('csv')">CSV</a>
                <a href="javascript:viewResultsAs('kml')">KML</a>
                <a href="javascript:viewResultsAs('geojson')">GeoJSON</a>
                <a href="javascript:viewResultsAs('png')">PNG</a>
                <a href="javascript:viewResultsAs('png8')">PNG8</a>
                <a href="javascript:viewResultsAs('jpg')">JPG</a>
                <a href="javascript:viewResultsAs('gif')">GIF</a>
            </div>
            <div>
                <span>Download:</span>
                <a href="javascript:downloadAs('xml')">XML</a>
                <a href="javascript:downloadAs('csv')">CSV</a>
                <a href="javascript:downloadAs('kml')">KML</a>
                <a href="javascript:downloadAs('geojson')">GeoJSON</a>
                <a href="javascript:downloadAs('png')">PNG</a>
                <a href="javascript:downloadAs('png8')">PNG8</a>
                <a href="javascript:downloadAs('jpg')">JPG</a>
                <a href="javascript:downloadAs('gif')">GIF</a>
            </div>
            {if $maxPages > 1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page</a>
                {/if}
                {if $currentPage < $maxPages}
                <a class="pull-right" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
                {/if}
            {elseif $maxPages = -1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page</a>
                {/if}
                <a class="pull-right" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
            {/if}
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Address</th>
                        <th>Floors</th>
                        <th>Year Built</th>
                        <th>Height (m)</th>
                    </tr>
                </thead>
                <tbody>
                    {while $model->Next()}
                    <tr>
                        <td><a href="{$model->Current()->FeatId}.html">{$model->Current()->FeatId}</a></td>
                        <td>{$model->Current()->FMTADDRESS}</td>
                        <td>{$model->Current()->FLOORS}</td>
                        <td>{$model->Current()->BUILD_YEAR}</td>
                        <td>{$model->Current()->HEIGHT}</td>
                    </tr>
                    {/while}
                </tbody>
            </table>
            {if $maxPages > 1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page</a>
                {/if}
                {if $currentPage < $maxPages}
                <a class="pull-right" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
                {/if}
            {elseif $maxPages = -1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page</a>
                {/if}
                <a class="pull-right" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
            {/if}
        </div>
    </body>
</html>