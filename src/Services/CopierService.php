<?php

namespace Mohanad\Copytrade\Services;

use Mohanad\Copytrade\Contracts\CopierServiceInterface;
use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\DTOs\Copier\CopierDTO;
use Mohanad\Copytrade\DTOs\Copier\CopierStatsDTO;
use Mohanad\Copytrade\DTOs\Copier\CopySettingsDTO;
use Mohanad\Copytrade\DTOs\Copier\CopyStrategyRequest;
use Mohanad\Copytrade\DTOs\Copier\CreateCopierRequest;
use Mohanad\Copytrade\DTOs\Copier\UpdateCopierRequest;
use Mohanad\Copytrade\DTOs\Copier\UpdateCopySettingsRequest;
use Mohanad\Copytrade\DTOs\Strategy\SignalDTO;
use Mohanad\Copytrade\DTOs\Strategy\StrategyDTO;

class CopierService implements CopierServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCopiers(string $profileId): array
    {
        $response = $this->httpClient->get("/api/profiles/{$profileId}/copiers");

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
    public function addCopier(string $profileId, array $data): CopierDTO
    {
        $request = CreateCopierRequest::fromArray($data);

        $response = $this->httpClient->post(
            "/api/profiles/{$profileId}/copiers",
            $request->toArray()
        );

        return CopierDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopier(string $profileId, string $copierId, array $data): CopierDTO
    {
        $request = UpdateCopierRequest::fromArray($data);

        $response = $this->httpClient->put(
            "/api/profiles/{$profileId}/copiers/{$copierId}",
            $request->toArray()
        );

        return CopierDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function removeCopier(string $profileId, string $copierId): bool
    {
        $this->httpClient->delete("/api/profiles/{$profileId}/copiers/{$copierId}");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierStats(string $copierId): CopierStatsDTO
    {
        $response = $this->httpClient->get("/api/copiers/{$copierId}/stats");

        return CopierStatsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function uploadCopierImage(string $profileId, string $copierId, $fileContent, string $filename): array
    {
        // Use multipart upload for file content
        $response = $this->httpClient->uploadFile(
            'PUT',
            "/api/profiles/{$profileId}/copiers/{$copierId}/image",
            $fileContent,
            $filename,
            'file'
        );

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierImageUrl(string $copierId): string
    {
        // CopyTrade stores images on separate asset server
        return "https://assets.copy-trade.io/images/copiers/thumbnails/{$copierId}";
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierStrategies(string $copierId): array
    {
        $response = $this->httpClient->get("/api/copiers/{$copierId}/strategies");

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
    public function copyStrategy(string $copierId, string $strategyId, array $data): CopySettingsDTO
    {
        $request = CopyStrategyRequest::fromArray($data);

        $response = $this->httpClient->post(
            "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings",
            $request->toArray()
        );

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getCopySettings(string $copierId, string $strategyId): CopySettingsDTO
    {
        $response = $this->httpClient->get("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings");

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopySettings(string $copierId, string $strategyId, array $data): CopySettingsDTO
    {
        $request = UpdateCopySettingsRequest::fromArray($data);

        $response = $this->httpClient->put(
            "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings",
            $request->toArray()
        );

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function stopCopyingStrategy(string $copierId, string $strategyId): bool
    {
        $this->httpClient->delete("/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissedSignals(string $profileId, string $copierId): array
    {
        $response = $this->httpClient->get("/api/profiles/{$profileId}/copiers/{$copierId}/signals/missed");

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