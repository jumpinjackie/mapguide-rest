<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Melbourne Buildings</name>
    <open>1</open>
    <Folder>
      <name>Buildings in View</name>
      <open>1</open>
      {while $model->Next()}
      <Placemark>
        <name>{$model->Current()->FeatId}: {$helper->EscapeForXml($model->Current()->FMTADDRESS)}</name>
        <description><![CDATA[View building report for <a href="http://localhost/mapguide/rest/data/building/{$model->Current()->FeatId}.html">{$helper->EscapeForXml($model->Current()->FMTADDRESS)}</a>]]></description>
        <snippet/>
        <styleUrl>{$helper->GetAssetPath("building/style_property_v1.kml")}#georest-building-style</styleUrl>
        <ExtendedData>
          <SchemaData schemaUrl="{$helper->GetAssetPath("building/schema_property_v1.kml")}#georest-building-schema">
            <SimpleData name="FeatId">{$model->Current()->FeatId}</SimpleData>
            <SimpleData name="FMTADDRESS">{$helper->EscapeForXml($model->Current()->FMTADDRESS)}</SimpleData>
          </SchemaData>
        </ExtendedData>
        <snippet/>
        {$model->Current()->GeometryAsType("Geometry", "GeomKML")}
      </Placemark>
      {/while}
    </Folder>
    <atom:author>      
      <atom:name>mapguide-rest Sample Data</atom:name>    
      <atom:uri>https://github.com/jumpinjackie/mapguide-rest</atom:uri>    
    </atom:author>
  </Document> 
</kml>
