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
                <ul>
                <xsl:apply-templates select="//ResourceList/ResourceFolder"/>
                <xsl:apply-templates select="//ResourceList/ResourceDocument"/>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceFolder">
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li>
            [Folder]
            <a href="{$resName}/list.html">
                <xsl:value-of select="$resName" />
            </a>
        </li>
    </xsl:template>

    <xsl:template match="ResourceDocument">
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li>
            <xsl:value-of select="$resName" />
            &#160;
            <a href="{$resName}/content.xml">XML</a>
            &#160;
            <a href="{$resName}/content.json">json</a>
            <br/>
            <strong>Resource Options:</strong>
            <!-- Common resource options -->
            <a href="{$resName}/header.xml">
                [Header (XML)]
            </a>
            &#160;
            <a href="{$resName}/header.json">
                [Header (json)]
            </a>
            &#160;
            <a href="{$resName}/references.html">
                [References]
            </a>
            &#160;
            <a href="{$resName}/data.html">
                [Data]
            </a>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('FeatureSource')) + 1) = 'FeatureSource'">
                <br/>
                <strong>Feature Source Options:</strong>
                <!-- Options for feature source -->
                <a href="{$resName}/schemas.html">
                    [Schema]
                </a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>