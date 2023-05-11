<?php

namespace MUtil\EchoOut\Store;

interface StoreInterface
{
    public function clear(): void;

    public function getItem(string $name): mixed;

    public function hasItem(string $name): bool;

    public function setItem(string $name, mixed $value): void;

}