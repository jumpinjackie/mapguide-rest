{*
feature_html_horizontal_foot.tpl

Smarty HTML footer template for the HTML representation of features in horizontal orientation

Parameters:
 - $model: The template view model parameter, contains the following properties:
  - baseUrl: The mapguide-rest root URL
  - className: The Feature Class Name
  - maxPages: The maximum number of pages
  - pageNo: The current page number
  - total: The total number of features. -1 if unknown
  - hasMorePages: true if there are more "pages" of data in this set of features
  - prevPageUrl: The URL to the "previous" page of features
  - nextPageUrl: The URL to the "next" page of features
*}
    </body>
</html>