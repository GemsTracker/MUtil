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

use Mezzio\Session\SessionInterface;

/**
 * A default command stack that uses the session ot store the commands to
 * execute.
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class SessionStack extends StackAbstract
{
    private string $sessionId;

    private SessionInterface $session;

    /**
     *
     * @param string $id A unique name identifying the batch
     */
    public function __construct(string $id, SessionInterface $session)
    {
        $this->sessionId = sprintf('%s_%s_commands', get_class($this), $id);
        $this->session = $session;
    }

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string|null $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    protected function _addCommand(array $command, ?string $id = null): bool
    {
        $commands = $this->getCommands();
        $result = (null === $id) || !isset($commands[$id]);

        if (null === $id) {
            $commands[] = $command;
        } else {
            $commands[$id] = $command;
        }
        $this->session->set($this->sessionId, $commands);

        return $result;
    }

    public function getCommands(): array
    {
        return $this->session->get($this->sessionId, []);
    }

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext(): array
    {
        $commands = $this->getCommands();
        return reset($commands);
    }

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext(): void
    {
        $commands = $this->getCommands();
        array_shift($commands);
        $this->session->set($this->sessionId, $commands);
    }

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext(): bool
    {
        $commands = $this->getCommands();

        return (count($commands) > 0);
    }

    /**
     * Reset the stack
     *
     * @return self
     */
    public function reset(): self
    {
        $this->session->unset($this->sessionId);

        return $this;
    }
}
