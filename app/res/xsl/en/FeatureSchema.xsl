<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fdo="http://fdo.osgeo.org/schemas">

<xsl:output method='html'/>
<xsl:param name="schemaName"/>
<xsl:param name="className"/>
<xsl:param name="resName"/>
<xsl:param name="sessionId"/>
<xsl:param name="viewer"/>

<xsl:param name="ROOTPATH" />
<xsl:param name="FOLDERPATH" />
<xsl:param name="BASEPATH" />
<xsl:param name="ASSETPATH" />

<!--Globalized strings used in this XSL doc-->
<xsl:param name="stringTitle">Schema Preview</xsl:param>
<xsl:param name="stringSchema">Schema</xsl:param>
<xsl:param name="stringClassTitle">Class</xsl:param>
<xsl:param name="stringViewData">View Data</xsl:param>
<xsl:param name="stringViewFeature">View Features</xsl:param>
<xsl:param name="stringDataProperties">Data Properties</xsl:param>
<xsl:param name="stringGeometricProperties">Geometric Properties</xsl:param>
<xsl:param name="stringPropertyName">Property Name</xsl:param>
<xsl:param name="stringPropertyType">Property Type</xsl:param>
<xsl:param name="stringHasMeasures">Has Measure</xsl:param>
<xsl:param name="stringHasElevation">Has Elevation</xsl:param>
<xsl:param name="stringNoGeometry">No Geometry</xsl:param>
<xsl:param name="stringNoData">No Data</xsl:param>
<xsl:param name="stringSrsName">SRS Name</xsl:param>


<xsl:template match="/">
<html>
    <head>
        <title><xsl:value-of select="$stringTitle"/></title>
        <link rel="stylesheet" href="{$ASSETPATH}/common/css/bootstrap.min.css" />
        <link rel="stylesheet" href="{$ASSETPATH}/fa/css/font-awesome.min.css" />
        <style type="text/css">
            #previewPane { width: 100%; height: 100%; border: 0; }
        </style>
        <script type="text/javascript" src="{$ASSETPATH}/common/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="{$ASSETPATH}/common/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <xsl:apply-templates select="//xs:schema"/>
                </div>
                <div class="col-md-8">
                    <iframe id="previewPane" name="previewPane" />
                </div>
            </div>
        </div>
    </body>
</html>
</xsl:template>

<xsl:template match="xs:schema">
    <xsl:if test="contains(@targetNamespace,$schemaName) or $schemaName=''">
        <xsl:if test="$className=./xs:element/@name or $className=''">
            <xsl:variable name="currSchemaName">
                <xsl:call-template name="getSchemaName">
                    <xsl:with-param name="nameSpace" select="@targetNamespace"/>
                </xsl:call-template>
            </xsl:variable>
            <h4><xsl:value-of select="$stringSchema"/>:&#160;
                <xsl:value-of select="$currSchemaName" />
            </h4>
            <xsl:for-each select="xs:element">
                <xsl:if test="$className=@name or $className=''">
                    <xsl:variable name="selector" select="@type"/>
                    <xsl:variable name="identity" select="xs:key/xs:field/@xpath"/>
                    <xsl:variable name="currclassname" select="@name"/>
                    <h4><xsl:value-of select="$stringClassTitle"/>:&#160;<xsl:value-of select="$currclassname"/></h4>
                    <xsl:choose>
                        <xsl:when test="@abstract='false'">
                            <a class="btn btn-primary" target="previewPane">
                                <xsl:attribute name="href"><xsl:value-of select="$BASEPATH" />/features.html/<xsl:value-of select="$currSchemaName" />/<xsl:value-of select="$currclassname" />?pagesize=100&amp;page=1</xsl:attribute>
                                <xsl:value-of select="$stringViewData"/>
                            </a>
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
    <xsl:param name="dataPanelID">DataPanel_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <xsl:param name="dataCollapseID">DataCollapse_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <xsl:param name="dataBodyID">DataBody_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <xsl:param name="geomPanelID">GeomPanel_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <xsl:param name="geomCollapseID">GeomCollapse_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <xsl:param name="geomBodyID">GeomBody_<xsl:call-template name="getSchemaName"><xsl:with-param name="nameSpace" select="$namespace"/></xsl:call-template>_<xsl:value-of select="$currclassname"/></xsl:param>
    <!--select only the complexType that matches the selector key field-->
    <xsl:if test="substring-after($selector, ':')=@name">
        <xsl:choose>
            <xsl:when test="$identity">
                <div class="panel-group">
                    <xsl:attribute name="id"><xsl:value-of select="$dataPanelID"/></xsl:attribute>
                <!--create table for data properties-->
                <xsl:if test="count(.//xs:element/@type) &lt; count(.//xs:element)">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse">
                                <xsl:attribute name="data-parent">#<xsl:value-of select="$dataPanelID"/></xsl:attribute>
                                <xsl:attribute name="href">#<xsl:value-of select="$dataBodyID"/></xsl:attribute>
                                <xsl:value-of select="$stringDataProperties"/>
                            </a>
                        </h4>
                    </div>
                    <div class="panel-collapse collapse">
                        <xsl:attribute name="id"><xsl:value-of select="$dataBodyID"/></xsl:attribute>
                        <div class="panel-body">
                            <table class="table table-hover table-condensed">
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
                    </div>
                </div>
                </xsl:if>
                <!--create table for geometric properties-->
                <xsl:if test=".//xs:element/@type='gml:AbstractGeometryType'">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse">
                                <xsl:attribute name="data-parent">#<xsl:value-of select="$geomPanelID"/></xsl:attribute>
                                <xsl:attribute name="href">#<xsl:value-of select="$geomBodyID"/></xsl:attribute>
                                <xsl:value-of select="$stringGeometricProperties"/>
                            </a>
                        </h4>
                    </div>
                    <div class="panel-collapse collapse">
                        <xsl:attribute name="id"><xsl:value-of select="$geomBodyID"/></xsl:attribute>
                        <div class="panel-body">
                            <table><tr>
                                <td class="data">
                                    <table class="table table-hover table-condensed" cellspacing="0">
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
                    </div>
                </div>
                </xsl:if>
            </div>
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
            <!--
            <a class="btn btn-primary" href="${BASEPATH}"><xsl:value-of select="$stringViewFeature"/></a>
            -->
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
                    <table class="table table-hover table-condensed" cellspacing="0">
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
