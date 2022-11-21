<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Dependency;

/**
 * Set attributes when the dependent attribute is on - remove them otherwise
 *
 * @package    MUtil
 * @subpackage OnOffElementsDependency
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7 27-apr-2015 18:50:11
 */
class OnOffElementsDependency extends DependencyAbstract
{
    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    protected $model;
    
    /**
     *
     * @var array
     */
    protected $modeOff;

    /**
     *
     * @var array
     */
    protected $modeOn;

    /**
     * Should we just submit on click or use jQuery
     *
     * @var boolean
     */
    protected $submit = false;

    /**
     * Constructor checks any subclass set variables
     *
     * @param string $onElement The element that switches the other fields on or off
     * @param array|string $forElements The elements switched on or off
     * @param array|string $mode The values set ON when $onElement is true
     * @param \MUtil\Model\ModelAbstract $model The model
     */
    public function __construct($onElement, $forElements, $mode = 'readonly', $model = null)
    {
        $this->setDependsOn($onElement);

        if (is_array($mode)) {
            $this->modeOn = $mode;
        } else {
            $this->modeOn = array($mode => $mode);
        }
        $keys = array_keys($this->modeOn);
        $this->modeOff = array_fill_keys($keys, null);

        $this->_defaultEffects = array_combine($keys, $keys);

        $this->setEffecteds((array) $forElements);

        $this->addEffected($onElement, 'onchange');
        
        $this->model = $model;
    }

    /**
     * Returns the changes that must be made in an array consisting of
     *
     * <code>
     * array(
     *  field1 => array(setting1 => $value1, setting2 => $value2, ...),
     *  field2 => array(setting3 => $value3, setting4 => $value4, ...),
     * </code>
     *
     * By using [] array notation in the setting name you can append to existing
     * values.
     *
     * Use the setting 'value' to change a value in the original data.
     *
     * When a 'model' setting is set, the workings cascade.
     *
     * @param array $context The current data this object is dependent on
     * @param boolean $new True when the item is a new record not yet saved
     * @return array name => array(setting => value)
     */
    public function getChanges(array $context, bool $new = false): array
    {
        $dependsOns = $this->getDependsOn();
        $dependsOn  = reset($dependsOns);

        $effecteds = array_keys($this->getEffecteds());
        array_pop($effecteds); // Remove $dependsOn

        if (isset($context[$dependsOn]) && $context[$dependsOn]) {
            $value = $this->modeOn;
        } else {
            $value = $this->modeOff;
        }
        $output = array_fill_keys($effecteds, $value);

        if ($this->submit) {
            $javaScript = 'this.form.submit();';
        } else {
            $setScript = '';
            foreach ($this->modeOn as $key => $value) {
                if ($value) {
                    $valueOn = "e.setAttribute('$key', '$value');";
                } else {
                    $valueOn = "e.removeAttribute('$key');";
                }
                if (isset($this->modeOff[$key])) {
                    $val      = $this->modeOff[$key];
                    $valueOff = "e.setAttribute('$key', '$val');";
                } else {
                    $valueOff = "e.removeAttribute('$key');";
                }
                
                $this->checkPicker($valueOn, $valueOff);
                
                $setScript .= "if (this.value != 0) { $valueOn } else { $valueOff }; ";
            }
            $javaScript = '';
            foreach ($effecteds as $field) {
                $javaScript = "e = document.getElementById('$field'); " . $setScript;
            }
        }
        $output[$dependsOn]['onchange'] = $javaScript;

        return $output;
    }  // */
    
    /**
     * Checks and updates the on and off strings when one of the effecteds is a date, time or datetime field
     * 
     * @param string $valueOn
     * @param string $valueOff
     */
    protected function checkPicker(&$valueOn, &$valueOff)
    {
        $effecteds = array_keys($this->getEffecteds());
        foreach ($effecteds as $field) {
            if ($this->model instanceof \MUtil\Model\ModelAbstract && $this->model->has($field)) {
                $modelItemType = $this->model->get($field, 'type');
                $dateFormat = $this->model->get($field, 'dateFormat');
                $timeFormat = $this->model->get($field, 'timeFormat');

                switch ($modelItemType) {
                    case \MUtil\Model::TYPE_DATE:
                    case \MUtil\Model::TYPE_TIME:
                    case \MUtil\Model::TYPE_DATETIME:
                        $picker = 'datepicker';
                        break;

                    default:
                        $picker = '';
                        break;
                }
                
                if (!empty($picker)) {
                    // If none set, get the locale default dateformat
                    if ((!$dateFormat) && (!$timeFormat) && \Zend_Registry::isRegistered('Zend_Locale')) {
                        $dateFormat = \ZendX_JQuery_View_Helper_DatePicker::resolveZendLocaleToDatePickerFormat();
                    }
                    if ($dateFormat) {
                        if ($timeFormat) {
                            $picker  = 'datetimepicker';
                        }
                    } elseif ($timeFormat) {
                        $picker  = 'timepicker';
                    }
                    $valueOn .= "$('#$field').$picker('enable');";
                    $valueOff .= "$('#$field').$picker('disable');";
                }
            }
        }
    }
}
