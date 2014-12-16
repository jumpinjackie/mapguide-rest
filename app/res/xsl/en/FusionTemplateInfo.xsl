<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">
    <xsl:output method='xml'/>
    <xsl:param name="ROOT" select="'../../'"/>
    <!-- This XSL stylesheet fixes the template location and preview image urls to reflect being relative to mapguide-rest -->
    <xsl:template match="node()|@*">
      <xsl:copy>
         <xsl:apply-templates select="node()|@*"/>
      </xsl:copy>
    </xsl:template>

    <xsl:template match="TemplateInfo/LocationUrl">
      <xsl:copy>
         <xsl:value-of select="$ROOT"/>
         <xsl:copy-of select="text()" />
      </xsl:copy>
    </xsl:template>

    <xsl:template match="TemplateInfo/PreviewImageUrl">
      <xsl:copy>
         <xsl:value-of select="$ROOT"/>
         <xsl:copy-of select="text()" />
      </xsl:copy>
    </xsl:template>

</xsl:stylesheet>