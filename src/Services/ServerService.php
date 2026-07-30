<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\DTOs\Server\ServerDTO;

class ServerService extends AbstractService implements ServerServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getServers(): array
    {
        return $this->mapList($this->get('/api/servers'), ServerDTO::class);
    }
}
