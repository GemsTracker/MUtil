<?php

namespace MUtil\EchoOut\Store;

class ObjectStore implements StoreInterface
{
    protected array $data = [];
    
    public function getItem(string $name): mixed
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new StoreException("$name not found");
    }

    public function hasItem(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function setItem(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function clear(): void
    {
        $this->data = [];
    }
}