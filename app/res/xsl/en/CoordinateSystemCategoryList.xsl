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
                <h1>Coordinate System Categories</h1>
                <ul>
                <xsl:apply-templates select="//StringCollection/Item" />
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="Item">
        <li>
            <xsl:variable name="category" select="." />
            <a href="category.html/{$category}">
                <xsl:value-of select="$category" />
            </a>
            &#160;
            <a href="category.xml/{$category}">
                XML
            </a>
            &#160;
            <a href="category.json/{$category}">
                json
            </a>
        </li>
    </xsl:template>
</xsl:stylesheet>