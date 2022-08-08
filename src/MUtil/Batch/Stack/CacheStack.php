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

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
class CacheStack extends StackAbstract
{
    private CacheItemPoolInterface $cache;

    private string $cacheId;

    private array $commands;

    /**
     *
     * @param string $id A unique name identifying the batch
     */
    public function __construct(string $id, CacheItemPoolInterface $cache)
    {
        $this->cacheId  = 'batch_' . session_id() . '_' . $id;
        $this->cache    = $cache;
        $item = $this->cache->getItem($this->cacheId);
        $commands = $item->get();

        if (! $commands) {
            $commands = [];
        }
        $this->commands = $commands;
    }

    /**
     * Save the cache here
     */
    public function __destruct()
    {
        // \MUtil\EchoOut\EchoOut::track(count($this->_commands));
        if ($this->commands) {
            $item = $this->cache->getItem($this->cacheId);
            $item->set($this->commands);
            if ($item instanceof ItemInterface) {
                $item->tag(['batch', 'sess_' . session_id()]);
            }
            $this->cache->save($item);
        } else {
            $this->cache->deleteItem($this->cacheId);
        }
    }

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    protected function _addCommand(array $command, ?string $id = null): bool
    {
        $result = (null === $id) || !isset($this->commands[$id]);

        if (null === $id) {
            $this->commands[] = $command;
        } else {
            $this->commands[$id] = $command;
        }

        return $result;
    }

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext(): array
    {
        return reset($this->commands);
    }

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext(): void
    {
        array_shift($this->commands);
    }

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext(): bool
    {
        return (boolean) $this->commands;
    }

    /**
     * Reset the stack
     *
     * @return self
     */
    public function reset(): self
    {
        $this->commands = [];

        return $this;
    }
}
