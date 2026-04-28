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
        $response = $this->makeRequest('GET', '/api/servers');

        // Extract servers array (handle both direct array and wrapped response)
        $servers = isset($response[0]) ? $response : ($response['data'] ?? []);

        // Convert each server to DTO
        return array_map(
            fn (array $server) => ServerDTO::fromResponse($server),
            $servers
        );
    }

}