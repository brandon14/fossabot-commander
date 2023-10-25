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

use Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException;
use Brandon14\FossabotCommander\Contracts\Exceptions\InvalidTokenException;

it('contains Fossabot error code on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->fossabotCode())->toEqual('token_invalid');
});

it('contains error class on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->errorClass())->toEqual('Bad Request');
});

it('contains error message on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->errorMessage())->toEqual('Invalid token');
});

it('contains error status on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->statusCode())->toEqual(400);
});

it('contains raw body array on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->body())->toBeArray()
        ->and($exception->body())->toHaveKeys(['code', 'error', 'message', 'status']);
});

it('contains raw body (null body) array on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
    );

    expect($exception->body())->toBeNull();
});

it('contains response body on FossabotApiException string cast', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    $exception = (string) $exception;

    expect($exception)->toMatch('/Response: {.*code.*error.*message.*status.*}/');
});

it('skips adding response body to string cast on FossabotApiException', function () {
    $exception = new FossabotApiException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        400,
    );

    $exception = (string) $exception;

    expect($exception)->not()->toMatch('/Response: {.*code.*error.*message.*status.*}/');
});

it('sets InvalidTokenException status to 400', function () {
    $exception = new InvalidTokenException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        [
            'code' => 'token_invalid',
            'error' => 'Bad Request',
            'message' => 'Invalid token',
            'status' => 400,
        ],
    );

    expect($exception->statusCode())->toEqual(400);
});

it('sets RatelimitException status to 429', function () {
    $body = json_decode(rateLimitedBody(), true);

    $exception = new RateLimitException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        rateLimitTotal(),
        rateLimitRemaining(),
        rateLimitResetsAt(),
        $body,
    );

    expect($exception->statusCode())->toEqual(429);
});

it('contains rate limit total on RateLimitException', function () {
    $body = json_decode(rateLimitedBody(), true);

    $exception = new RateLimitException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        rateLimitTotal(),
        rateLimitRemaining(),
        rateLimitResetsAt(),
        $body,
    );

    expect($exception->total())->toEqual(rateLimitTotal());
});

it('contains rate limit remaining on RateLimitException', function () {
    $body = json_decode(rateLimitedBody(), true);

    $exception = new RateLimitException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        rateLimitTotal(),
        rateLimitRemaining(),
        rateLimitResetsAt(),
        $body,
    );

    expect($exception->remaining())->toEqual(rateLimitRemaining());
});

it('contains rate limit resets at timestamp on RateLimitException', function () {
    $body = json_decode(rateLimitedBody(), true);

    $exception = new RateLimitException(
        'token_invalid',
        'Bad Request',
        'Invalid token',
        rateLimitTotal(),
        rateLimitRemaining(),
        rateLimitResetsAt(),
        $body,
    );

    expect($exception->resetsAt()->getTimestamp())->toEqual(rateLimitResetsAt());
});
