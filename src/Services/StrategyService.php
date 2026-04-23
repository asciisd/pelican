<?php

namespace Mohanad\Copytrade\Services;

use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Contracts\StrategyServiceInterface;
use Mohanad\Copytrade\DTOs\Copier\CopierDTO;
use Mohanad\Copytrade\DTOs\Strategy\CreateStrategyRequest;
use Mohanad\Copytrade\DTOs\Strategy\SearchStrategyDTO;
use Mohanad\Copytrade\DTOs\Strategy\SignalDTO;
use Mohanad\Copytrade\DTOs\Strategy\StrategyDTO;
use Mohanad\Copytrade\DTOs\Strategy\StrategyStatsDTO;
use Mohanad\Copytrade\DTOs\Strategy\UpdateStrategyRequest;

class StrategyService implements StrategyServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getStrategies(string $profileId): array
    {
        $response = $this->httpClient->get("/api/profiles/{$profileId}/strategies");

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

        $response = $this->httpClient->post(
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

        $response = $this->httpClient->put(
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
        $response = $this->httpClient->get("/api/strategies/{$strategyId}/stats");

        return StrategyStatsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function searchStrategies(string $filter): array
    {
        $response = $this->httpClient->get("/api/strategies?filter={$filter}");

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
        $response = $this->httpClient->get("/api/strategies/{$strategyId}/copiers");

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
        // Use multipart upload for file content
        $response = $this->httpClient->uploadFile(
            'PUT',
            "/api/profiles/{$profileId}/strategies/{$strategyId}/image",
            $fileContent,
            $filename,
            'file'
        );

        return $response;
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
        $response = $this->httpClient->get(
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
        $response = $this->httpClient->get("/api/strategies/{$strategyId}/signals/open");

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
        $this->httpClient->withToken($token);

        return $this;
    }
}