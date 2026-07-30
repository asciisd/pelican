<?php

use Asciisd\Copytrade\Contracts\ServerServiceInterface;

it('withToken returns a new instance and never mutates the shared singleton', function () {
    $service = app(ServerServiceInterface::class);

    $scoped = $service->withToken('user-token');

    expect($scoped)->not->toBe($service);
});

it('does not leak a token from one scoped service into another', function () {
    $shared = app(ServerServiceInterface::class);

    $userA = $shared->withToken('token-a');
    $userB = $shared->withToken('token-b');

    $read = function (object $service): ?string {
        $property = (new ReflectionClass($service))->getProperty('token');

        return $property->getValue($service);
    };

    expect($read($userA))->toBe('token-a')
        ->and($read($userB))->toBe('token-b')
        ->and($read($shared))->toBeNull();
});
