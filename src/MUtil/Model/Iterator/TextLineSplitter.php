<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Iterator;

/**
 * Helper function to prevent having to use the whole model to serialize
 * a TextFileIterator.
 *
 * @see \MUtil\Model\Iterator\TextFileIterator
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class TextLineSplitter
{
    /**
     * The content file encoding
     *
     * @var string
     */
    protected $_encoding;

    /**
     * The regular expression for the split
     *
     * @var string
     */
    protected $_split = "\t";

    /**
     *
     * @param string $split The regular expression for the split
     * @param string $encoding An encoding to use
     */
    public function __construct($split, $encoding)
    {
        $this->_encoding = $encoding;
        $this->_split    = $split;
    }

    /**
     * Splits the line
     *
     * @param array $line
     * @return array
     */
    public function split($line)
    {
        return explode($this->_split, $line);
    }

    /**
     * Recodes and splits the line
     *
     * @param array $line
     * @return array
     */
    public function splitRecoded($line)
    {
        $line = mb_convert_encoding($line, mb_internal_encoding(), $this->_encoding);
        return explode($this->_split, $line);
    }
}
