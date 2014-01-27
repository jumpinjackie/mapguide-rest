<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fdo="http://fdo.osgeo.org/schemas">

<xsl:output method='html'/>
<xsl:param name="schemaName"/>
<xsl:param name="className"/>
<xsl:param name="resName"/>
<xsl:param name="sessionId"/>
<xsl:param name="viewer"/>

<!--Globalized strings used in this XSL doc-->
<xsl:param name="stringSchema"/>
<xsl:param name="stringClassTitle"/>
<xsl:param name="stringViewData"/>
<xsl:param name="stringViewFeature"/>
<xsl:param name="stringDataProperties"/>
<xsl:param name="stringGeometricProperties"/>
<xsl:param name="stringPropertyName"/>
<xsl:param name="stringPropertyType"/>
<xsl:param name="stringHasMeasures"/>
<xsl:param name="stringHasElevation"/>
<xsl:param name="stringNoGeometry"/>
<xsl:param name="stringNoData"/>
<xsl:param name="stringSrsName"/>


<xsl:template match="/">
    <xsl:apply-templates select="//xs:schema"/>
</xsl:template>

<xsl:template match="xs:schema">
    <xsl:if test="contains(@targetNamespace,$schemaName) or $schemaName=''">
        <xsl:if test="$className=./xs:element/@name or $className=''">
            <h1><xsl:value-of select="$stringSchema"/>
                <xsl:call-template name="getSchemaName">
                    <xsl:with-param name="nameSpace" select="@targetNamespace"/>
                </xsl:call-template>
            </h1>
            <xsl:for-each select="xs:element">
                <xsl:if test="$className=@name or $className=''">
                    <xsl:variable name="selector" select="@type"/>
                    <xsl:variable name="identity" select="xs:key/xs:field/@xpath"/>
                    <xsl:variable name="currclassname" select="@name"/>
                    <h2><xsl:value-of select="$stringClassTitle"/> <xsl:value-of select="$currclassname"/></h2>
                    <xsl:choose>
                        <xsl:when test="@abstract='false'">
                            <span><a><xsl:attribute name="href">showclass.php?resId=<xsl:value-of select="$resName"/>&amp;schemaName=<xsl:value-of select="../@targetNamespace"/>&amp;className=<xsl:value-of select="$currclassname"/>&amp;sessionId=<xsl:value-of select="$sessionId"/>&amp;index=0</xsl:attribute><xsl:attribute name="target">viewFrame</xsl:attribute><xsl:value-of select="$stringViewData"/></a></span>
                        </xsl:when>
                        <xsl:otherwise>
                            <span class="gray"><xsl:value-of select="$stringNoData"/></span>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:apply-templates select="../xs:complexType">
                        <xsl:with-param name="selector" select="$selector"/>
                        <xsl:with-param name="currclassname" select="$currclassname"/>
                        <xsl:with-param name="namespace" select="../@targetNamespace"/>
                    </xsl:apply-templates>
                    <!--apply template to the complexType-->
                    <xsl:apply-templates select="../xs:complexType">
                        <xsl:with-param name="selector" select="$selector"/>
                        <xsl:with-param name="identity" select="$identity"/>
                        <xsl:with-param name="currclassname" select="$currclassname"/>
                        <xsl:with-param name="namespace" select="../@targetNamespace"/>
                    </xsl:apply-templates>
                    <hr/>
                </xsl:if>
            </xsl:for-each>
        </xsl:if>
    </xsl:if>
</xsl:template>

