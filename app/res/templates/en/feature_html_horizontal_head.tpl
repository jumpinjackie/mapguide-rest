{*  
feature_html_horizontal_head.tpl

Smarty HTML header template for the HTML representation of features in horizontal orientation

Parameters:
 - $model: The template view model parameter, contains the following properties:
  - baseUrl: The mapguide-rest root URL
  - className: The Feature Class Name
  - maxPages: The maximum number of pages
  - isPaginated: true if this reader is paginated. If false, the parameters below will not be valid for use
  - pageNo: The current page number. 
  - total: The total number of features. -1 if unknown
  - hasMorePages: true if there are more "pages" of data in this set of features
  - prevPageUrl: The URL to the "previous" page of features
  - nextPageUrl: The URL to the "next" page of features
*}
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" href="{$model->baseUrl}/assets/common/css/bootstrap.min.css" />
        <script type="text/javascript" src="{$model->baseUrl}/assets/common/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript">
          $(function() {
            //This is a little hack to ensure that the user doesn't advance to a page that doesn't exist
            //If the body template reaches the end of reader, it will insert a .end-of-reader element. We
            //simply check if that element exists, if it does we remove the "next page" link
            var el = $(".end-of-reader");
            if (el.length > 0)
              $(".next-page").remove();
          });
        </script>
    </head>
    <body>
        <div class="pull-left">
            <div>
                <strong>{$model->className}</strong>
                {if $model->maxPages gte 0}
                <strong>(Page {$model->pageNo} of {$model->maxPages})</strong>
                {else}
                <strong>(Page {$model->pageNo})</strong>
                {/if}
            </div>
            {if $model->total gte 0}
            <span>{$model->total} features</span>
            {/if}
            {if $model->isPaginated}
              {if $model->pageNo gt 1}
              <a href="{$model->prevPageUrl}">&lt;&lt;&nbsp;Prev</a>&nbsp;
              {/if}
              {if $model->hasMorePages}
              <span class="next-page">
                {if $model->pageNo gt 1}
                |&nbsp;
                {/if}
                <a href="{$model->nextPageUrl}">Next&nbsp;&gt;&gt;</a>
              </span>
              {/if}
            {/if}
        </div>