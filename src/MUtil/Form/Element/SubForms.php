<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 * Show a repeating subform repeated for the number of rows set for this item
 * when rendered.
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class SubForms extends \Zend_Form_Element_Xhtml implements \MUtil\Form\Element\SubFocusInterface
{
    /**
     * Use no view helper by default
     *
     * @var string
     */
    public $helper = 'Form';

    /**
     * SubFormsis an array of values by default
     *
     * @var bool
     */
    protected $_isArray = true;

    /**
     * The model sub form all others are copied from
     *
     * @var \Zend_Form
     */
    protected $_subForm;

    /**
     * Actual clones of form
     *
     * @var array of \Zend_Form
     */
    protected $_subForms;

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - \Zend_Config: \Zend_Config with options for configuring element
     *
     * @param  string|array|\Zend_Config $spec
     * @return void
     * @throws \Zend_Form_Exception if no element name after initialization
     */
    public function __construct(\Zend_Form $subForm, $spec, $options = null)
    {
        $this->setSubForm($subForm);

        parent::__construct($spec, $options);
    }

    /**
     * Get the (possibly focusing) elements/dispalygroups/form contained by this element
     *
     * return array of elements or subforms
     */
    public function getSubFocusElements()
    {
        // If the subforms have been initialezed return them, otherwise return the (later cloned) parent form
        if ($this->_subForms) {
            return $this->_subForms;
        }

        return $this->_subForm;
    }

    /**
     * Get the (base) form cloned for each repetition
     *
     * @return \Zend_Form
     */
    public function getSubForm()
    {
        return $this->_subForm;
    }

    /**
     * Get the (actual) cloned, repeated forms
     *
     * @return array of \Zend_Form
     */
    public function getSubForms()
    {
        return $this->_subForms;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $valid = parent::isValid($value, $context);

        // Subforms are set bet setValue() called by parent::isValid()
        if ($this->_subForms) {
            foreach ($value as $id => $data) {
                $valid = $this->_subForms[$id]->isValid($data) && $valid;
            }
        }

        return $valid;
    }

    /**
     * Load default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Subforms');
        }
    }

    /**
     * Change  the sub form later
     *
     * @param \Zend_Form $subForm
     * @return \MUtil\Form\Element\SubForms (continuation pattern)
     */
    public function setSubForm(\Zend_Form $subForm)
    {
        $this->_subForm = $subForm;
        return $this;
    }

    /**
     * Set element value
     *
     * @param  mixed $value
     * @return \MUtil\Form\Element\SubForms (continuation pattern)
     */
    public function setValue($value)
    {
        // $this->setElementsBelongTo($this->getName());
        if ($this->_subForm && $value) {
            $this->_subForm->setElementsBelongTo($this->getName());

            foreach ($value as $id => $row) {

                if (isset($this->_subForms[$id])) {
                    $this->_subForms[$id]->populate($row);

                } else {
                    $subForm = clone $this->_subForm;

                    $name = $this->getName() . '[' . $id . ']';
                    $subForm->setElementsBelongTo($name);
                    $subForm->setName($name);
                    $subForm->populate($row);

                    $this->_subForms[$id] = $subForm;
                }
            }
        }

        return parent::setValue($value);
    }
}
