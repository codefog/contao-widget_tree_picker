widget_tree_picker Contao Extension
===================================

Contao widget that behaves similar to the page or file picker but allows you to choose any table as the source.

Usage example:

```php
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude'                 => true,
    'inputType'               => 'treePicker',
    'eval'                    => array
    (
        'foreignTable' => 'tl_news_category', // Source table
        'titleField' => 'title', // Field used e.g. in the breadcrumb
        'searchField' => 'title', // Field to be searched by (enables search bar)
        'managerHref' => 'do=news&table=tl_news_category', // Link to the manager in the popup
        'fieldType' => 'checkbox', // Field type
        'multiple' => true, // Must be specified for checkbox

        // You can use a custom callback to modify records displayed in the picker
        // The callback parameters are exactly the same as in the default "label_callback"
        'pickerCallback' => function($row) {
            return $row['title'] . ' [' . $row['id'] . ']';
        }
    ),
    'sql'                     => "blob NULL"
);
```

### Contao compatibility
- Contao 3.2

### Available languages
- English

### Support us
We put a lot of effort to make our extensions useful and reliable. If you like our work, please support us by liking our [Facebook profile](http://facebook.com/Codefog), following us on [Twitter](https://twitter.com/codefog) and watching our [Github activities](http://github.com/codefog). Thank you!

### Copyright
The extension was developed by [Codefog](http://codefog.pl) and is distributed under the Lesser General Public License (LGPL). Feel free to contact us using the [website](http://codefog.pl) or directly at info@codefog.pl.