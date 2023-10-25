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

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Brandon14\FossabotCommander\FossabotCommander;
use Brandon14\FossabotCommander\Tests\Stubs\StubCommand;

it('runs a command', function () {
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

    expect($res)->toBeString()
        ->and($res)->toEqual('Foo.');
});
