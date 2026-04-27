<?php

namespace Asciisd\Copytrade\Services;

use Illuminate\Support\Facades\Http;
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

class CopierService implements CopierServiceInterface
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCopiers(string $profileId): array
    {
        $response = $this->makeRequest('GET', "/api/profiles/{$profileId}/copiers");

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

        $response = $this->makeRequest('POST', "/api/profiles/{$profileId}/copiers", $request->toArray());

        return CopierDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopier(string $profileId, string $copierId, array $data): CopierDTO
    {
        $request = UpdateCopierRequest::fromArray($data);

        $response = $this->makeRequest('PUT', "/api/profiles/{$profileId}/copiers/{$copierId}", $request->toArray());

        return CopierDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function removeCopier(string $profileId, string $copierId): bool
    {
        $this->makeRequest('DELETE', "/api/profiles/{$profileId}/copiers/{$copierId}");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopierStats(string $copierId): CopierStatsDTO
    {
        $response = $this->makeRequest('GET', "/api/copiers/{$copierId}/stats");

        return CopierStatsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function uploadCopierImage(string $profileId, string $copierId, $fileContent, string $filename): array
    {
        // Use Laravel Http facade for file uploads
        $client = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = $client->attach('file', $fileContent, $filename)
            ->put("/api/profiles/{$profileId}/copiers/{$copierId}/image");

        return $response->json() ?? [];
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
        $response = $this->makeRequest('GET', "/api/copiers/{$copierId}/strategies");

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

        $response = $this->makeRequest('POST', "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings", $request->toArray());

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getCopySettings(string $copierId, string $strategyId): CopySettingsDTO
    {
        $response = $this->makeRequest('GET', "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings");

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCopySettings(string $copierId, string $strategyId, array $data): CopySettingsDTO
    {
        $request = UpdateCopySettingsRequest::fromArray($data);

        $response = $this->makeRequest('PUT', "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings", $request->toArray());

        return CopySettingsDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function stopCopyingStrategy(string $copierId, string $strategyId): bool
    {
        $this->makeRequest('DELETE', "/api/copiers/{$copierId}/strategies/{$strategyId}/copy-settings");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissedSignals(string $profileId, string $copierId): array
    {
        $response = $this->makeRequest('GET', "/api/profiles/{$profileId}/copiers/{$copierId}/signals/missed");

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

        return $response->json() ?? [];
    }
}