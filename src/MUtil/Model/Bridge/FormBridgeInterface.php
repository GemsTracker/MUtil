<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Bridge;

use Zalt\Model\Data\DataReaderInterface;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 8-mei-2014 11:12:40
 */
interface FormBridgeInterface extends \MUtil\Model\Bridge\BridgeInterface
{
    public function add($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addColorPicker($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addCheckbox($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Add a ZendX date picker to the form
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \ZendX_JQuery_Form_Element_DatePicker
     */
    public function addDate($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Adds a displayGroup to the bridge
     *
     * Use a description to set a label for the group. All elements should be added to the bridge before adding
     * them to the group. Use the special option showLabels to display the labels of the individual fields
     * in front of them. This option is only available in tabbed forms, to display multiple fields in one tablecell.
     *
     * Without labels:
     * usage: $this->addDisplayGroup('mygroup', array('element1', 'element2'), 'description', 'Pretty name for the group');
     *
     * With labels:
     * usage: $this->addDisplayGroup('mygroup', array('element1', 'element2'), 'description', 'Pretty name for the group', 'showLabels', true);
     *
     * Or specify using the 'elements' option:
     * usage: $this->addDisplayGroup('mygroup', array('elements', array('element1', 'element2'), 'description', 'Pretty name for the group'));
     *
     * @param string $name Name of element
     * @param array $elements or \MUtil\Ra::pairs() name => value array with 'elements' item in it
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \Zend_Form_Displaygroup
     */
    public function addDisplayGroup($name, $elements, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addElement(\Zend_Form_Element $element);

    /**
     * Add an element that just displays the value to the user
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Form\Element\Exhibitor
     */
    public function addExhibitor($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Add an element that just displays the value to the user
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Form\Element\FakeSubmit
     */
    public function addFakeSubmit($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addFile($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addFilter($name, $filter, $options = array());

    /**
     * Adds a form multiple times in a table
     *
     * You can add your own 'form' either to the model or here in the parameters.
     * Otherwise a form of the same class as the parent form will be created.
     *
     * All elements not yet added to the form are added using a new FormBridge
     * instance using the default label / non-label distinction.
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Form\Element\Table
     */
    public function addFormTable($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addHidden($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addHiddenMulti($name_args);

    public function addHtml($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addList($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Adds a group of checkboxes (multicheckbox)
     *
     * @see \Zend_Form_Element_MultiCheckbox
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \Zend_Form_Element_MultiCheckbox
     */
    public function addMultiCheckbox($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Adds a select box with multiple options
     *
     * @see \Zend_Form_Element_Multiselect
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     */
    public function addMultiSelect($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Stub for elements where no class should be displayed.
     *
     * @param string $name Name of element
     */
    public function addNone($name);

    public function addPassword($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addRadio($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addSelect($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Adds a form multiple times in a table
     *
     * You can add your own 'form' either to the model or here in the parameters.
     * Otherwise a form of the same class as the parent form will be created.
     *
     * All elements not yet added to the form are added using a new FormBridge
     * instance using the default label / non-label distinction.
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Form\Element\Table
     */
    public function addSubForm($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * Start a tab after this element, with the given name / title
     *
     * Can ofcourse only be used in tabbed forms.
     *
     * Usage:
     * <code>
     * $this->addTab('tab1')->h3('First tab');
     * </code>
     * or
     * <code>
     * $this->addTab('tab1', 'value', 'First tab');
     * </code>
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Form\Element\Tab
     */
    public function addTab($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addText($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    public function addTextarea($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     * @param string $name
     * @param mixed $arrayOrKey1 \MUtil\Ra::pairs() name => value array
     * @return \MUtil\Bootstrap\Form\Element\ToggleCheckboxes
     * @throws \Zend_Form_Exception
     */
    public function addToggleCheckboxes($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null);

    /**
     *
     * @param sting $elementName
     * @param mixed $validator
     * @param boolean $breakChainOnFailure
     * @param mixed $options
     * @return \MUtil\Model\Bridge\FormBridge
     */
    public function addValidator($elementName, $validator, $breakChainOnFailure = false, $options = array());

    /**
     * Returns the allowed options for a certain key or all options if no
     * key specified
     *
     * @param string $key
     * @return array
     */
    public function getAllowedOptions($key = null);

    /**
     *
     * @return \Zend_Form
     */
    public function getForm();

    /**
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    public function getModel(): DataReaderInterface;

    /**
     * Retrieve a tab from a \Gems\TabForm to add extra content to it
     *
     * @param string $name
     * @return \Gems\Form\TabSubForm
     */
    public function getTab($name);

    /**
     * Set the allowed options for a certain key to the specified options array
     *
     * @param string $key
     * @param array $options
     * @return \MUtil\Model\Bridge\FormBridge
     */
    public function setAllowedOptions($key, $options);
 }
