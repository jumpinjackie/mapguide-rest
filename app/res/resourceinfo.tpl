<!DOCTYPE html>
<html>
    <head>
        <title>Resource Info: {$resId}</title>
        <link rel="stylesheet" href="{$assetPath}/common/css/bootstrap.min.css" />
        <link rel="stylesheet" href="{$assetPath}/fa/css/font-awesome.min.css" />
        <script type="text/javascript" src="{$assetPath}/common/js/jquery-1.10.2.min.js"></script>
        <style type="text/css">
            #resourcePane { border: none; position: absolute; top: 230px; bottom: 5px; right: 5px; left: 5px; }
            .fullwidthandheight { width: 100%; height: 100%; }
            .borderless { border: none; }
            ul.itemlist { margin-top: 70px; }
        </style>
        <script type="text/javascript">
            $(function() {
                $("a.contentload").click(function(e) {
                    e.preventDefault();
                    $("#resourcePane").empty().append('<textarea class="fullwidthandheight" readonly="readonly"/>');
                    $("#resourcePane textarea").load($(this).attr("href"));
                });
                $("a.pageload").click(function(e) {
                    e.preventDefault();
                    $("#resourcePane").empty().load($(this).attr("href"));
                });
                $("a.iframeload").click(function(e) {
                    e.preventDefault();
                    var url = $(this).attr("href");
                    $("#resourcePane").empty().append('<iframe class="fullwidthandheight borderless"></iframe>');
                    $("iframe.fullwidthandheight").attr("src", url);
                });
            });
        </script>
    </head>
    <body>
        <h3>Resource: {$resId}</h3>
        <div class="well">
            <strong>General Options</strong>
            <a class="contentload" href="{$urlRoot}/content.xml"><i class="fa fa-file-text-o"></i>&nbsp;Content (XML)</a>
            <a class="contentload" href="{$urlRoot}/content.json"><i class="fa fa-file-text-o"></i>&nbsp;Content (JSON)</a>
            <!-- Common resource options -->
            <a class="contentload" href="{$urlRoot}/header.xml">
                <i class="fa fa-file-text-o"></i>&nbsp;Header (XML)
            </a>
            &nbsp;
            <a class="contentload" href="{$urlRoot}/header.json">
                <i class="fa fa-file-text-o"></i>&nbsp;Header (json)
            </a>
            &nbsp;
            <a class="pageload" href="{$urlRoot}/references.html">
                <i class="fa fa-chain"></i>&nbsp;References
            </a>
            &nbsp;
            <a class="pageload" href="{$urlRoot}/datalist.html">
                <i class="fa fa-files-o"></i>&nbsp;Data
            </a>
        </div>
        {if $resourceType eq 'FeatureSource'}
        <div class="well">
            <strong>Feature Source Options</strong>
            <a class="iframeload" href="{$urlRoot}/schemas.html">
                <i class="fa fa-sitemap" ></i>&nbsp;Schema
            </a>
            <a class="iframeload" href="{$urlRoot}/preview">
                <i class="fa fa-search" ></i>&nbsp;Preview
            </a>
        </div>
        {elseif $resourceType eq 'LayerDefinition'}
        <div class="well">
            <strong>Layer Definition Options</strong>
            <a class="iframeload" href="{$urlRoot}/preview">
                <i class="fa fa-search" ></i>&nbsp;Preview
            </a>
        </div>
        {elseif $resourceType eq 'MapDefinition'}
        <div class="well">
            <strong>Map Definition Options</strong>
            <a class="iframeload" href="{$urlRoot}/preview">
                <i class="fa fa-search" ></i>&nbsp;Preview
            </a>
        </div>
        {elseif $resourceType eq 'SymbolDefinition'}
        <div class="well">
            <strong>Symbol Definition Options</strong>
            <a class="iframeload" href="{$urlRoot}/preview">
                <i class="fa fa-search" ></i>&nbsp;Preview
            </a>
        </div>
        {elseif $resourceType eq 'WatermarkDefinition'}
        <div class="well">
            <strong>Watermark Definition Options</strong>
            <a class="iframeload" href="{$urlRoot}/preview">
                <i class="fa fa-search" ></i>&nbsp;Preview
            </a>
        </div>
        {elseif $resourceType eq 'WebLayout'}
        <div class="well">
            <strong>Web Layout Options</strong>
            <a class="iframeload" href="{$urlRoot}/viewer">
                <i class="fa fa-globe" ></i>&nbsp;AJAX Viewer
            </a>
        </div>
        {elseif $resourceType eq 'ApplicationDefinition'}
        <div class="well">
            <strong>Application Definition Options</strong>
            <a class="iframeload" href="{$urlRoot}/viewer/slate">
                <i class="fa fa-globe" ></i>&nbsp;Fusion - Slate
            </a>
            <a class="iframeload" href="{$urlRoot}/viewer/aqua">
                <i class="fa fa-globe" ></i>&nbsp;Fusion - Aqua
            </a>
            <a class="iframeload" href="{$urlRoot}/viewer/maroon">
                <i class="fa fa-globe" ></i>&nbsp;Fusion - Maroon
            </a>
            <a class="iframeload" href="{$urlRoot}/viewer/limegold">
                <i class="fa fa-globe" ></i>&nbsp;Fusion - LimeGold
            </a>
            <a class="iframeload" href="{$urlRoot}/viewer/turquoiseyellow">
                <i class="fa fa-globe" ></i>&nbsp;Fusion - TurquoiseYellow
            </a>
        </div>
        {/if}
        <div id="resourcePane" name="resourcePane">
        </div>
    </body>
</html>