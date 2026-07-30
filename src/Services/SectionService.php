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
        return $this->mapList($this->get('/api/discover/'), SectionDTO::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(string $code): SectionDTO
    {
        return SectionDTO::fromResponse($this->get("/api/discover/{$code}"));
    }
}
