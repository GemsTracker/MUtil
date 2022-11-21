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
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class SelectDependency extends DependencyAbstract
{
    /**
     *
     * @var array
     */
    protected $_filter;

    /**
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     *
     * @param \Zend_Db_Select $select The base select statement
     * @param array $filter Array of select field => context field, context can be a \Zend_Db_Expr
     */
    public function __construct(\Zend_Db_Select $select, array $filter)
    {
        $this->_select = $select;
        $this->_filter = $filter;

        foreach ($filter as $context) {
            if (! $context instanceof \Zend_Db_Expr) {
                $this->addDependsOn($context);
            }
        }
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
        $select = clone $this->_select;

        foreach ($this->_filter as $fieldName => $contextName) {
            if ($contextName instanceof \Zend_Db_Expr) {
                $select->where($fieldName . ' = ?', $contextName);
            } elseif (null === $context[$contextName]) {
                $select->where($fieldName . ' IS NULL');
            } else {
                $select->where($fieldName . ' = ?', $context[$contextName]);
            }
        }

        $options = $this->db->fetchPairs($select);

        // \MUtil\EchoOut\EchoOut::track($this->getEffecteds());
        $results = array();
        foreach ($this->getEffecteds() as $name => $settings) {
            foreach ($settings as $setting) {
                $results[$name][$setting] = $options;
            }
        }

        return $results;
    }
}
