<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class XmlModel extends \MUtil\Model\ArrayModelAbstract
{
    /**
     * The field map for raw input to text
     *
     * @var array
     */
    protected $_fieldMap;

    /**
     * The name of the content file
     *
     * @var string
     */
    protected $_fileName;

    /**
     *
     * @var \MUtil_Xmlra
     */
    protected $_xml;

    /**
     *
     * @param string $fileName
     */
    public function __construct($fileName, $xpath = '/*')
    {
        parent::__construct($fileName);

        $this->_fileName = $fileName;
        $this->_xml = \MUtil\XmlRa::loadFile($fileName, $xpath);

        foreach($this->_loadAllTraversable() as $row) {
            if (is_array($row)) {
                $this->setMulti(array_keys($row));
            }
        }
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return \Traversable Return an iterator over or an array of all the rows in this object
     */
    protected function _loadAllTraversable()
    {
        $iter = $this->_xml->getElementIterator();
        $iter->setMapFunction(array('\\MUtil\\XmlRa', 'toArray'));
        return $iter;
    }
}
