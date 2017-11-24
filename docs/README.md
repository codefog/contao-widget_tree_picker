# Widget Tree Picker – Documentation

## Usage example

**This widget works only with DCAs that have sorting mode set to 5 (tree)!**

The following options can be set in the evalution array:

Property | Description
--- | ---
foreignTable | Source table.
titleField | Field used e.g. in the breadcrumb.
searchField | Field to be searched by (enables search bar).
managerHref | Link to the manager in the popup.
fieldType | Field type.
selectParents | If the field type is checkbox, parent records will be automatically selected when checking the child record.
multiple | Must be specified for checkbox.
pickerCallback | You can use a custom callback to modify records displayed in the picker. The parameters are exactly the same as in the default "`label_callback`".

For example implementation see below code:

```php
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude'   => true,
    'inputType' => 'treePicker',
    'eval'      => array
    (
        'foreignTable'   => 'tl_news_category',
        'titleField'     => 'title',
        'searchField'    => 'title',
        'managerHref'    => 'do=news&table=tl_news_category',
        'fieldType'      => 'checkbox',
        'selectParents'  => true,
        'multiple'       => true,
        'pickerCallback' => function($row) {
            return $row['title'] . ' [' . $row['id'] . ']';
        }
    ),
    'sql'       => "blob NULL"
);
```