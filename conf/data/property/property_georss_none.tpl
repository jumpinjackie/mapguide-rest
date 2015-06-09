<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:datetime="http://exslt.org/dates-and-times" xmlns:georss="http://www.georss.org/georss" version="2.0">
  <channel>
    {if $single}
    <title>Property not found</title>
    <description>Property not found</description>
    {else}
    <title>No results</title>
    <description>No results</description>
    {/if}
    <language>en-us</language>
    <docs>https://github.com/jumpinjackie/mapguide-rest</docs>
    <generator>MapGuide REST Extension</generator>
  </channel>
  <item>
    {if $single}
    <title>Property not found</title>
    {else}
    <title>No results</title>
    {/if}
    <description>
      {if $single}
      No property with ID ({$ID}) found
      {else}
      No properties found with the given query
      {/if}
    </description>
  </item>
</rss>