<xsl:template name="getSchemaName">
    <xsl:param name="nameSpace"/>
    <xsl:choose>
        <xsl:when test="contains($nameSpace, '/')">
            <xsl:call-template name="getSchemaName">
                <xsl:with-param name="nameSpace" select="substring-after($nameSpace, '/')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$nameSpace"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="xs:complexType">
    <xsl:param name="selector"/>
    <xsl:param name="identity"/>
    <xsl:param name="currclassname"/>
    <xsl:param name="namespace"/>
    <!--select only the complexType that matches the selector key field-->
    <xsl:if test="substring-after($selector, ':')=@name">
        <xsl:choose>
            <xsl:when test="$identity">
                <!--create table for data properties-->
                <xsl:if test="count(.//xs:element/@type) &lt; count(.//xs:element)">
                    <h3><img src="./images/expand_pane.png"><xsl:attribute name="onclick">Toggle("Data_"+"<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>"+"_"+"<xsl:value-of select="$currclassname"/>")</xsl:attribute></img> <xsl:value-of select="$stringDataProperties"/></h3>
                    <div style="display:none">
                        <xsl:attribute name="id">Data_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:attribute>
                        <table cellspacing="0">
                            <tr>
                                <td class="heading"><xsl:value-of select="$stringPropertyName"/></td>
                                <td class="heading"><xsl:value-of select="$stringPropertyType"/></td>
                            </tr>
                            <!--apply template to elements-->
                            <xsl:apply-templates select=".//xs:element">
                                <xsl:with-param name="identity" select="$identity"/>
                            </xsl:apply-templates>
                        </table>
                    </div>
                </xsl:if>
                <!--create table for geometric properties-->
                <xsl:if test=".//xs:element/@type='gml:AbstractGeometryType'">
                    <h3><img src="./images/expand_pane.png"><xsl:attribute name="onclick">Toggle("Geom_"+"<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>"+"_"+"<xsl:value-of select="$currclassname"/>")</xsl:attribute></img> <xsl:value-of select="$stringGeometricProperties"/></h3>
                    <div style="display:none">
                        <xsl:attribute name="id">Geom_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:attribute>
                        <table cellspacing="0"><tr>
                            <td class="data">
                                <table class="data" cellspacing="0">
                                <tr><td class="heading"><xsl:value-of select="$stringPropertyName"/></td></tr>
                                <tr><td class="heading"><xsl:value-of select="$stringPropertyType"/></td></tr>
                                <tr><td class="heading"><xsl:value-of select="$stringHasMeasures"/></td></tr>
                                <tr><td class="heading"><xsl:value-of select="$stringHasElevation"/></td></tr>
                                <tr><td class="heading"><xsl:value-of select="$stringSrsName"/></td></tr>
                                </table>
                            </td>
                            <!--apply template to elements-->
                            <xsl:apply-templates select=".//xs:element"/>
                        </tr></table>
                    </div>
                </xsl:if>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test=".//xs:element/@type='gml:AbstractGeometryType'">
                        <xsl:call-template name="getGeom">
                            <xsl:with-param name="currclassname" select="$currclassname"/>
                            <xsl:with-param name="sessionId" select="$sessionId"/>
                            <xsl:with-param name="namespace" select="$namespace"/>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <span class="gray"><xsl:value-of select="$stringNoGeometry"/></span>
            </xsl:otherwise>
        </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:if>
</xsl:template>

<xsl:template name="getGeom">
    <xsl:param name="currclassname"/>
    <xsl:param name="sessionId"/>
    <xsl:param name="namespace"/>
    <xsl:for-each select=".//xs:element">
        <xsl:if test="@type='gml:AbstractGeometryType'">
            <span>
                <a><xsl:attribute name="href">showgeom.php?resId=<xsl:value-of select="$resName"/>&amp;schemaName=<xsl:value-of select="$namespace"/>&amp;className=<xsl:value-of select="$currclassname"/>&amp;geomname=<xsl:value-of select="@name"/>&amp;geomtype=<xsl:value-of select="@fdo:geometricTypes"/>&amp;sessionId=<xsl:value-of select="$sessionId"/>&amp;viewer=<xsl:value-of select="$viewer"/></xsl:attribute>
                <xsl:attribute name="target">viewFrame</xsl:attribute><xsl:value-of select="$stringViewFeature"/></a>
            </span>
        </xsl:if>
    </xsl:for-each>
</xsl:template>

<xsl:template match="xs:element">
    <xsl:param name="identity"/>
    <xsl:choose>
        <!--determines the data properties-->
        <xsl:when test="$identity">
            <xsl:if test="not(@type)">
                <tr>
                    <!--checks if the element is the identity property-->
                    <td><xsl:if test="$identity=@name">*</xsl:if><xsl:value-of select="@name"/></td>
                    <td><xsl:variable name="type" select=".//xs:restriction/@base"/><xsl:value-of select="substring-after($type, ':')"/><xsl:variable name="maxlength" select=".//xs:maxLength/@value"/><xsl:if test="$maxlength and not($maxlength='')">(<xsl:value-of select="$maxlength"/>)</xsl:if></td>
                </tr>
            </xsl:if>
        </xsl:when>
        <!--determines the geometry properties-->
        <xsl:otherwise>
            <xsl:if test="@type='gml:AbstractGeometryType'">
                <td class="data">
                    <table class="data" cellspacing="0">
                    <tr><td><xsl:value-of select="@name"/></td></tr>
                    <tr><td><xsl:value-of select="@fdo:geometricTypes"/></td></tr>
                    <tr><td><xsl:value-of select="@fdo:hasMeasure"/></td></tr>
                    <tr><td><xsl:value-of select="@fdo:hasElevation"/></td></tr>
                    <tr><td><xsl:value-of select="@fdo:srsName"/></td></tr>
                    </table>
                </td>
            </xsl:if>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
