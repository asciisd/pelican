<?php

namespace Mohanad\Copytrade\Contracts;

use Mohanad\Copytrade\DTOs\Copier\CopierDTO;
use Mohanad\Copytrade\DTOs\Copier\CopierStatsDTO;
use Mohanad\Copytrade\DTOs\Copier\CopySettingsDTO;
use Mohanad\Copytrade\DTOs\Strategy\SignalDTO;
use Mohanad\Copytrade\DTOs\Strategy\StrategyDTO;

interface CopierServiceInterface
{
    /**
     * Get copiers connected to profile.
     *
     * @return CopierDTO[]
     */
    public function getCopiers(string $profileId): array;

    /**
     * Add copier to profile.
     */
    public function addCopier(string $profileId, array $data): CopierDTO;

    /**
     * Update copier.
     */
    public function updateCopier(string $profileId, string $copierId, array $data): CopierDTO;

    /**
     * Remove copier.
     */
    public function removeCopier(string $profileId, string $copierId): bool;

    /**
     * Get copier statistics.
     */
    public function getCopierStats(string $copierId): CopierStatsDTO;

    /**
     * Upload copier image.
     */
    public function uploadCopierImage(string $profileId, string $copierId, $fileContent, string $filename): array;

    /**
     * Get copier image URL.
     */
    public function getCopierImageUrl(string $copierId): string;

    /**
     * Get strategies that are being copied by a copier.
     *
     * @return StrategyDTO[]
     */
    public function getCopierStrategies(string $copierId): array;

    /**
     * Copy a strategy with settings.
     */
    public function copyStrategy(string $copierId, string $strategyId, array $data): CopySettingsDTO;

    /**
     * Get copy settings for a strategy.
     */
    public function getCopySettings(string $copierId, string $strategyId): CopySettingsDTO;

    /**
     * Update copy settings for a strategy.
     */
    public function updateCopySettings(string $copierId, string $strategyId, array $data): CopySettingsDTO;

    /**
     * Stop copying a strategy.
     */
    public function stopCopyingStrategy(string $copierId, string $strategyId): bool;

    /**
     * Get missed signals for a copier.
     *
     * @return SignalDTO[]
     */
    public function getMissedSignals(string $profileId, string $copierId): array;

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self;
}