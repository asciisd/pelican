<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\SectionServiceInterface;
use Asciisd\Copytrade\DTOs\Section\SectionDTO;

class SectionService extends AbstractService implements SectionServiceInterface
{

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

}