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
use Brandon14\FossabotCommander\Contracts\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotCommanderException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotExecuteCommandException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException;
use Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException;

it('returns a command', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $res = $foss->runCommand($command, customToken());

    expect($res)->toEqual($response);
});

it('handles invalid token on validate call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Return an invalid token response during validation.
    $response = makeResponse(400, array_merge(standardHeaders(), rateLimitingHeaders()), invalidTokenBody());

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    $httpClient->shouldReceive('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('handles rate limiting on validate call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Return a rate limited response.
    $response = makeResponse(429, array_merge(standardHeaders(), rateLimitingHeaders()), rateLimitedBody());

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    $httpClient->shouldReceive('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

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

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    // We want to throw an exception on the validate request.
    $httpClient->shouldReceive('sendRequest')->once()->andThrows(new RuntimeException());

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('handles non-200, non-400, non-429 exceptions during validation', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Mock a 500 error when validating token.
    $response = makeResponse(500, array_merge(standardHeaders(), rateLimitingHeaders()), invalidTokenBody());

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    $httpClient->shouldReceive('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('handles exceptions from HTTP client during context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    // We want to throw a generic exception on the second request when it would be getting the context.
    $httpClient->shouldReceive('sendRequest')->twice()->andReturnUsing(static function () use ($response) {
        static $counter = 0;

        switch ($counter++) {
            case 0:
                return $response;
            case 1:
                throw new RuntimeException('Foo.');
        }

        return true;
    });

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(FossabotCommanderException::class);

it('handles FossabotCommanderException exceptions from HTTP client during context (rethrows as CannotGetContextException)', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);

    $exception = new FossabotCommanderException('Foo.');
    // We want to throw a generic exception on the second request when it would be getting the context.
    $httpClient->shouldReceive('sendRequest')->twice()->andReturnUsing(static function () use ($response, $exception) {
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

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotGetContextException::class);

it('handles failing to get context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Mock invalid token error response on context request.
    $contextResponse = makeResponse(400, array_merge(standardHeaders(), messageHeaders()), invalidTokenBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotGetContextException::class);

it('handles non-200, non-400, non-429 exceptions during context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    // Return an invalid token response during validation.
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Mock a 500 error when getting context.
    $contextResponse = makeResponse(500, array_merge(standardHeaders(), messageHeaders()), invalidTokenBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotGetContextException::class);

it('handles getting context with no message property', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextNoMessageBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $command = Mockery::mock(FossabotCommand::class);

    // Ensure we get a context object with no message.
    $command->shouldReceive('getResponse')->once()->withArgs(function ($context) {
        return $context !== null && is_a($context, FossabotContext::class) && $context->message() === null;
    })->andReturn('This is a test response.');

    $foss->runCommand($command, customToken());
});

it('skips getting context if getContext is false', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    $httpClient->shouldReceive('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $command = Mockery::mock(FossabotCommand::class);

    // Ensure we get no context passed to the command since we didn't fetch the context.
    $command->shouldReceive('getResponse')->once()->withArgs(function ($context) {
        return $context === null;
    })->andReturn('This is a test response.');

    $foss->runCommand($command, customToken(), false);
});
it('handles rate limiting on context call', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Return a rate limited response on the context call.
    $contextResponse = makeResponse(429, array_merge(standardHeaders(), rateLimitingHeaders()), rateLimitedBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

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

it('handles rate limiting when no rate limiting headers are found', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Return a rate limited response on the context call. Don't provide rate limiting headers.
    $contextResponse = makeResponse(429, array_merge(standardHeaders(), []), rateLimitedBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    // Check that we get a InvalidStatusException and the exception code is 429 for rate limiting.
    try {
        $foss->runCommand($command, customToken());
    } catch (CannotGetContextException $exception) {
        expect($exception)->toBeInstanceOf(CannotGetContextException::class)
            ->and($exception->getCode())->toEqual(429);
    }
});

it('throws exception (CannotValidateRequestException) on invalid JSON during validation', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    // Cut some of JSON string off to give invalid JSON payload.
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), mb_substr(validTokenBody(), 0, -5));

    $requestFactory->shouldReceive('createRequest')->once()->andReturns($request);
    $httpClient->shouldReceive('sendRequest')->once()->andReturns($response);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotValidateRequestException::class);

it('throws exception (CannotGetContextException) on invalid JSON during context', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Trim some of the context JSON body off to give it invalid JSON.
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), mb_substr(contextBody(), 0, -5));

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotGetContextException::class);

it('handles creating context data with invalid data', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    // Use invalid context data here.
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), invalidContextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
})->throws(CannotCreateContextException::class);

it('handles exceptions thrown from FossabotCommand::getResponse()', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);

    $foss = new FossabotCommander($httpClient, $requestFactory);

    // Command will throw an exception when executed.
    $response = 'This is a test response.';
    $command = getStubCommand($response, new RuntimeException('This is an exception.'));

    $foss->runCommand($command, customToken());
})->throws(CannotExecuteCommandException::class);

it('makes calls to logger if provided', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);
    // Ensure we make at least one call to the mocked logger.
    $logger->shouldReceive('log')->atLeast()->once();

    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
});

it('disables logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);
    // Ensure we don't make a call to log since logging is disabled.
    $logger->shouldReceive('log')->never();

    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);
    $foss->disableLogging();

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
});

it('enables logging', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $request = makeRequest(getFossabotUrl('/validate/'.customToken()));
    $contextRequest = makeRequest(getFossabotUrl('/context/'.customToken()));
    $response = makeResponse(200, array_merge(standardHeaders(), rateLimitingHeaders()), validTokenBody());
    $contextResponse = makeResponse(200, array_merge(standardHeaders(), messageHeaders()), contextBody());

    $requestFactory->shouldReceive('createRequest')->twice()->andReturns($request, $contextRequest);
    $httpClient->shouldReceive('sendRequest')->twice()->andReturns($response, $contextResponse);
    // Ensure we make at least one call to the mocked logger.
    $logger->shouldReceive('log')->atLeast()->once();

    // Disable logging when creating, so we can enable it later.
    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, false);
    $foss->enableLogging();

    $response = 'This is a test response.';
    $command = getStubCommand($response);

    $foss->runCommand($command, customToken());
});

it('only allows logging enabled when a valid PSR logger is provided via constructor', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    // Enable logging in constructor, but don't provide PSR logger.
    new FossabotCommander($httpClient, $requestFactory, null, true);
})->throws(NoValidLoggerProvidedException::class);

it('only allows logging enabled when a valid PSR logger is provided via enableLogging method', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory, null, false);
    // Enable logging via method, with no PSR logger provided.
    $foss->enableLogging();
})->throws(NoValidLoggerProvidedException::class);

it('only allows setting null logger when logging is disabled', function () {
    $httpClient = Mockery::mock(ClientInterface::class);
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $foss = new FossabotCommander($httpClient, $requestFactory, $logger, true);
    // Logging is enabled, but we tried to null out the logger instance, should throw exception.
    $foss->setLog(null);
})->throws(NoValidLoggerProvidedException::class);
