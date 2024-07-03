<?php

/**
 * This file is part of the brandon14/fossabot-commander package.
 *
 * MIT License
 *
 * Copyright (c) 2023-2024 Brandon Clothier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Brandon14\FossabotCommander\FossabotCommander;
use Brandon14\FossabotCommander\Tests\Stubs\StubCommand;

it('runs a command', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $res = $foss->runCommand($command, customToken());

    expect($res)->toBeString()
        ->and($res)->toEqual('Foo.');
});

it('gets HTTP client instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $client = $foss->getHttpClient();

    expect($client)->toBeInstanceOf(ClientInterface::class)
        ->and($client)->toBe($httpClient);
});

it('sets HTTP client instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $newClient = Mockery::mock(ClientInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $client = $foss->setHttpClient($newClient)->getHttpClient();

    expect($client)->toBeInstanceOf(ClientInterface::class)
        ->and($client)->toBe($newClient)
        ->and($client)->not()->toBe($httpClient);
});

it('gets request factory instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $factory = $foss->getRequestFactory();

    expect($factory)->toBeInstanceOf(RequestFactoryInterface::class)
        ->and($factory)->toBe($requestFactory);
});

it('sets request factory instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $newFactory = Mockery::mock(RequestFactoryInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $factory = $foss->setRequestFactory($newFactory)->getRequestFactory();

    expect($factory)->toBeInstanceOf(RequestFactoryInterface::class)
        ->and($factory)->toBe($newFactory)
        ->and($factory)->not()->toBe($requestFactory);
});

it('gets logger instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory, $logger);
    $log = $foss->getLogger();

    expect($log)->toBeInstanceOf(LoggerInterface::class)
        ->and($log)->toBe($logger);
});

it('sets logger instance', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $log = $foss->setLog($logger)->getLogger();

    expect($log)->toBeInstanceOf(LoggerInterface::class)
        ->and($log)->toBe($logger);
});

it('enables logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    // Logging enabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, false);
    $foss->enableLogging();
    $logging = $foss->getLogging();

    expect($logging)->toBeTrue();
});

it('disables logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    // Logging enabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);
    $foss->disableLogging();
    $logging = $foss->getLogging();

    expect($logging)->toBeFalse();
});

it('sets logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    // Logging enabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);
    $foss->setLogging(false);
    $logging = $foss->getLogging();

    expect($logging)->toBeFalse();

    // Logging disabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, false);
    $foss->setLogging(true);
    $logging = $foss->getLogging();

    expect($logging)->toBeTrue();
});

it('gets logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    // Logging enabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);
    $logging = $foss->getLogging();

    expect($logging)->toBeTrue();

    // Logging disabled.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, false);
    $logging = $foss->getLogging();

    expect($logging)->toBeFalse();
});

it('gets logging context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    // Logging enabled.
    $foss = new FossabotCommander($httpClient, $requestFactory);
    $context = $foss->getLoggingContext();

    expect($context)->toBeArray();
});
