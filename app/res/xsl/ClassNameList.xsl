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
                <h1>Class Names</h1>
                <a href="javascript:history.go(-1)">Back</a>
                <ul>
                <xsl:apply-templates select="//StringCollection" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="StringCollection">
        <xsl:for-each select="Item">
        <li>
            <xsl:variable name="className" select="text()" />
            <xsl:variable name="classNamePath" select="translate(text(), ':', '/')" />
            <xsl:value-of select="$className" />
            &#160;
            <a href="../classdef.xml/{$className}">
                [XML]
            </a>
            &#160;
            <a href="../classdef.json/{$className}">
                [json]
            </a>
            &#160;
            <a href="../features.html/{$classNamePath}?pagesize=100&amp;page=1">
                [HTML preview]
            </a>
        </li>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
