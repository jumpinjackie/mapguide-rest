<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:georss="http://www.georss.org/georss"
      xmlns:gml="http://www.opengis.net/gml">
  <title>Error</title>
  <entry>
    <title>Error</title>
    <summary type="xhtml">
      <div xmlns="http://www.w3.org/1999/xhtml">
        <div class="alert alert-danger">
            An unexpected error occured. Full error details below
        </div>
        <p>Code</p>
        <pre>{$error->code}</pre>
        <p>Message</p>
        <pre>{$error->message}</pre>
        <p>Stack Trace</p>
        <pre>{$error->stack}</pre>
      </div>
    </summary>
  </entry>
</feed>