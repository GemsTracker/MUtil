<?php

namespace MUtil\EchoOut\Store;

use Mezzio\Session\SessionInterface;

class SessionStore implements StoreInterface
{
    private string $dataKey = 'EchoOutStoreData';

    public function __construct(private SessionInterface $session)
    {}

    public function clear(): void
    {
        $this->session->unset($dataKey);
    }

    public function getItem($name): mixed
    {
        if ($this->session->has($this->dataKey)) {
            $data = $this->session->get($this->dataKey, []);
            if (array_key_exists($name, $data)) {
                return $data[$name];
            }
        }
        throw new \Exception("$name not found");
    }

    public function hasItem(string $name): bool
    {
        $data = $this->session->get($this->dataKey, []);
        return array_key_exists($name, $data);
    }

    public function setItem($name, $value): void
    {
        $data = $this->session->get($this->dataKey, []);
        $data[$name] = $value;
        $this->session->set($this->dataKey, $data);
    }
}