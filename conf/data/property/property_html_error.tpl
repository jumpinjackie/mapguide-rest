<!DOCTYPE html>
<html>
    <head>
        <title>Error</title>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" />
    </head>
    <body>
        <div class="container">
            <h3>Error</h3>
            <div class="alert alert-danger">
                An unexpected error occured. Full error details below
            </div>
            <pre>{$error->GetDetails()}</pre>
        </div>
    </body>
</html>