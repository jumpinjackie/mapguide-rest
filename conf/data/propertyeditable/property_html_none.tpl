<!DOCTYPE html>
<html>
    <head>
        {if $single}
        <title>Property not found</title>
        {else}
        <title>No results</title>
        {/if}
        <link rel="stylesheet" href="{$helper->GetAssetPath('common/css/bootstrap.min.css')}" />
    </head>
    <body>
        <div class="container">
            {if $single}
            <h3>Property not found</h3>
            {else}
            <h3>No results</h3>
            {/if}
            <div class="alert alert-danger">
                {if $single}
                No property with ID ({$ID}) found
                {else}
                No properties found with the given query
                {/if}
            </div>
        </div>
    </body>
</html>