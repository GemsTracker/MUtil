<?php


/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 * Repeats all elements in a form, so a form can be used as source for e.g. an html element.
 *
 * Allows splitting of all hidden fields and flattening the form.
 *
 * Splitting the hidden fields to a separate repeater makes sure they don't mess up
 * your layout by appearing between fields.
 *
 * Flattening the form enables you treat nested forms as if they are part of
 * the main form.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatableFormElements extends \MUtil\Lazy\Repeatable
{
    /**
     * Enable access to the elements in this repeater using $this->element
     *
     * @var \MUtil\Lazy\LazyAbstract
     */
    public $element;

    /**
     * Flatten all sub forms into the main form
     *
     * @var boolean
     */
    public $flattenSubs = false;

    /**
     * Output the hidden fields to a separate location
     *
     * @var boolean
     */
    public $splitHidden = false;

    /**
     * Enable access to the elements in this repeater using $this->element
     *
     * @var \MUtil\Lazy\LazyAbstract
     */
    public $label;

    /**
     * Storage of the hidden elements
     *
     * @var array
     */
    private $_hidden_elements;

    /**
     * Construct the element repeater
     *
     * @param \Zend_Form $form
     */
    public function __construct(\Zend_Form $form)
    {
        parent::__construct($form);

        // Enable access to the elements in this repeater using:
        // $this->element and $this->label.
        //
        // The other access method is: $this->{name of element renderer}
        $this->element =  new \MUtil\Lazy\Call(array($this, '__current'));
        $this->label   = \MUtil\Html::create('label', $this->element);
    }

    /**
     * Get a Lazy call to the current element's decorator output or property
     * output if the decorator does not exist
     *
     * @param string $name
     * @return \MUtil\Lazy\Call
     */
    public function __get($name)
    {
        // Form elements have few public properties, so usually we use this as a
        // shortcut for a decorator function, however, if the property exists
        // (and no Decorator with the same name exists) the property value will
        // be returned.
        return \MUtil\Lazy::call(array($this, 'getDecorator'), ucfirst($name));
    }

    /**
     * Return the core data in the Repeatable in one go
     *
     * @return \Iterator|array
     */
    public function __getRepeatable()
    {
        $elements = iterator_to_array(parent::__getRepeatable());

        if ($this->flattenSubs) {
            $newElements = array();
            foreach ($elements as $element) {
                $this->_flattenElement($element, $newElements);
            }
            $elements = $newElements;
        }

        if ($this->splitHidden) {
            $filteredElements = array();
            $this->_hidden_elements = array();

            foreach ($elements as $element) {
                if (($element instanceof \Zend_Form_Element_Hidden) || ($element instanceof \Zend_Form_Element_Hash)) {
                    $this->_hidden_elements[] = $element;
                } else {
                    $filteredElements[] = $element;
                }
            }

            return $filteredElements;

        } else {
            $this->_hidden_elements = array();
            return $elements;
        }
    }

    /**
     * Flatten element depending on it's type
     *
     * @param mixed $element
     * @param array $newElements
     */
    private function _flattenElement($element, array &$newElements)
    {
        if ($element instanceof \Zend_Form) {
            $this->_flattenForm($element, $newElements);

        } elseif ($element instanceof \MUtil\Form\Element\SubFocusInterface) {
            foreach ($element->getSubFocusElements() as $sub) {
                $this->_flattenElement($sub, $newElements);
            }

        } else {
            $newElements[] = $element;
        }
    }

    /**
     * Flatten al elements in the form
     *
     * @param \Zend_Form $form
     * @param array $newElements
     */
    private function _flattenForm(\Zend_Form $form, array &$newElements)
    {
        foreach ($form as $id => $element) {
            $this->_flattenElement($element, $newElements);
        }
    }

    /**
     * Get the current element's decorator output or property output if the decorator does not exist
     *
     * @param string $name
     * @return \MUtil\Html\Raw|null
     */
    public function getDecorator($name)
    {
        if ($current = $this->__current()) {
            if ($decorator = $current->getDecorator($name)) {
                $decorator->setElement($current);
                return new \MUtil\Html\Raw($decorator->render(''));
            }

            if (property_exists($current, $name)) {
                return $current->$name;
            }
        }

        return null;
    }

    /**
     * Are the sub forms split off?
     *
     * @return boolean
     */
    public function getFlattenSubs()
    {
        return $this->flattenSubs;
    }

    /**
     * An array containing all the hidden elements
     *
     * @return array
     */
    public function getHidden()
    {
        if ($this->splitHidden) {
            if (! is_array($this->_hidden_elements)) {
                $this->__getRepeatable();
            }

            return $this->_hidden_elements;
        }

        return array();
    }

    /**
     * Are the hidden fields split off?
     *
     * @return boolean
     */
    public function getSplitHidden()
    {
        return $this->splitHidden;
    }

    /**
     * Should the sub forms be split off?
     *
     * @param boolean $value
     * @return \MUtil\Lazy\RepeatableFormElements (continuation pattern)
     */
    public function setFlattenSubs($value = true)
    {
        $this->flattenSubs = $value;
        return $this;
    }

    /**
     * Should the hidden fields be split off?
     *
     * @param boolean $value
     * @return \MUtil\Lazy\RepeatableFormElements (continuation pattern)
     */
    public function setSplitHidden($value = true)
    {
        $this->splitHidden = $value;

        return $this;
    }
}