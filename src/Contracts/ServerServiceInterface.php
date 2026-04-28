<?php

namespace Asciisd\Copytrade\Contracts;

use Asciisd\Copytrade\DTOs\Server\ServerDTO;

interface ServerServiceInterface
{
    /**
     * Get list of available servers.
     *
     * @return ServerDTO[]
     */
    public function getServers(): array;

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): static;
}