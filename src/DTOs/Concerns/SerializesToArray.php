<?php

namespace Asciisd\Copytrade\DTOs\Concerns;

trait SerializesToArray
{
    /**
     * Serialize the DTO to JSON via its toArray() representation.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Read the first present key from an API response, tolerating PascalCase,
     * camelCase and snake_case variants.
     */
    protected static function value(array $data, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }
}
