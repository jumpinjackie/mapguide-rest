{*
feature_html_horizontal_body.tpl

Smarty HTML body template for the HTML representation of features in horizontal orientation

Parameters:
 - $model: The template view model parameter, contains the following properties:
    - propertyCount: The number of properties in this feature reader
    - propertyName(index): Gets the name of the property at the given index
    - read(): Advance the feature reader. Returns false if end of reader.
    - getValue(index): Gets the value of reader at the given index
    - endOfReader(): Returns true if we've reached end of the reader
*}
        <table class="table table-bordered table-condensed table-hover">
            <!-- Table header -->
            <tr>
            {for $i = 0 to $model->propertyCount - 1}
                <th>{$model->propertyName($i)}</th>
            {/for}
            </tr>
            {while $model->read()}
            <tr>
                {for $i = 0 to $model->propertyCount - 1}
                <td>{$model->getValue($i)}</td>
                {/for}
            </tr>
            {/while}
        </table>
        {if $model->endOfReader()}
        <span class="end-of-reader"></span>
        {/if}