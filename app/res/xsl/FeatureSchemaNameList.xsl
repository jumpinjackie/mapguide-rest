<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='html'/>
    <xsl:param name="ROOTPATH" />
    <xsl:param name="RESOURCENAME" />
    <xsl:param name="ASSETPATH" />
    <xsl:template match="/">
        <html>
            <head>
                <title>Schema Names: <xsl:value-of select="$RESOURCENAME"/></title>
                <link rel="stylesheet" href="{$ASSETPATH}/common/css/bootstrap.min.css" />
                <link rel="stylesheet" href="{$ASSETPATH}/fa/css/font-awesome.min.css" />
            </head>
            <body>
                <h3>Schema Names: <xsl:value-of select="$RESOURCENAME"/></h3>
                <ul class="list-group">
                <xsl:apply-templates select="//StringCollection">
                    <xsl:with-param name="root" select="$ROOTPATH" />
                </xsl:apply-templates>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="StringCollection">
        <xsl:param name="root" />
        <xsl:for-each select="Item">
        <li class="list-group-item">
            <xsl:variable name="schemaName" select="text()" />
            <xsl:value-of select="$schemaName" />
            &#160;
            <a href="{$root}schema.xml/{$schemaName}">
                <i class='fa fa-file-o' />&#160;XML
            </a>
            &#160;
            <a href="{$root}schema.json/{$schemaName}">
                <i class='fa fa-file-o' />&#160;json
            </a>
            &#160;
            <a href="{$root}schema.html/{$schemaName}">
                <i class='fa fa-file-o' />&#160;HTML
            </a>
            &#160;
            <a href="{$root}classes.xml/{$schemaName}"><i class='fa fa-file-o' />&#160;Classes (XML)</a>
            &#160;
            <a href="{$root}classes.json/{$schemaName}"><i class='fa fa-file-o' />&#160;Classes (JSON)</a>
            &#160;
            <a href="{$root}classes.html/{$schemaName}"><i class='fa fa-file-o' />&#160;Classes (HTML)</a>
        </li>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
