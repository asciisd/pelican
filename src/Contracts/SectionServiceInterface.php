<?php

namespace Asciisd\Copytrade\Contracts;

use Asciisd\Copytrade\DTOs\Section\SectionDTO;

interface SectionServiceInterface
{
    /**
     * Get all sections.
     *
     * @return SectionDTO[]
     */
    public function getSections(): array;

    /**
     * Get a specific section by code.
     */
    public function getSection(string $code): SectionDTO;

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self;
}