<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Batch
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Batch\Stack;

/**
 * A command stack that uses the cache system to store the data. The advantage over
 * a session is that each sets of commands is stored in a separate file. meaning
 * it does not lead to excessive session file sizes.
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class CacheStack extends \MUtil\Batch\Stack\StackAbstract
{
    /**
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $_cache;

    /**
     *
     * @var string
     */
    private $_cacheId;

    /**
     *
     * @var array
     */
    private $_commands;

    /**
     *
     * @param string $id A unique name identifying the batch
     */
    public function __construct($id, \Psr\Cache\CacheItemPoolInterface $cache)
    {
        $this->_cacheId  = 'batch_' . session_id() . '_' . $id;
        $this->_cache    = $cache;
        $item = $this->_cache->getItem($this->_cacheId);
        $this->_commands = $item->get();

        if (! $this->_commands) {
            $this->_commands = [];
        }
    }

    /**
     * Save the cache here
     */
    public function __destruct()
    {
        // \MUtil\EchoOut\EchoOut::track(count($this->_commands));
        if ($this->_commands) {
            $item = $this->_cache->getItem($this->_cacheId);
            $item->set($this->_commands);
            if ($item instanceof \Symfony\Contracts\Cache\ItemInterface) {
                $item->tag(['batch', 'sess_' . session_id()]);
            }
            $this->_cache->save($item);
        } else {
            $this->_cache->deleteItem($this->_cacheId);
        }
    }

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    protected function _addCommand(array $command, $id = null)
    {
        $result = (null === $id) || !isset($this->_commands[$id]);

        if (null === $id) {
            $this->_commands[] = $command;
        } else {
            $this->_commands[$id] = $command;
        }

        return $result;
    }

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext()
    {
        return reset($this->_commands);
    }

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext()
    {
        array_shift($this->_commands);
    }

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext()
    {
        return (boolean) $this->_commands;
    }

    /**
     * Reset the stack
     *
     * @return \MUtil\Batch\Stack\Stackinterface (continuation pattern)
     */
    public function reset()
    {
        $this->_commands = array();

        return $this;
    }
}
