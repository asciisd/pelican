<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\StrategyServiceInterface;
use Asciisd\Copytrade\DTOs\Copier\CopierDTO;
use Asciisd\Copytrade\DTOs\Strategy\CreateStrategyRequest;
use Asciisd\Copytrade\DTOs\Strategy\SearchStrategyDTO;
use Asciisd\Copytrade\DTOs\Strategy\SignalDTO;
use Asciisd\Copytrade\DTOs\Strategy\StrategyDTO;
use Asciisd\Copytrade\DTOs\Strategy\StrategyStatsDTO;
use Asciisd\Copytrade\DTOs\Strategy\UpdateStrategyRequest;
use Asciisd\Copytrade\Exceptions\CopytradeException;
use Illuminate\Support\Facades\Http;

class StrategyService implements StrategyServiceInterface
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getStrategies(string $profileId): array
    {
        $response = $this->makeRequest('GET', "/api/profiles/{$profileId}/strategies");

        // Extract strategies array
        $strategies = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $strategy) => StrategyDTO::fromResponse($strategy),
            $strategies
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addStrategy(string $profileId, array $data): StrategyDTO
    {
        $request = CreateStrategyRequest::fromArray($data);

        $response = $this->makeRequest('POST',
            "/api/profiles/{$profileId}/strategies",
            $request->toArray()
        );

        return StrategyDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStrategy(string $profileId, string $strategyId, array $data): StrategyDTO
    {
        $request = UpdateStrategyRequest::fromArray($data);

        $response = $this->makeRequest('PUT',
            "/api/profiles/{$profileId}/strategies/{$strategyId}",
            $request->toArray()
        );

        return StrategyDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyStats(string $strategyId): StrategyStatsDTO
    {
        $response = $this->makeRequest('GET', "/api/strategies/{$strategyId}/stats");

        return StrategyStatsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function searchStrategies(string $filter): array
    {
        $response = $this->makeRequest('GET', "/api/strategies?filter={$filter}");

        // Extract strategies array
        $strategies = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $strategy) => SearchStrategyDTO::fromResponse($strategy),
            $strategies
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyCopiers(string $strategyId): array
    {
        $response = $this->makeRequest('GET', "/api/strategies/{$strategyId}/copiers");

        // Extract copiers array
        $copiers = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $copier) => CopierDTO::fromResponse($copier),
            $copiers
        );
    }

    /**
     * {@inheritdoc}
     */
    public function uploadStrategyImage(string $profileId, string $strategyId, $fileContent, string $filename): array
    {
        // Use Laravel Http facade for file uploads
        $client = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = $client->attach('file', $fileContent, $filename)
            ->put("/api/profiles/{$profileId}/strategies/{$strategyId}/image");

        $result = $response->json();

        // Ensure we always return an array
        if (is_string($result)) {
            return ['url' => $result];
        }

        return is_array($result) ? $result : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyImageUrl(string $strategyId): string
    {
        // CopyTrade stores images on separate asset server
        return "https://assets.copy-trade.io/images/strategies/thumbnails/{$strategyId}";
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyClosedSignals(string $strategyId, string $startDate, string $endDate): array
    {
        $response = $this->makeRequest('GET',
            "/api/strategies/{$strategyId}/signals/closed/?startDate={$startDate}&endDate={$endDate}"
        );

        // Extract signals array
        $signals = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $signal) => SignalDTO::fromResponse($signal),
            $signals
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyOpenSignals(string $strategyId): array
    {
        $response = $this->makeRequest('GET', "/api/strategies/{$strategyId}/signals/open");

        // Extract signals array
        $signals = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $signal) => SignalDTO::fromResponse($signal),
            $signals
        );
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

        // Check if response is successful
        if ($response->failed()) {
            throw new CopytradeException(
                "API request failed: {$response->status()} - {$response->body()}",
                $response->status()
            );
        }

        $result = $response->json();

        // Ensure we always return an array
        return is_array($result) ? $result : [];
    }
}