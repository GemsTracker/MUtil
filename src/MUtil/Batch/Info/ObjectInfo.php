<?php

namespace MUtil\Batch\Info;

class ObjectInfo implements InfoInterface
{
    private array $batchInfo = [];

    public function get(): array
    {
        return $this->batchInfo;
    }

    public function set(array $batchInfo): void
    {
        $this->batchInfo = $batchInfo;
    }
}