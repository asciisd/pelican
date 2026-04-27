<?php

namespace Asciisd\Copytrade\Exceptions;

class AuthenticationException extends CopytradeException
{
    public function __construct(string $message = 'Authentication failed', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
