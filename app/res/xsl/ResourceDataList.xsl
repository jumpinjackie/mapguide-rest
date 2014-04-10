<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='html'/>
    <xsl:param name="ROOTPATH" />
    <xsl:param name="RESOURCENAME" />
    <xsl:template match="/">
        <html>
            <head>
            </head>
            <body>
                <h3>Resource Data: <xsl:value-of select="$RESOURCENAME"/></h3>
                <ul class="list-group">
                <xsl:apply-templates select="//ResourceDataList/ResourceData">
                    <xsl:with-param name="root" select="$ROOTPATH" />
                </xsl:apply-templates>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceData">
        <xsl:param name="root" />
        <li class="list-group-item">
            <xsl:variable name="resDataName" select="Name" />
            [<xsl:value-of select="Type" />]
            &#160;
            <xsl:value-of select="Name" />
            &#160;
            <xsl:if test="Type = 'File'">
                <a href="{$root}data/{$resDataName}"><i class="fa fa-download" />&#160;Download</a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>