<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Type;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ConcatenatedRow
{
    /**
     * The character used to separate values when displaying.
     *
     * @var string
     */
    protected $displaySeperator = ' ';

    /**
     * Optional multi options to use
     *
     * @var array
     */
    protected $options;

    /**
     * The character used to separate values when storing.
     *
     * @var string
     */
    protected $seperatorChar = ' ';

    /**
     * When true the value is padded on both sides with the $seperatorChar.
     *
     * Makes it easier to filter.
     *
     * @var boolean
     */
    protected $valuePad = true;

    /**
     * \MUtil\Ra::args() parameter passing is allowed.
     *
     * @param string $seperatorChar
     * @param string $displaySeperator
     * @param boolean $valuePad
     */
    public function __construct($seperatorChar = ' ', $displaySeperator = ' ', $valuePad = true)
    {
        $args = \MUtil\Ra::args(
                func_get_args(),
                array(
                    'seperatorChar' => 'is_string',
                    'displaySeperator' => array('\\MUtil\\Html\\HtmlInterface', 'is_string'),
                    'valuePad' => 'is_boolean',
                    ),
                array('seperatorChar' => ' ', 'displaySeperator' => ' ', 'valuePad' => true)
                );

        $this->seperatorChar    = substr($args['seperatorChar'] . ' ', 0, 1);
        $this->displaySeperator = $args['displaySeperator'];
        $this->valuePad         = $args['valuePad'];
    }

    /**
     * Use this function for a default application of this type to the model
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param string $name The field to set the seperator character
     * @return \MUtil\Model\Type\ConcatenatedRow (continuation pattern)
     */
    public function apply(\MUtil\Model\ModelAbstract $model, $name)
    {
        $model->set($name, 'formatFunction', array($this, 'format'));
        $model->setOnLoad($name, array($this, 'loadValue'));
        $model->setOnSave($name, array($this, 'saveValue'));

        if ($model instanceof \MUtil\Model\DatabaseModelAbstract) {
            $model->setOnTextFilter($name, array($this, 'textFilter'));
        }

        $this->options = $model->get($name, 'multiOptions');
        return $this;
    }


    /**
     * Displays the content
     *
     * @param string $value
     * @return string
     */
    public function format($value)
    {
        // \MUtil\EchoOut\EchoOut::track($value, $this->options);
        if (! is_array($value)) {
            $value = $this->loadValue($value);
        }
        if (is_array($value)) {
            if ($this->options) {
                foreach ($value as &$val) {
                    if (isset($this->options[$val])) {
                        $val = $this->options[$val];
                    }
                 }
            }
            if (is_string($this->displaySeperator)) {
                return implode($this->displaySeperator, $value);
            } else {
                $output = new \MUtil\Html\Sequence($value);
                $output->setGlue($this->displaySeperator);
                return $output;
            }
        }
        if (isset($this->options[$value])) {
            return $this->options[$value];
        }
        return $value;
    }

    /**
     * If this field is saved as an array value, use
     *
     * @return array Containing settings for model item
     */
    public function getSettings()
    {
        $output['formatFunction'] = array($this, 'format');
        $output[\MUtil\Model\ModelAbstract::LOAD_TRANSFORMER] = array($this, 'loadValue');
        $output[\MUtil\Model\ModelAbstract::SAVE_TRANSFORMER] = array($this, 'saveValue');
        $output[\MUtil\Model\DatabaseModelAbstract::TEXTFILTER_TRANSFORMER] = array($this, 'textFilter');

        return $output;
    }

    /**
     * A ModelAbstract->setOnLoad() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @param boolean $isPost True when passing on post data
     * @return array Of the values
     */
    public function loadValue($value, $isNew = false, $name = null, array $context = array(), $isPost = false)
    {
        // \MUtil\EchoOut\EchoOut::track($value, $name, $context);
        if (! is_array($value)) {
            if ($this->valuePad) {
                $value = trim((string)$value, $this->seperatorChar);
            }
            // If it was empty, return an empty array instead of array with an empty element
            if(empty($value)) {
                return [];
            }
            $value = explode($this->seperatorChar, $value);
        }
        // \MUtil\EchoOut\EchoOut::track($value);

        return $value;
    }

    /**
     * A ModelAbstract->setOnSave() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @return string Of the values concatenated
     */
    public function saveValue($value, $isNew = false, $name = null, array $context = array())
    {
        // \MUtil\EchoOut\EchoOut::track($value);
        if (is_array($value)) {
            $value = implode($this->seperatorChar, $value);

            if ($this->valuePad) {
                $value = $this->seperatorChar . $value . $this->seperatorChar;
            }
        }
        return $value;
    }

    /**
     *
     * @param string $filter The text to filter for
     * @param string $name The model field name
     * @param string $sqlField The SQL field name
     * @param \MUtil\Model\DatabaseModelAbstract $model
     * @return array Array of OR-filter statements
     */
    public function textFilter($filter, $name, $sqlField, \MUtil\Model\DatabaseModelAbstract $model)
    {
        $options = $model->get($name, 'multiOptions');
        if ($options) {
            $adapter = $model->getAdapter();
            $wheres = array();
            foreach ($options as $key => $value) {
                // \MUtil\EchoOut\EchoOut::track($key, $value, $filter, stripos($value, $filter));
                if (stripos($value, $filter) === false) {
                    continue;
                }
                if (null === $key) {
                    $wheres[] = $sqlField . ' IS NULL';
                } else {
                    $wheres[] = $adapter->quoteInto(
                            $sqlField . " LIKE ?",
                            '%' . $this->seperatorChar . $key . $this->seperatorChar . '%'
                            );

                    if (! $this->valuePad) {
                        // Add other options
                        $wheres[] = $adapter->quoteInto(
                                $sqlField . " LIKE ?",
                                $key . $this->seperatorChar . '%'
                                );
                        $wheres[] = $adapter->quoteInto(
                                $sqlField . " LIKE ?",
                                '%' . $this->seperatorChar . $key
                                );
                        $wheres[] = $adapter->quoteInto($sqlField . " = ?", $key);
                    }
                }
            }
            return $wheres;
        }
    }
}
