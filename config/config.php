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
 * Extension version
 */
@define('WIDGET_TREE_PICKER_VERSION', '1.0');
@define('WIDGET_TREE_PICKER_BUILD', '6');


/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['treePicker']   = 'TreePicker\WidgetTreePicker';
$GLOBALS['BE_FFL']['treeSelector'] = 'TreePicker\WidgetTreeSelector';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][]  = array('TreePicker\TreePickerHelper', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('TreePicker\TreePickerHelper', 'executePostActions');
$GLOBALS['TL_HOOKS']['parseTemplate'][]      = array('TreePicker\TreePickerHelper', 'parseTemplate');
