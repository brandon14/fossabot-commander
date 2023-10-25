<?php

/**
 * This file is part of the brandon14/fossabot-commander package.
 *
 * MIT License
 *
 * Copyright (c) 2023 Brandon Clothier
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
use Brandon14\FossabotCommander\Contracts\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException;
use Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotCommanderException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException;

it('returns a command', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    $contextResponse = makeResponse(200, [...standardHeaders(), ...messageHeaders()], contextBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $res = $foss->runCommand($command, customToken());

    expect($res)->toEqual('Foo.');
});

it('handles invalid token on validate call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Return an invalid token response during validation.
    $response = makeResponse(400, [...standardHeaders(), ...rateLimitingHeaders()], invalidTokenBody());

    $requestFactory->allows('createRequest')->once()->andReturns($request);
    $httpClient->allows('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('handles rate limiting on validate call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Return a rate limited response.
    $response = makeResponse(429, [...standardHeaders(), ...rateLimitingHeaders()], rateLimitedBody());

    $requestFactory->allows('createRequest')->once()->andReturns($request);
    $httpClient->allows('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    // Check that we get a RateLimitException and that the exception has the correct rate limit information context.
    try {
        $foss->runCommand($command, customToken());
    } catch (RateLimitException $exception) {
        expect($exception)->toBeInstanceOf(RateLimitException::class)
            ->and($exception->remaining())->toEqual(rateLimitRemaining())
            ->and($exception->total())->toEqual(rateLimitTotal())
            ->and($exception->resetsAt()->getTimestamp())->toEqual(rateLimitResetsAt());
    }
});

it('handles exceptions from HTTP client during validation', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));

    $requestFactory->allows('createRequest')->once()->andReturns($request);
    // We want to throw an exception on the validate request.
    $httpClient->allows('sendRequest')->once()->andThrows(new RuntimeException());

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('handles exceptions from HTTP client during context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    // We want to throw a generic exception on the second request when it would be getting the context.
    $httpClient->allows('sendRequest')->twice()->andReturnUsing(static function () use ($response) {
        static $counter = 0;

        switch ($counter++) {
            case 0:
                return $response;
            case 1:
                throw new RuntimeException();
        }

        return true;
    });

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(FossabotCommanderException::class);

it('handles FossabotCommanderException exceptions from HTTP client during context (rethrows)', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);

    $exception = new FossabotCommanderException();
    // We want to throw a generic exception on the second request when it would be getting the context.
    $httpClient->allows('sendRequest')->twice()->andReturnUsing(static function () use ($response, $exception) {
        static $counter = 0;

        switch ($counter++) {
            case 0:
                return $response;
            case 1:
                throw $exception;
        }

        return true;
    });

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    try {
        $foss->runCommand($command, customToken());
    } catch (FossabotCommanderException $exp) {
        // Check that it rethrew the same exception in this case.
        expect($exp)->toBeInstanceOf(FossabotCommanderException::class)
            ->and($exp)->toEqual($exception);
    }
});

it('handles failing to get context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    // Mock invalid token error response on context request.
    $contextResponse = makeResponse(400, [...standardHeaders(), ...messageHeaders()], invalidTokenBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(CannotGetContextException::class);

it('handles getting context with no message property', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    $contextResponse = makeResponse(200, [...standardHeaders(), ...messageHeaders()], contextNoMessageBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $command = Mockery::mock(FossabotCommand::class);

    // Ensure we get a context object with no message.
    $command->allows('getResponse')->once()->withArgs(function ($context) {
        return $context !== null && is_a($context, FossabotContext::class) && $context->message() === null;
    })->andReturn('Foo.');

    $foss->runCommand($command, customToken());
});

it('skips getting context if getContext is false', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());

    $requestFactory->allows('createRequest')->once()->andReturns($request);
    $httpClient->allows('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $command = Mockery::mock(FossabotCommand::class);

    // Ensure we get no context passed to the command since we didn't fetch the context.
    $command->allows('getResponse')->once()->withArgs(function ($context) {
        return $context === null;
    })->andReturn('Foo.');

    $foss->runCommand($command, customToken(), false);
});
it('handles rate limiting on context call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    // Return a rate limited response on the context call.
    $contextResponse = makeResponse(429, [...standardHeaders(), ...rateLimitingHeaders()], rateLimitedBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    // Check that we get a RateLimitException and that the exception has the correct rate limit information context.
    try {
        $foss->runCommand($command, customToken());
    } catch (RateLimitException $exception) {
        expect($exception)->toBeInstanceOf(RateLimitException::class)
            ->and($exception->remaining())->toEqual(rateLimitRemaining())
            ->and($exception->total())->toEqual(rateLimitTotal())
            ->and($exception->resetsAt()->getTimestamp())->toEqual(rateLimitResetsAt());
    }
});

it('throws exception (CannotValidateRequestException) on invalid JSON during validation', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Cut some of JSON string off to give invalid JSON payload.
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], mb_substr(validTokenBody(), 0, -5));

    $requestFactory->allows('createRequest')->once()->andReturns($request);
    $httpClient->allows('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('throws exception (JsonParsingException) on invalid JSON during context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    // Trim some of the context JSON body off to give it invalid JSON.
    $contextResponse = makeResponse(200, [...standardHeaders(), ...messageHeaders()], mb_substr(contextBody(), 0, -5));

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(JsonParsingException::class);

it('handles creating context data with invalid data', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    // Use invalid context data here.
    $contextResponse = makeResponse(200, [...standardHeaders(), ...messageHeaders()], invalidContextBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
})->throws(CannotCreateContextException::class);

it('makes calls to logger if provided', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, [...standardHeaders(), ...rateLimitingHeaders()], validTokenBody());
    $contextResponse = makeResponse(200, [...standardHeaders(), ...messageHeaders()], contextBody());

    $requestFactory->allows('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->allows('sendRequest')->twice()->andReturns($response, $contextResponse);
    // Ensure we make at least one call to the mocked logger.
    $logger->allows('log')->atLeast()->once();

    $foss = new FossabotCommander($httpClient, $requestFactory, $logger);
    $command = new StubCommand();

    $foss->runCommand($command, customToken());
});
