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

class StrategyService extends AbstractService implements StrategyServiceInterface
{
    public function __construct(
        string $baseUri,
        protected string $assetUri,
        int $timeout = 120
    ) {
        parent::__construct($baseUri, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategies(string $profileId): array
    {
        return $this->mapList(
            $this->get("/api/profiles/{$profileId}/strategies"),
            StrategyDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addStrategy(string $profileId, array $data): StrategyDTO
    {
        $request = CreateStrategyRequest::fromArray($data);

        return StrategyDTO::fromResponse(
            $this->post("/api/profiles/{$profileId}/strategies", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateStrategy(string $profileId, string $strategyId, array $data): StrategyDTO
    {
        $request = UpdateStrategyRequest::fromArray($data);

        return StrategyDTO::fromResponse(
            $this->put("/api/profiles/{$profileId}/strategies/{$strategyId}", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyStats(string $strategyId): StrategyStatsDTO
    {
        return StrategyStatsDTO::fromResponse($this->get("/api/strategies/{$strategyId}/stats"));
    }

    /**
     * {@inheritdoc}
     */
    public function searchStrategies(string $filter): array
    {
        return $this->mapList(
            $this->get('/api/strategies', query: ['filter' => $filter]),
            SearchStrategyDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyCopiers(string $strategyId): array
    {
        return $this->mapList(
            $this->get("/api/strategies/{$strategyId}/copiers"),
            CopierDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function uploadStrategyImage(string $profileId, string $strategyId, $fileContent, string $filename): array
    {
        return $this->upload(
            "/api/profiles/{$profileId}/strategies/{$strategyId}/image",
            $fileContent,
            $filename
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyImageUrl(string $strategyId): string
    {
        return "{$this->assetUri}/images/strategies/thumbnails/{$strategyId}";
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyClosedSignals(string $strategyId, string $startDate, string $endDate): array
    {
        return $this->mapList(
            $this->get("/api/strategies/{$strategyId}/signals/closed/", query: [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]),
            SignalDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategyOpenSignals(string $strategyId): array
    {
        return $this->mapList(
            $this->get("/api/strategies/{$strategyId}/signals/open"),
            SignalDTO::class
        );
    }
}
