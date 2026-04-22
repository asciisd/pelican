<?php

namespace Mohanad\Copytrade\Exceptions;

class ValidationException extends CopytradeException
{
    public function __construct(string $message = 'Validation failed', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
