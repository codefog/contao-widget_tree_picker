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

namespace TreePicker;

/**
 * Set the script name
 */
define('TL_SCRIPT', 'system/modules/widget_tree_picker/public/treepicker.php');


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');

// Include the Contao initialization script (see #9)
if (file_exists('../../../initialize.php')) {
    // Regular way
    /** @noinspection PhpIncludeInspection */
    require_once '../../../initialize.php';
} elseif (file_exists('../../../../system/initialize.php')) {
    // Contao 4 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../system/initialize.php';
} else {
    // Contao 3 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../../system/initialize.php';
}

/**
 * Class TreePicker
 *
 * Back end tree picker.
 */
class TreePicker extends \Backend
{

    /**
     * Current Ajax object
     * @var object
     */
    protected $objAjax;


    /**
     * Initialize the controller
     *
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();

        $this->User->authenticate();
        \System::loadLanguageFile('default');
    }


    /**
     * Run the controller and parse the template
     */
    public function run()
    {
        $this->Template = new \BackendTemplate('be_picker');
        $this->Template->main = '';

        // Ajax request
        if ($_POST && \Environment::get('isAjaxRequest'))
        {
            $this->objAjax = new \Ajax(\Input::post('action'));
            $this->objAjax->executePreActions();
        }

        $strTable = \Input::get('table');
        $strField = \Input::get('field');

        // Define the current ID
        define('CURRENT_ID', (\Input::get('table') ? $this->Session->get('CURRENT_ID') : \Input::get('id')));

        $this->loadDataContainer($strTable);
        $strDriver = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
        $objDca = new $strDriver($strTable);

        // AJAX request
        if ($_POST && \Environment::get('isAjaxRequest'))
        {
            $this->objAjax->executePostActions($objDca);
        }

        $this->Session->set('treePickerRef', \Environment::get('request'));

        $objTreeSelector = new $GLOBALS['BE_FFL']['treeSelector'](\Widget::getAttributesFromDca($GLOBALS['TL_DCA'][$strTable]['fields'][$strField], $strField, array_filter(explode(',', \Input::get('value'))), $strField, $strTable, $objDca));

        $this->Template->main = $objTreeSelector->generate();
        $this->Template->theme = \Backend::getTheme();
        $this->Template->base = \Environment::get('base');
        $this->Template->language = $GLOBALS['TL_LANGUAGE'];
        $this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']);
        $this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->Template->addSearch = $objTreeSelector->searchField;
        $this->Template->search = $GLOBALS['TL_LANG']['MSC']['search'];
        $this->Template->action = ampersand(\Environment::get('request'));
        $this->Template->value = $this->Session->get($objTreeSelector->getSearchSessionKey());
        $this->Template->manager = $GLOBALS['TL_LANG']['MSC']['treepickerManager'];
        $this->Template->breadcrumb = $GLOBALS['TL_DCA'][$objTreeSelector->foreignTable]['list']['sorting']['breadcrumb'];
        $this->Template->managerHref = '';

        // Add the manager link
        if ($objTreeSelector->managerHref)
        {
            $this->Template->managerHref = 'contao/main.php?' . ampersand($objTreeSelector->managerHref) . '&amp;popup=1&amp;wtp=1';
        }

        $GLOBALS['TL_CONFIG']['debugMode'] = false;

        echo $this->Template->parse();
        exit;
    }
}


/**
 * Instantiate the controller
 */
$objTreePicker = new TreePicker();
$objTreePicker->run();
