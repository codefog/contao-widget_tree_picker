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
 * Class TreePickerHelper
 *
 * Provide helper methods to tree picker actions.
 */
class TreePickerHelper extends \Backend
{

    /**
     * Generate item label and return it as HTML string
     * @param object
     * @param string
     * @param object
     * @param string
     * @param mixed
     * @return string
     */
    public static function generateItemLabel($objItem, $strForeignTable, $objDca=null, $strTitleField='', $varCallback=null)
    {
        $args = array();
        $label = '';
        $blnSimple = false;
        $showFields = $GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['fields'];

        // Generate simple label, e.g. for breadcrumb
        if ($strTitleField != '')
        {
            $blnSimple = true;
            $showFields['titleField'] = $strTitleField;
        }

        foreach ($showFields as $k=>$v)
        {
            // Decrypt the value
            if ($GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['eval']['encrypt'])
            {
                $objItem->$v = \Encryption::decrypt(deserialize($objItem->$v));
            }

            if (strpos($v, ':') !== false)
            {
                list($strKey, $strTable) = explode(':', $v);
                list($strTable, $strField) = explode('.', $strTable);

                $objRef = \Database::getInstance()->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
                                         ->limit(1)
                                         ->execute($objItem->$strKey);

                $args[$k] = $objRef->numRows ? $objRef->$strField : '';
            }
            elseif (in_array($GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
            {
                $args[$k] = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $objItem->$v);
            }
            elseif ($GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['eval']['multiple'])
            {
                $args[$k] = ($objItem->$v != '') ? (isset($GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['label'][0]) ? $GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['label'][0] : $v) : '';
            }
            else
            {
                $args[$k] = $GLOBALS['TL_DCA'][$strForeignTable]['fields'][$v]['reference'][$objItem->$v] ?: $objItem->$v;
            }
        }

        $label = vsprintf(((strlen($GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['format'])) ? $GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['format'] : '%s'), $args);

        // Shorten the label if it is too long
        if ($GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['maxCharacters'] < utf8_strlen(strip_tags($label)))
        {
            $label = trim(\String::substrHtml($label, $GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['maxCharacters'])) . ' …';
        }

        $label = preg_replace('/\(\) ?|\[\] ?|\{\} ?|<> ?/', '', $label);

        // Use the default callback if none provided
        if ($varCallback === null)
        {
            $varCallback = $GLOBALS['TL_DCA'][$strForeignTable]['list']['label']['label_callback'];
        }

        // Call the label_callback ($row, $label, $this)
        if (is_array($varCallback))
        {
            $strClass = $varCallback[0];
            $strMethod = $varCallback[1];

            $label = \System::importStatic($strClass)->$strMethod($objItem->row(), $label, $objDca, '', $blnSimple, false);
        }
        elseif (is_callable($varCallback))
        {
            $label = $varCallback($objItem->row(), $label, $objDca, '', $blnSimple, false);
        }
        else
        {
            $label = \Image::getHtml('iconPLAIN.gif') . ' ' . ($blnSimple ? $args['titleField'] : $label);
        }

        return $label;
    }


    /**
     * Display the manager link in the popup window
     * @param object
     */
    public function parseTemplate($objTemplate)
    {
        if (\Input::get('popup') && $objTemplate->getName() == 'be_main' && \Input::get('do') != 'page' && \Input::get('do') != 'files' && $this->Session->get('treePickerRef'))
        {
            $objTemplate->managerHref = $this->Session->get('treePickerRef');
            $objTemplate->manager = $GLOBALS['TL_LANG']['MSC']['treePickerHome'];
        }
    }


    /**
     * Ajax actions that do not require a data container object
     * @param string
     */
    public function executePreActions($strAction)
    {
        switch ($strAction)
        {
            case 'toggleTreepicker':
                $this->strAjaxId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', \Input::post('id'));
                $this->strAjaxKey = str_replace('_' . $this->strAjaxId, '', \Input::post('id'));

                if (\Input::get('act') == 'editAll')
                {
                    $this->strAjaxKey = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $this->strAjaxKey);
                    $this->strAjaxName = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', \Input::post('name'));
                }

                $nodes = $this->Session->get($this->strAjaxKey);
                $nodes[$this->strAjaxId] = intval(\Input::post('state'));
                $this->Session->set($this->strAjaxKey, $nodes);
                exit; break;

            case 'loadTreepicker':
                $this->strAjaxId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', \Input::post('id'));
                $this->strAjaxKey = str_replace('_' . $this->strAjaxId, '', \Input::post('id'));

                if (\Input::get('act') == 'editAll')
                {
                    $this->strAjaxKey = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $this->strAjaxKey);
                    $this->strAjaxName = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', \Input::post('name'));
                }

                $nodes = $this->Session->get($this->strAjaxKey);
                $nodes[$this->strAjaxId] = intval(\Input::post('state'));
                $this->Session->set($this->strAjaxKey, $nodes);
                break;
        }
    }


    /**
     * Ajax actions that do require a data container object
     * @param string
     * @param \DataContainer
     */
    public function executePostActions($strAction, \DataContainer $dc)
    {
        switch ($strAction)
        {
            case 'loadTreepicker':
                $arrData = \Widget::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][\Input::post('name')], \Input::post('name'), null, \Input::post('name'), $dc->table, $dc);
                $arrData['id'] = $this->strAjaxName ?: $dc->id;

                $objWidget = new $GLOBALS['BE_FFL']['treeSelector']($arrData, $dc);
                echo $objWidget->generateAjax($this->strAjaxId, \Input::post('field'), intval(\Input::post('level')));
                exit; break;

            case 'reloadTreepicker':
                $intId = \Input::get('id');
                $strField = $dc->field = \Input::post('name');

                // Handle the keys in "edit multiple" mode
                if (\Input::get('act') == 'editAll')
                {
                    $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]))
                {
                    $this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                $objRow = null;
                $varValue = null;

                // Load the value
                if ($intId > 0 && $this->Database->tableExists($dc->table))
                {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
                                             ->execute($intId);

                    // The record does not exist
                    if ($objRow->numRows < 1)
                    {
                        $this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        die('Bad Request');
                    }

                    $varValue = $objRow->$strField;
                    $dc->activeRecord = $objRow;
                }

                // Call the load_callback
                if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback']))
                {
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback)
                    {
                        if (is_array($callback))
                        {
                            $this->import($callback[0]);
                            $varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
                        }
                        elseif (is_callable($callback))
                        {
                            $varValue = $callback($varValue, $dc);
                        }
                    }
                }

                // Set the new value
                $varValue = \Input::post('value', true);

                // Convert the selected values
                if ($varValue != '')
                {
                    $varValue = trimsplit("\t", $varValue);
                    $varValue = serialize($varValue);
                }

                $objWidget = new $GLOBALS['BE_FFL']['treePicker'](\Widget::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField], $dc->field, $varValue, $strField, $dc->table, $dc));
                echo $objWidget->generate();
                exit; break;
        }
    }
}
