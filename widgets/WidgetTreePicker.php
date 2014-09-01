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
 * Class WidgetTreePicker
 *
 * Provide methods to handle input field "tree picker".
 */
class WidgetTreePicker extends \Widget
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Order ID
     * @var string
     */
    protected $strOrderId;

    /**
     * Order name
     * @var string
     */
    protected $strOrderName;

    /**
     * Order field
     * @var string
     */
    protected $strOrderField;

    /**
     * Multiple flag
     * @var boolean
     */
    protected $blnIsMultiple = false;


    /**
     * Load the database object
     * @param array
     * @throws \Exception
     */
    public function __construct($arrAttributes=null)
    {
        $this->import('Database');
        parent::__construct($arrAttributes);

        if (!$this->foreignTable)
        {
            throw new \Exception('The foreign table is not specified');
        }

        $this->strOrderField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['orderField'];
        $this->blnIsMultiple = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'];

        // Prepare the order field
        if ($this->strOrderField != '')
        {
            $this->strOrderId = $this->strOrderField . str_replace($this->strField, '', $this->strId);
            $this->strOrderName = $this->strOrderField . str_replace($this->strField, '', $this->strName);

            // Retrieve the order value
            $objRow = $this->Database->prepare("SELECT {$this->strOrderField} FROM {$this->strTable} WHERE id=?")
                           ->limit(1)
                           ->execute($this->activeRecord->id);

            $tmp = deserialize($objRow->{$this->strOrderField});
            $this->{$this->strOrderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : array();
        }

        $this->loadDataContainer($this->foreignTable);
        \System::loadLanguageFile($this->foreignTable);

        // Add the scripts
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/widget_tree_picker/assets/treepicker.js';
    }


    /**
     * Return an array if the "multiple" attribute is set
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        // Store the order value
        if ($this->strOrderField != '')
        {
            $arrNew = explode(',', \Input::post($this->strOrderName));

            // Only proceed if the value has changed
            if ($arrNew !== $this->{$this->strOrderField})
            {
                $this->Database->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->strOrderField}=? WHERE id=?")
                               ->execute(time(), serialize($arrNew), $this->activeRecord->id);

                $this->objDca->createNewVersion = true;
            }
        }

        // Return the value as usual
        if ($varInput == '')
        {
            if ($this->mandatory)
            {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }

            return '';
        }
        elseif (strpos($varInput, ',') === false)
        {
            return $this->blnIsMultiple ? array($varInput) : $varInput;
        }
        else
        {
            $arrValue = array_filter(explode(',', $varInput));
            return $this->blnIsMultiple ? $arrValue : $arrValue[0];
        }
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $arrSet = array();
        $arrValues = array();
        $blnHasOrder = ($this->strOrderField != '' && is_array($this->{$this->strOrderField}));

        if (is_array($this->varValue) && !empty($this->varValue))
        {
            $objItems = $this->Database->execute("SELECT * FROM " . $this->foreignTable . " WHERE id IN (" . implode(',', array_map('intval', $this->varValue)) . ") ORDER BY sorting");

            if ($objItems !== null)
            {
                while ($objItems->next())
                {
                    $arrSet[] = $objItems->id;
                    $arrValues[$objItems->id] = TreePickerHelper::generateItemLabel($objItems, $this->foreignTable, $this->objDca, null, $this->pickerCallback);
                }
            }

            // Apply a custom sort order
            if ($blnHasOrder)
            {
                $arrNew = array();

                foreach ($this->{$this->strOrderField} as $i)
                {
                    if (isset($arrValues[$i]))
                    {
                        $arrNew[$i] = $arrValues[$i];
                        unset($arrValues[$i]);
                    }
                }

                if (!empty($arrValues))
                {
                    foreach ($arrValues as $k=>$v)
                    {
                        $arrNew[$k] = $v;
                    }
                }

                $arrValues = $arrNew;
                unset($arrNew);
            }
        }

        // Load the fonts for the drag hint
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        $return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.implode(',', $arrSet).'">' . ($blnHasOrder ? '
  <input type="hidden" name="'.$this->strOrderName.'" id="ctrl_'.$this->strOrderId.'" value="'.$this->{$this->strOrderField}.'">' : '') . '
  <div class="selector_container">' . (($blnHasOrder && count($arrValues)) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_'.$this->strId.'" class="'.($blnHasOrder ? 'sortable' : '').'">';

        foreach ($arrValues as $k=>$v)
        {
            $return .= '<li data-id="'.$k.'">'.$v.'</li>';
        }

        $return .= '</ul>
    <p><a href="system/modules/widget_tree_picker/public/treepicker.php?do='.\Input::get('do').'&amp;table='.$this->strTable.'&amp;field='.$this->strField.'&amp;act=show&amp;id='.$this->activeRecord->id.'&amp;value='.implode(',', $arrSet).'&amp;rt='.REQUEST_TOKEN.'" class="tl_submit" onclick="Backend.getScrollOffset();TreePicker.openModal({\'width\':765,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']).'\',\'url\':this.href,\'id\':\''.$this->strId.'\'});return false">'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</a></p>' . ($blnHasOrder ? '
    <script>Backend.makeMultiSrcSortable("sort_'.$this->strId.'", "ctrl_'.$this->strOrderId.'")</script>' : '') . '
  </div>';

        if (!\Environment::get('isAjaxRequest'))
        {
            $return = '<div>' . $return . '</div>';
        }

        return $return;
    }
}
