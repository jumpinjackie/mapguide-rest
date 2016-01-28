<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>mapguide-rest API Reference</title>
  <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32" />
  <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16" />
  <link href='{$docAssetRoot}/css/typography.css' media='screen' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/reset.css' media='screen' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/screen.css' media='screen' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/reset.css' media='print' rel='stylesheet' type='text/css'/>
  <link href='{$docAssetRoot}/css/print.css' media='print' rel='stylesheet' type='text/css'/>
  <script src='{$docAssetRoot}/lib/jquery-1.8.0.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.slideto.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.wiggle.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/handlebars-2.0.0.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/underscore-min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/backbone-min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/swagger-ui.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/highlight.7.3.pack.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/jsoneditor.min.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/marked.js' type='text/javascript'></script>
  <script src='{$docAssetRoot}/lib/swagger-oauth.js' type='text/javascript'></script>

  <!-- Some basic translations -->
  <!-- <script src='{$docAssetRoot}/lang/translator.js' type='text/javascript'></script> -->
  <!-- <script src='{$docAssetRoot}/lang/ru.js' type='text/javascript'></script> -->
  <!-- <script src='{$docAssetRoot}/lang/en.js' type='text/javascript'></script> -->

  <script type="text/javascript">
    $(function () {
      var url = "{$docUrl}";

      // Pre load translate...
      if(window.SwaggerTranslator) {
        window.SwaggerTranslator.translate();
      }
      window.swaggerUi = new SwaggerUi({
        url: url,
        dom_id: "swagger-ui-container",
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
        onComplete: function(swaggerApi, swaggerUi){
          if(typeof initOAuth == "function") {
            initOAuth({
              clientId: "your-client-id",
              clientSecret: "your-client-secret-if-required",
              realm: "your-realms",
              appName: "your-app-name", 
              scopeSeparator: ",",
              additionalQueryStringParams: {}
            });
          }

          if(window.SwaggerTranslator) {
            window.SwaggerTranslator.translate();
          }

          $('pre code').each(function(i, e) {
            hljs.highlightBlock(e)
          });
        },
        onFailure: function(data) {
          log("Unable to Load SwaggerUI");
        },
        docExpansion: "none",
        jsonEditor: false,
        apisSorter: "alpha",
        defaultModelRendering: 'schema',
        showRequestHeaders: true
      });

      window.swaggerUi.load();

      function log() {
        if ('console' in window) {
          console.log.apply(console, arguments);
        }
      }
  });
  </script>
</head>

<body class="swagger-section">
<div id='header'>
  <div class="swagger-ui-wrap">
    <a id="logo" href="index.html">mapguide-rest API Reference</a>
  </div>
</div>

<div id="message-bar" class="swagger-ui-wrap" data-sw-translate>&nbsp;</div>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
</body>
</html>