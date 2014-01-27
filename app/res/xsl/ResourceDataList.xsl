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
                <h1>Resource Data</h1>
                <a href="javascript:history.go(-1)">Back</a>
                <ul>
                <xsl:apply-templates select="//ResourceDataList/ResourceData" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceData">
        <li>
            <xsl:variable name="resDataName" select="Name" />
            [<xsl:value-of select="Type" />]
            &#160;
            <xsl:value-of select="Name" />
            &#160;
            <xsl:if test="Type = 'File'">
                <a href="data/{$resDataName}">Download</a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>