<?php

use Asciisd\Copytrade\Contracts\CopierServiceInterface;
use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\Contracts\StrategyServiceInterface;
use Asciisd\Copytrade\DTOs\Server\ServerDTO;
use Asciisd\Copytrade\DTOs\Strategy\SignalDTO;
use Asciisd\Copytrade\Exceptions\CopytradeException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('maps a bare array list response into DTOs', function () {
    Http::fake([
        'papi.copy-trade.io/api/servers' => Http::response([
            ['id' => 'srv-1'],
            ['id' => 'srv-2'],
        ], 200),
    ]);

    $servers = app(ServerServiceInterface::class)->getServers();

    expect($servers)->toHaveCount(2)
        ->and($servers[0])->toBeInstanceOf(ServerDTO::class)
        ->and($servers[0]->rawData['id'])->toBe('srv-1');
});

it('maps a wrapped "data" list response into DTOs', function () {
    Http::fake([
        'papi.copy-trade.io/api/servers' => Http::response([
            'data' => [
                ['id' => 'srv-1'],
            ],
        ], 200),
    ]);

    $servers = app(ServerServiceInterface::class)->getServers();

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->rawData['id'])->toBe('srv-1');
});

it('throws a CopytradeException on a failed request', function () {
    Http::fake([
        'papi.copy-trade.io/api/servers' => Http::response(['message' => 'boom'], 500),
    ]);

    app(ServerServiceInterface::class)->getServers();
})->throws(CopytradeException::class);

it('url-encodes search filter as a query parameter', function () {
    Http::fake([
        'papi.copy-trade.io/api/strategies*' => Http::response([], 200),
    ]);

    app(StrategyServiceInterface::class)->searchStrategies('name eq "A&B"');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/api/strategies?filter=')
            && str_contains($request->url(), '%26')
            && $request['filter'] === 'name eq "A&B"';
    });
});

it('does not send a JSON body on GET requests', function () {
    Http::fake([
        'papi.copy-trade.io/api/servers' => Http::response([], 200),
    ]);

    app(ServerServiceInterface::class)->getServers();

    Http::assertSent(fn (Request $request) => $request->body() === '');
});

it('uploads a copier image and returns the decoded response', function () {
    Http::fake([
        'papi.copy-trade.io/api/profiles/*/copiers/*/image' => Http::response(['url' => 'https://cdn/x.png'], 200),
    ]);

    $result = app(CopierServiceInterface::class)
        ->uploadCopierImage('p-1', 'c-1', 'binary-content', 'x.png');

    expect($result)->toBe(['url' => 'https://cdn/x.png']);
});

it('throws when an image upload fails', function () {
    Http::fake([
        'papi.copy-trade.io/api/profiles/*/copiers/*/image' => Http::response(['message' => 'nope'], 422),
    ]);

    app(CopierServiceInterface::class)
        ->uploadCopierImage('p-1', 'c-1', 'binary-content', 'x.png');
})->throws(CopytradeException::class);

it('fetches a copier\'s open signals as SignalDTOs', function () {
    Http::fake([
        'papi.copy-trade.io/api/copiers/c-1/signals/open' => Http::response([
            ['id' => 'sig-1', 'symbol' => 'EURUSD'],
        ], 200),
    ]);

    $signals = app(CopierServiceInterface::class)->getCopierOpenSignals('c-1');

    expect($signals)->toHaveCount(1)
        ->and($signals[0])->toBeInstanceOf(SignalDTO::class)
        ->and($signals[0]->rawData['id'])->toBe('sig-1');
});

it('fetches a copier\'s closed signals with the date range as query parameters', function () {
    Http::fake([
        'papi.copy-trade.io/api/copiers/c-1/signals/closed*' => Http::response([
            ['id' => 'sig-2'],
        ], 200),
    ]);

    $signals = app(CopierServiceInterface::class)
        ->getCopierClosedSignals('c-1', '2024-05-01', '2024-05-28');

    expect($signals)->toHaveCount(1)
        ->and($signals[0])->toBeInstanceOf(SignalDTO::class);

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/api/copiers/c-1/signals/closed')
            && $request['startDate'] === '2024-05-01'
            && $request['endDate'] === '2024-05-28';
    });
});

it('maps missed signals into SignalDTOs', function () {
    Http::fake([
        'papi.copy-trade.io/api/profiles/p-1/copiers/c-1/signals/missed' => Http::response([
            'data' => [
                ['id' => 'sig-3'],
            ],
        ], 200),
    ]);

    $signals = app(CopierServiceInterface::class)->getMissedSignals('p-1', 'c-1');

    expect($signals)->toHaveCount(1)
        ->and($signals[0])->toBeInstanceOf(SignalDTO::class)
        ->and($signals[0]->rawData['id'])->toBe('sig-3');
});

it('builds the copier image url from configuration', function () {
    config()->set('copytrade.asset_uri', 'https://cdn.example.test');

    $url = app(CopierServiceInterface::class)->getCopierImageUrl('c-1');

    expect($url)->toBe('https://cdn.example.test/images/copiers/thumbnails/c-1');
});
