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
                <h1>FDO Providers</h1>
                <ul>
                <xsl:apply-templates select="//FeatureProviderRegistry/FeatureProvider" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="ConnectionProperties">
        <xsl:param name="providerName" />
        <strong>Connection Properties</strong>
        <ul>
            <xsl:apply-templates select="ConnectionProperty">
                <xsl:with-param name="providerName" select="$providerName" />
            </xsl:apply-templates>
        </ul>
    </xsl:template>

    <xsl:template match="ConnectionProperty">
        <xsl:param name="providerName" />
        <li>
            <xsl:variable name="propName" select="Name" />
            <xsl:value-of select="$propName" />
            <ul>
                <li>
                    <strong>Is Enumerable:</strong>&#160;<xsl:value-of select="@Enumerable" />
                    &#160;
                    <xsl:if test="@Enumerable = 'true'">
                        <a href="providers/{$providerName}/connectvalues.xml/{$propName}">[Possible values (XML)]</a>
                        &#160;
                        <a href="providers/{$providerName}/connectvalues.json/{$propName}">[Possible values (json)]</a>
                    </xsl:if>
                </li>
                <li><strong>Is Protected:</strong>&#160;<xsl:value-of select="@Protected" /></li>
                <li><strong>Is Required:</strong>&#160;<xsl:value-of select="@Required" /></li>
                <li><strong>Default Value:</strong>&#160;<xsl:value-of select="DefaultValue" /></li>
            </ul>
        </li>
    </xsl:template>

    <xsl:template match="FeatureProvider">
        <li>
            <xsl:variable name="providerName" select="Name" />
            <xsl:value-of select="$providerName" /> (<xsl:value-of select="DisplayName" />)
            <br/>
            <strong>Version:</strong> <xsl:value-of select="Version" />
            <br/>
            <strong>FDO Version:</strong> <xsl:value-of select="FeatureDataObjectsVersion" />
            <br/>
            <xsl:apply-templates select="ConnectionProperties">
                <xsl:with-param name="providerName" select="$providerName" />
            </xsl:apply-templates>
            <strong>Options:</strong>
            <a href="providers/{$providerName}/capabilities.xml">
                [Capabilities XML]
            </a>
            <a href="providers/{$providerName}/capabilities.json">
                [Capabilities json]
            </a>
        </li>
    </xsl:template>
</xsl:stylesheet>