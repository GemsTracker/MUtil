<?php

namespace MUtil\Batch\Info;

use Mezzio\Session\SessionInterface;

class SessionInfo implements InfoInterface
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $key
    )
    {}


    public function get(): array
    {
        return $this->session->get($this->key, []);
    }

    public function set(array $batchInfo): void
    {
        $this->session->set($this->key, $batchInfo);
    }
}