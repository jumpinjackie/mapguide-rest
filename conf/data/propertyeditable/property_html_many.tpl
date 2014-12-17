<!DOCTYPE html>
<html>
    <head>
        <title>Property Results</title>
        <link rel="stylesheet" href="{$helper->GetAssetPath('common/css/bootstrap.min.css')}" />
        <link rel="stylesheet" href="{$helper->GetAssetPath('fa/css/font-awesome.min.css')}" />
        <script type="text/javascript" src="{$helper->GetAssetPath('common/js/jquery-1.10.2.min.js')}"></script>
        <style type="text/css">
            
        </style>
        <script type="text/javascript">

            var zoomScale = 500;

            function getViewer() {
                if (typeof(opener) != 'undefined') //window.open()'d from Task Pane frame
                    return opener.parent.parent;
                else
                    return parent.parent;
            }

            function zoomToProperty(x, y) {
                getViewer().ZoomToView(x, y, zoomScale, true);
            }

            function deleteProperty(id) {
                if (confirm("Are you sure you want to delete property (" + id + ")?")) {
                    var session = getViewer().GetMapFrame().GetSessionId();
                    var urlWithoutQuery = window.location.href.split("?")[0];
                    var deleteUrl = urlWithoutQuery.replace(".html", id + ".json");
                    var promise = $.ajax({
                        method: "post",
                        url: deleteUrl,
                        data: { session: session },
                        headers: {
                            "X-HTTP-Method-Override": "DELETE"
                        },
                        success: onPropertyDeleted,
                        error: onPropertyDeletedError
                    });
                }
            }

            function onPropertyDeletedError(data, textStatus, jqXHR) {
                try {
                    var xml = data.responseXML;
                    var errNode = xml.getElementsByTagName("Error").item(0);
                    alert("Failed to delete property.\n\n" + errNode.textContent);
                } catch (e) {
                    if (data.status == 403)
                        alert("Failed to delete property. You have no permission to delete properties");
                    else
                        alert("Failed to delete property");
                }
            }

            function onPropertyDeleted(data, textStatus, jqXHR) {
                alert("Property deleted");
                var map = getViewer().GetMapFrame();
                map.Refresh();
                window.location.reload();
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
        <div id="main" class="container">
            <h3>Property Results</h3>
            {if $maxPages > 1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page&nbsp;&nbsp;</a>
                {/if}
                {if $currentPage < $maxPages}
                <a class="pull-left" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
                {/if}
            {elseif $maxPages = -1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page&nbsp;&nbsp;</a>
                {/if}
                <a class="pull-left" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
            {/if}
            <table class="table table-striped table-bordered table-hover table-condensed">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>ID</th>
                        <th>Address</th>
                        <th>Owner</th>
                        <th>Zone</th>
                        <th>Description1</th>
                        <th>Description2</th>
                        <th>Description3</th>
                        <th>City</th>
                        <th>Zip</th>
                    </tr>
                </thead>
                <tbody>
                    {while $model->Next()}
                    <tr>
                        <td>
                            <a class="btn btn-success" href="javascript:zoomToProperty({$model->Current()->GeometryAsType("SHPGEOM", "CentroidCommaSeparated")})"><i class="fa fa-search-plus"></i></a>
                        </td>
                        <td>
                            <a class="btn btn-primary" href="{$model->Current()->Autogenerated_SDF_ID}.html"><i class="fa fa-pencil"></i></a>
                        </td>
                        <td>
                            <a class="btn btn-danger" href="javascript:deleteProperty({$model->Current()->Autogenerated_SDF_ID})"><i class="fa fa-times"></i></a>
                        </td>
                        <td>{$model->Current()->Autogenerated_SDF_ID}</td>
                        <td>{$model->Current()->RPROPAD}</td>
                        <td>{$model->Current()->RNAME}</td>
                        <td>{$model->Current()->RTYPE}</td>
                        <td>{$model->Current()->RLDESCR1}</td>
                        <td>{$model->Current()->RLDESCR2}</td>
                        <td>{$model->Current()->RLDESCR3}</td>
                        <td>{$model->Current()->RCITY}</td>
                        <td>{$model->Current()->RZIP}</td>
                    </tr>
                    {/while}
                </tbody>
            </table>
            {if $maxPages > 1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page&nbsp;&nbsp;</a>
                {/if}
                {if $currentPage < $maxPages}
                <a class="pull-left" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
                {/if}
            {elseif $maxPages = -1}
                {if $currentPage > 1}
                <a class="pull-left" href="javascript:gotoPage({$currentPage - 1})">&lt;&lt;&nbsp;Previous Page&nbsp;&nbsp;</a>
                {/if}
                <a class="pull-left" href="javascript:gotoPage({$currentPage + 1})">Next Page&nbsp;&gt;&gt;</a>
            {/if}
        </div>
    </body>
</html>