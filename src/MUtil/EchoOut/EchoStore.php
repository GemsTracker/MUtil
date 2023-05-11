<?php

namespace MUtil\EchoOut;

use MUtil\EchoOut\Store\ObjectStore;
use MUtil\EchoOut\Store\StoreInterface;

class EchoStore
{
    private StoreInterface $store;

    public function __construct(StoreInterface $store = null)
    {
        if ($store === null) {
            $store = new ObjectStore();
        }
        $this->store = $store;
        $this->initStore();
    }

    public function __get(string $name): mixed
    {
        return $this->store->getItem($name);
    }

    public function __isset(string $name): bool
    {
        return $this->store->hasItem($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->store->setItem($name, $value);
    }

    private function initStore(): void
    {
        if (!$this->store->hasItem('content') || !is_string($this->store->getItem('content'))) {
            $this->store->setItem('content', '');
        }
    }
    
    public function setStore(StoreInterface $store): void
    {
        $this->store = $store;
    }

    public function unsetAll()
    {
        $this->store->clear();
    }
}