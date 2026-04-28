<?php

namespace Asciisd\Copytrade\Contracts;

use Asciisd\Copytrade\DTOs\Copier\CopierDTO;
use Asciisd\Copytrade\DTOs\Strategy\SearchStrategyDTO;
use Asciisd\Copytrade\DTOs\Strategy\SignalDTO;
use Asciisd\Copytrade\DTOs\Strategy\StrategyDTO;
use Asciisd\Copytrade\DTOs\Strategy\StrategyStatsDTO;

interface StrategyServiceInterface
{
    /**
     * Get strategies connected to profile.
     *
     * @return StrategyDTO[]
     */
    public function getStrategies(string $profileId): array;

    /**
     * Add strategy to profile.
     */
    public function addStrategy(string $profileId, array $data): StrategyDTO;

    /**
     * Update strategy.
     */
    public function updateStrategy(string $profileId, string $strategyId, array $data): StrategyDTO;

    /**
     * Get strategy statistics.
     */
    public function getStrategyStats(string $strategyId): StrategyStatsDTO;

    /**
     * Search for strategies.
     *
     * @return SearchStrategyDTO[]
     */
    public function searchStrategies(string $filter): array;

    /**
     * Get copiers that are copying a strategy.
     *
     * @return CopierDTO[]
     */
    public function getStrategyCopiers(string $strategyId): array;

    /**
     * Upload strategy image.
     */
    public function uploadStrategyImage(string $profileId, string $strategyId, $fileContent, string $filename): array;

    /**
     * Get strategy image URL.
     */
    public function getStrategyImageUrl(string $strategyId): string;

    /**
     * Get strategy closed signals.
     *
     * @return SignalDTO[]
     */
    public function getStrategyClosedSignals(string $strategyId, string $startDate, string $endDate): array;

    /**
     * Get strategy open signals.
     *
     * @return SignalDTO[]
     */
    public function getStrategyOpenSignals(string $strategyId): array;

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): static;
}