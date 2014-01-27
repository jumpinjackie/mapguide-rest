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
                <h1>Coordinate Systems</h1>
                <a href="javascript:history.go(-1)">Back</a>
                <ul>
                <xsl:apply-templates select="//BatchPropertyCollection/PropertyCollection" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="PropertyCollection">
        <li>
            <ul>
                <xsl:apply-templates select="./Property" />
            </ul>
        </li>
    </xsl:template>

    <xsl:template match="Property">
        <li>
            <xsl:variable name="propVal" select="Value" />
            <strong><xsl:value-of select="Name" /></strong>
            &#160;
            <xsl:value-of select="$propVal" />
            &#160;
            <xsl:if test="Name = 'Code'">
                <a href="../mentor/{$propVal}/wkt">[As WKT]</a>
                &#160;
                <a href="../mentor/{$propVal}/epsg">[As EPSG]</a>
            </xsl:if>
        </li>
    </xsl:template>
</xsl:stylesheet>