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
 * A model that uses csv files as a source
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class CsvModel extends \MUtil\Model\ArrayModelAbstract
{
    /**
     * The content file encoding
     *
     * @var string
     */
    protected $_encoding;

    /**
     * The name of the content file
     *
     * @var string
     */
    protected $_fileName;

    /**
     *
     * @param string $fileName Name fe the file
     * @param string $encoding An encoding to use
     */
    public function __construct($fileName, $encoding = null)
    {
        parent::__construct($fileName);

        $this->_fileName = $fileName;
        $this->_encoding = $encoding;
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return \Traversable Return an iterator over or an array of all the rows in this object
     */
    protected function _loadAllTraversable()
    {
        $iterator = new \MUtil\Model\Iterator\CsvFileIterator($this->_fileName, $this->_encoding);

        // Store the positions in the model
        foreach ($iterator->getFieldMap() as $pos => $name) {
            $this->set($name, 'read_position', $pos);
        }

        return $iterator;
    }
}
