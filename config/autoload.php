<?php

/**
 * widget_tree_picker extension for Contao Open Source CMS
 *
 * Copyright (C) 2014 Codefog
 *
 * @package widget_tree_picker
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Register the namespace
 */
ClassLoader::addNamespace('TreePicker');


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'TreePicker\TreePickerHelper' => 'system/modules/widget_tree_picker/classes/TreePickerHelper.php',

    // Widgets
    'TreePicker\WidgetTreePicker'   => 'system/modules/widget_tree_picker/widgets/WidgetTreePicker.php',
    'TreePicker\WidgetTreeSelector' => 'system/modules/widget_tree_picker/widgets/WidgetTreeSelector.php',
));
