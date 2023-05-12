<?php

namespace MUtil\Batch\Stack;

class ObjectStack extends StackAbstract
{
    private array $commands = [];

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

    public function hasNext(): bool
    {
        return count($this->commands) > 0;
    }

    public function getNext(): array
    {
        return reset($this->commands);
    }

    public function gotoNext(): void
    {
        array_shift($this->commands);
    }

    public function reset(): Stackinterface
    {
        $this->commands = [];

        return $this;
    }
}