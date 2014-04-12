<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='html'/>
    <xsl:param name="ROOTPATH" />
    <xsl:param name="FOLDERPATH" />
    <xsl:param name="PARENTPATHROOT" />
    <xsl:param name="ASSETPATH" />
    <xsl:template match="/">
        <html>
            <head>
                <xsl:if test="string-length($FOLDERPATH) > 0">
                <title>Site Repository: <xsl:value-of select="$FOLDERPATH"/></title>
                </xsl:if>
                <link rel="stylesheet" href="{$ASSETPATH}/common/css/bootstrap.min.css" />
                <link rel="stylesheet" href="{$ASSETPATH}/fa/css/font-awesome.min.css" />
                <script type="text/javascript" src="{$ASSETPATH}/common/js/jquery-1.10.2.min.js"></script>
                <style type="text/css">
                    #resourcePane { width: 65%; height: 80%; border: none; position: fixed; margin-top: 70px; }
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
                <div class="navbar navbar-default navbar-fixed-top" role="navigation">
                    <div class="container">
                        <xsl:if test="string-length($FOLDERPATH) > 0">
                            <a class="navbar-brand" href="javascript:void(0)"><xsl:value-of select="$FOLDERPATH"/></a>
                        </xsl:if>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-xs-3">
                            <ul class="itemlist list-group">
                            <xsl:if test="string-length($PARENTPATHROOT) > 0">
                                <li class="list-group-item"><a href="{$PARENTPATHROOT}/list.html"><i class="fa fa-arrow-up" />&#160;Parent</a></li>
                            </xsl:if>
                            <xsl:apply-templates select="//ResourceList/ResourceFolder">
                                <xsl:with-param name="root" select="$ROOTPATH" />
                            </xsl:apply-templates>
                            <xsl:apply-templates select="//ResourceList/ResourceDocument">
                                <xsl:with-param name="root" select="$ROOTPATH" />
                            </xsl:apply-templates>
                            </ul>
                        </div>
                        <div class="col-xs-9">
                            <div id="resourcePane" name="resourcePane">
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceFolder">
        <xsl:param name="root" />
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li class="list-group-item">
            <a href="{$root}{$resName}/list.html">
                <i class="fa fa-folder-o" />&#160;<xsl:value-of select="$resName" />
            </a>
        </li>
    </xsl:template>

    <xsl:template match="ResourceDocument">
        <xsl:param name="root" />
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li class="list-group-item">
            <p class="list-group-item-heading"><i class="fa fa-file-o" />&#160;<xsl:value-of select="$resName" /></p>
            &#160;
            <a class="contentload" href="{$root}{$resName}/content.xml"><i class="fa fa-file-text-o" />&#160;Content (XML)</a>
            &#160;
            <a class="contentload" href="{$root}{$resName}/content.json"><i class="fa fa-file-text-o" />&#160;Content (JSON)</a>
            &#160;
            <!-- Common resource options -->
            <a class="contentload" href="{$root}{$resName}/header.xml">
                <i class="fa fa-file-text-o" />&#160;Header (XML)
            </a>
            &#160;
            <a class="contentload" href="{$root}{$resName}/header.json">
                <i class="fa fa-file-text-o" />&#160;Header (json)
            </a>
            &#160;
            <a class="pageload" href="{$root}{$resName}/references.html">
                <i class="fa fa-chain" />&#160;References
            </a>
            &#160;
            <a class="pageload" href="{$root}{$resName}/datalist.html">
                <i class="fa fa-files-o" />&#160;Data
            </a>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('FeatureSource')) + 1) = 'FeatureSource'">
                <hr/>
                <!-- Options for feature source -->
                <a class="pageload" href="{$root}{$resName}/schemas.html">
                    <i class="fa fa-sitemap" />&#160;Schema
                </a>
                <a class="iframeload" href="{$root}{$resName}/preview">
                    <i class="fa fa-search" />&#160;Preview
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('LayerDefinition')) + 1) = 'LayerDefinition'">
                <hr/>
                <a class="iframeload" href="{$root}{$resName}/preview">
                    <i class="fa fa-search" />&#160;Preview
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('MapDefinition')) + 1) = 'MapDefinition'">
                <hr/>
                <a class="iframeload" href="{$root}{$resName}/preview">
                    <i class="fa fa-search" />&#160;Preview
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('SymbolDefinition')) + 1) = 'SymbolDefinition'">
                <hr/>
                <a class="iframeload" href="{$root}{$resName}/preview">
                    <i class="fa fa-search" />&#160;Preview
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('WatermarkDefinition')) + 1) = 'WatermarkDefinition'">
                <hr/>
                <a class="iframeload" href="{$root}{$resName}/preview">
                    <i class="fa fa-search" />&#160;Preview
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('WebLayout')) + 1) = 'WebLayout'">
                <hr/>
                <!-- Options for Web Layout -->
                <a class="iframeload" href="{$root}{$resName}/viewer">
                    <i class="fa fa-globe" />&#160;AJAX Viewer
                </a>
            </xsl:if>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('ApplicationDefinition')) + 1) = 'ApplicationDefinition'">
                <hr/>
                <!-- Options for Application Definition -->
                <a class="iframeload" href="{$root}{$resName}/viewer/slate">
                    <i class="fa fa-globe" />&#160;Fusion - Slate
                </a>
                &#160;
                <!-- Options for Application Definition -->
                <a class="iframeload" href="{$root}{$resName}/viewer/aqua">
                    <i class="fa fa-globe" />&#160;Fusion - Aqua
                </a>
                &#160;
                <!-- Options for Application Definition -->
                <a class="iframeload" href="{$root}{$resName}/viewer/maroon">
                    <i class="fa fa-globe" />&#160;Fusion - Maroon
                </a>
                &#160;
                <!-- Options for Application Definition -->
                <a class="iframeload" href="{$root}{$resName}/viewer/limegold">
                    <i class="fa fa-globe" />&#160;Fusion - LimeGold
                </a>
                &#160;
                <!-- Options for Application Definition -->
                <a class="iframeload" href="{$root}{$resName}/viewer/turquoiseyellow">
                    <i class="fa fa-globe" />&#160;Fusion - TurquoiseYellow
                </a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>