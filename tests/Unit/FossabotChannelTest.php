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

it('gets ID', function () {
    $channel = contextDataModel()->channel();

    expect($channel->id())->toBeString()
        ->and($channel->id())->not()->toBeEmpty();
});

it('gets login name', function () {
    $channel = contextDataModel()->channel();

    expect($channel->login())->toBeString()
        ->and($channel->login())->not()->toBeEmpty();
});

it('gets display name', function () {
    $channel = contextDataModel()->channel();

    expect($channel->displayName())->toBeString()
        ->and($channel->displayName())->not()->toBeEmpty();
});

it('gets avatarUrl', function () {
    $channel = contextDataModel()->channel();

    expect($channel->avatarUrl())->toBeString()
        ->and($channel->avatarUrl())->not()->toBeEmpty();
});

it('gets slug', function () {
    $channel = contextDataModel()->channel();

    expect($channel->slug())->toBeString()
        ->and($channel->slug())->not()->toBeEmpty();
});

it('gets broadcasterType', function () {
    $channel = contextDataModel()->channel();

    expect($channel->broadcasterType())->toBeString()
        ->and($channel->broadcasterType())->not()->toBeEmpty();
});

it('gets provider', function () {
    $channel = contextDataModel()->channel();

    expect($channel->provider())->toBeString()
        ->and($channel->provider())->not()->toBeEmpty();
});

it('gets provider ID', function () {
    $channel = contextDataModel()->channel();

    expect($channel->providerId())->toBeString()
        ->and($channel->providerId())->not()->toBeEmpty();
});

it('gets created at', function () {
    $channel = contextDataModel()->channel();

    expect($channel->createdAt())->toBeInstanceOf(DateTimeInterface::class);
});

it('gets stream timestamp', function () {
    $channel = contextDataModel()->channel();

    expect($channel->streamTimestamp())->toBeInstanceOf(DateTimeInterface::class);
});

it('gets is live', function () {
    $channel = contextDataModel()->channel();

    expect($channel->isLive())->toBeBool();
});
