<?php

namespace Mohanad\Copytrade\Exceptions;

class NotFoundException extends CopytradeException
{
    public function __construct(string $message = 'Resource not found', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
