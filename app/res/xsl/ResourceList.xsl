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
    <xsl:template match="/">
        <html>
            <head>
            </head>
            <body>
                <xsl:if test="string-length($FOLDERPATH) > 0">
                <h3>Index of <xsl:value-of select="$FOLDERPATH"/></h3>
                </xsl:if>
                <xsl:if test="string-length($PARENTPATHROOT) > 0">
                <a href="{$PARENTPATHROOT}/list.html">Parent</a>
                </xsl:if>
                <ul>
                <xsl:apply-templates select="//ResourceList/ResourceFolder">
                    <xsl:with-param name="root" select="$ROOTPATH" />
                </xsl:apply-templates>
                <xsl:apply-templates select="//ResourceList/ResourceDocument">
                    <xsl:with-param name="root" select="$ROOTPATH" />
                </xsl:apply-templates>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ResourceFolder">
        <xsl:param name="root" />
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li>
            [Folder]
            <a href="{$root}{$resName}/list.html">
                <xsl:value-of select="$resName" />
            </a>
        </li>
    </xsl:template>

    <xsl:template match="ResourceDocument">
        <xsl:param name="root" />
        <xsl:variable name="resName" select="str:split(ResourceId, '/')[last()]" />
        <li>
            <xsl:value-of select="$resName" />
            &#160;
            <a href="{$root}{$resName}/content.xml">XML</a>
            &#160;
            <a href="{$root}{$resName}/content.json">json</a>
            <br/>
            <strong>Resource Options:</strong>
            <!-- Common resource options -->
            <a href="{$root}{$resName}/header.xml">
                [Header (XML)]
            </a>
            &#160;
            <a href="{$root}{$resName}/header.json">
                [Header (json)]
            </a>
            &#160;
            <a href="{$root}{$resName}/references.html">
                [References]
            </a>
            &#160;
            <a href="{$root}{$resName}/datalist.html">
                [Data]
            </a>
            <xsl:if test="substring($resName, (string-length($resName) - string-length('FeatureSource')) + 1) = 'FeatureSource'">
                <br/>
                <strong>Feature Source Options:</strong>
                <!-- Options for feature source -->
                <a href="{$root}{$resName}/schemas.html">
                    [Schema]
                </a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>