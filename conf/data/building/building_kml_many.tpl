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
        <styleUrl>{$helper->GetAssetPath("building/style_building_v1.kml")}#georest-building-style</styleUrl>
        <snippet/>
        {$model->Current()->GeometryAsType("Geometry", "GeomKML")}
      </Placemark>
      {/while}
    </Folder>
    <atom:author>      
      <atom:name>Melbourne Building Footprints</atom:name>    
      <atom:uri>https://data.melbourne.vic.gov.au/Property-and-Planning/Building-Foot-Prints/qe9w-cym8</atom:uri>    
    </atom:author>
  </Document> 
</kml>
