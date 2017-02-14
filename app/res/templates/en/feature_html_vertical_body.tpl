{*
feature_html_vertical_body.tpl

Smarty HTML body template for the HTML representation of features in vertical orientation

Parameters:
 - $model: The template view model parameter, contains the following properties:
    - propertyCount: The number of properties in this feature reader
    - propertyName(index): Gets the name of the property at the given index
    - read(): Advance the feature reader. Returns false if end of reader.
    - getValue(index): Gets the value of reader at the given index
    - endOfReader(): Returns true if we've reached end of the reader
*}
        {while $model->read()}
        <table class="table table-bordered table-condensed table-hover">
            <!-- Table header -->
            {for $i = 0 to $model->propertyCount - 1}
            <tr>
                <td><strong>{$model->propertyName($i)}</strong></td>
                <td>{$model->getValue($i)|unescape:"html"}</td>
            </tr>
            {/for}
        </table>
        {/while}
        {if $model->endOfReader()}
        <span class="end-of-reader"></span>
        {/if}