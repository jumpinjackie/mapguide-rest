<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:datetime="http://exslt.org/dates-and-times" xmlns:georss="http://www.georss.org/georss" version="2.0">
  <channel>
    <title>Error</title>
    <description>Error Details</description>
    <language>en-us</language>
    <docs>https://github.com/jumpinjackie/mapguide-rest</docs>
    <generator>MapGuide REST Extension</generator>
  </channel>
  <item>
    <title>Error</title>
    <description>
      An unexpected error occured. Full error details below:

      <p>Code</p>
      <pre>{$error->code}</pre>
      <p>Message</p>
      <pre>{$error->message}</pre>
      <p>Stack Trace</p>
      <pre>{$error->stack}</pre>
    </description>
  </item>
</rss>