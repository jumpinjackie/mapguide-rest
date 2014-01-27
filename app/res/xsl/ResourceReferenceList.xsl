<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='html'/>

    <xsl:template match="/">
        <html>
            <head>
            </head>
            <body>
                <h1>Resource References</h1>
                <a href="javascript:history.go(-1)">Back</a>
                <ul>
                <xsl:apply-templates select="//ResourceReferenceList" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceReferenceList">
        <li>
            <xsl:variable name="resId" select="ResourceId" />
            <xsl:value-of select="$resId" />
        </li>
    </xsl:template>
</xsl:stylesheet>