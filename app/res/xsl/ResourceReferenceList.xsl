<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='html'/>
    <xsl:param name="RESOURCENAME" />
    <xsl:template match="/">
        <html>
            <head>
                <title>Resource References: <xsl:value-of select="$RESOURCENAME"/></title>
            </head>
            <body>
                <h3>Resource References: <xsl:value-of select="$RESOURCENAME"/></h3>
                <ul class="list-group">
                <xsl:apply-templates select="//ResourceReferenceList" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceReferenceList">
        <li class="list-group-item">
            <xsl:variable name="resId" select="ResourceId" />
            <xsl:value-of select="$resId" />
        </li>
    </xsl:template>
</xsl:stylesheet>