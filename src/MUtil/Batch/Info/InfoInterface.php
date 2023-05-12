<?php

namespace MUtil\Batch\Info;

interface InfoInterface
{
    public function get(): array;

    public function set(array $batchInfo): void;
}