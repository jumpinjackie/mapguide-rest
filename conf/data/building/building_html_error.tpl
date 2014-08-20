<!DOCTYPE html>
<html>
    <head>
        <title>Error</title>
        <link rel="stylesheet" href="{$helper->GetAssetPath('common/css/bootstrap.min.css')}" />
    </head>
    <body>
        <div class="container">
            <h3>Error</h3>
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
    </body>
</html>