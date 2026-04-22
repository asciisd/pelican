<?php

namespace Mohanad\Copytrade\Services;

use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Contracts\SectionServiceInterface;
use Mohanad\Copytrade\DTOs\Section\SectionDTO;

class SectionService implements SectionServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getSections(): array
    {
        $response = $this->httpClient->get('/api/discover/');

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
        $response = $this->httpClient->get("/api/discover/{$code}");

        return SectionDTO::fromResponse($response);
    }

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self
    {
        $this->httpClient->withToken($token);

        return $this;
    }
}