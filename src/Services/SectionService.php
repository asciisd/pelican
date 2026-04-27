<?php

namespace Asciisd\Copytrade\Services;

use Illuminate\Support\Facades\Http;
use Asciisd\Copytrade\Contracts\SectionServiceInterface;
use Asciisd\Copytrade\DTOs\Section\SectionDTO;

class SectionService implements SectionServiceInterface
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getSections(): array
    {
        $response = $this->makeRequest('GET', '/api/discover/');

        // Extract sections array
        $sections = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $section) => SectionDTO::fromResponse($section),
            $sections
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(string $code): SectionDTO
    {
        $response = $this->makeRequest('GET', "/api/discover/{$code}");

        return SectionDTO::fromResponse($response);
    }

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Make HTTP request.
     */
    protected function makeRequest(string $method, string $uri, array $data = []): array
    {
        $client = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = $client->send($method, $uri, [
            'json' => $data,
        ]);

        return $response->json() ?? [];
    }
}