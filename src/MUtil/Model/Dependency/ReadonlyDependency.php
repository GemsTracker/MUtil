<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Dependency;

/**
 * A class for adding dependencies that turn readonly off in the model unless
 * one of the values the dependency depends on returns a true value.
 *
 * Example:
 * <code>
 * $model->addDependency('ReadOnlyDependency', array('cannot_edit'), $this->getColNames('label'));
 * </code>
 * Will set readonly=readonly for all fields with a label when cannot_edit returns true,
 * otherwise sets readonly=null
 *
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ReadonlyDependency extends DependencyAbstract
{
    /**
     * The settings array for those effecteds that don't have an effects array
     *
     * @var array of setting => setting of setting changed by this dependency
     */
    protected $_defaultEffects = array('readonly', 'disabled');

    /**
     * Array for unsetting the readonly attributes
     *
     * @return array
     */
    protected function _getUneffecteds()
    {
        $output = array();

        foreach ($this->_effecteds as $key => $settings) {
            $output[$key] = array_fill_keys(array_keys($settings), null);
        }

        return $output;
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
    public function getChanges(array $context, $new)
    {
        foreach ($this->_dependentOn as $dependsOn) {
            if ($context[$dependsOn]) {
                return $this->_effecteds;
            }
        }

        return $this->_getUneffecteds();
    }
}
