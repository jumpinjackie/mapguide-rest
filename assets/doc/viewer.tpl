<!DOCTYPE html>
<html>
<head>
  <title>{$title}</title>
  <link href='https://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/highlight.default.css' media='screen' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/screen.css' media='screen' rel='stylesheet' type='text/css'/>
  <script src="{$docAssetRoot}/lib/shred.bundle.js" type="text/javascript"></script>
  <script src='{$docAssetRoot}/lib/jquery-1.8.0.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.slideto.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.wiggle.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/handlebars-1.0.0.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/underscore-min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/backbone-min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/swagger.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/swagger-ui.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/highlight.7.3.pack.js' type='text/javascript'></script>
  <script type="text/javascript">
    jQuery(function () {
      var sourceUrl = "{$docUrl}";
      window.swaggerUi = new SwaggerUi({
      url: sourceUrl,
      dom_id: "swagger-ui-container",
      supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
      onComplete: function(swaggerApi, swaggerUi){
        log("Loaded SwaggerUI")
        jQuery('pre code').each(function(i, e) {
          hljs.highlightBlock(e);
        });
      },
      onFailure: function(data) {
        log("Unable to Load SwaggerUI");
      },
      docExpansion: "none"
    });

    jQuery('#input_apiKey').change(function() {
      var key = jQuery('#input_apiKey')[0].value;
      log("key: " + key);
      if(key && key.trim() != "") {
        log("added key " + key);
        window.authorizations.add("key", new ApiKeyAuthorization("api_key", key, "query"));
      }
    })
    window.swaggerUi.load();
  });

  </script>
</head>

<body>
<div id='header'>
  <div class="swagger-ui-wrap">
    <a id="logo" href="index.html">{$title}</a>
  </div>
</div>
<div id="message-bar" class="swagger-ui-wrap">
  &nbsp;
</div>

<div id="swagger-ui-container" class="swagger-ui-wrap">

</div>

</body>

</html>
