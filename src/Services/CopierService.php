<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\CopierServiceInterface;
use Asciisd\Copytrade\DTOs\Copier\CopierDTO;
use Asciisd\Copytrade\DTOs\Copier\CopierStatsDTO;
use Asciisd\Copytrade\DTOs\Copier\CopySettingsDTO;
use Asciisd\Copytrade\DTOs\Copier\CopyStrategyRequest;
use Asciisd\Copytrade\DTOs\Copier\CreateCopierRequest;
use Asciisd\Copytrade\DTOs\Copier\UpdateCopierRequest;
use Asciisd\Copytrade\DTOs\Copier\UpdateCopySettingsRequest;
use Asciisd\Copytrade\DTOs\Strategy\SignalDTO;
use Asciisd\Copytrade\DTOs\Strategy\StrategyDTO;

class CopierService extends AbstractService implements CopierServiceInterface
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
    public function getCopiers(string $profileId): array
    {
        return $this->mapList(
            $this->get("/api/profiles/{$profileId}/copiers"),
            CopierDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addCopier(string $profileId, array $data): CopierDTO
    {
        $request = CreateCopierRequest::fromArray($data);

        return CopierDTO::fromResponse(
            $this->post("/api/profiles/{$profileId}/copiers", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopier(string $profileId, string $copierId, array $data): CopierDTO
    {
        $request = UpdateCopierRequest::fromArray($data);

        return CopierDTO::fromResponse(
            $this->put("/api/profiles/{$profileId}/copiers/{$copierId}", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeCopier(string $profileId, string $copierId): bool
    {
        $this->delete("/api/profiles/{$profileId}/copiers/{$copierId}");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierStats(string $copierId): CopierStatsDTO
    {
        return CopierStatsDTO::fromResponse($this->get("/api/copiers/{$copierId}/stats"));
    }

    /**
     * {@inheritdoc}
     */
    public function uploadCopierImage(string $profileId, string $copierId, $fileContent, string $filename): array
    {
        return $this->upload(
            "/api/profiles/{$profileId}/copiers/{$copierId}/image",
            $fileContent,
            $filename
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierImageUrl(string $copierId): string
    {
        return "{$this->assetUri}/images/copiers/thumbnails/{$copierId}";
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierStrategies(string $copierId): array
    {
        return $this->mapList(
            $this->get("/api/copiers/{$copierId}/strategies"),
            StrategyDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function copyStrategy(string $copierId, string $strategyId, array $data): CopySettingsDTO
    {
        $request = CopyStrategyRequest::fromArray($data);

        return CopySettingsDTO::fromResponse(
            $this->post("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCopySettings(string $copierId, string $strategyId): CopySettingsDTO
    {
        return CopySettingsDTO::fromResponse(
            $this->get("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings")
        );
    }

    /**
     * {@inheritdoc}
     */
    public function stopCopying(string $copierId, string $strategyId): bool
    {
        $this->delete("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopySettings(string $copierId, string $strategyId, array $data): CopySettingsDTO
    {
        $request = UpdateCopySettingsRequest::fromArray($data);

        return CopySettingsDTO::fromResponse(
            $this->put("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings", $request->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierOpenSignals(string $copierId): array
    {
        return $this->mapList(
            $this->get("/api/copiers/{$copierId}/signals/open"),
            SignalDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierClosedSignals(string $copierId, string $startDate, string $endDate): array
    {
        return $this->mapList(
            $this->get("/api/copiers/{$copierId}/signals/closed", query: [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]),
            SignalDTO::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMissedSignals(string $profileId, string $copierId): array
    {
        return $this->mapList(
            $this->get("/api/profiles/{$profileId}/copiers/{$copierId}/signals/missed"),
            SignalDTO::class
        );
    }
}
