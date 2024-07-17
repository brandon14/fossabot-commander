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

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Brandon14\FossabotCommander\Contracts\Context\FossabotChannel;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\Exceptions\InvalidArgumentException;
use Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException;

it('handles offsetExists', function () {
    $context = contextDataModel();

    expect($context->offsetExists('channel'))->toBeTrue();
});

it('handles offsetGet', function () {
    $context = contextDataModel();

    expect($context->offsetGet('channel'))->toBeInstanceOf(FossabotChannel::class);
});

it('handles array access exists', function () {
    $context = contextDataModel();

    expect(isset($context['channel']))->toBeTrue();
});

it('handles array access get', function () {
    $context = contextDataModel();

    expect($context['channel'])->toBeInstanceOf(FossabotChannel::class);
});

it('handles magic exists', function () {
    $context = contextDataModel();

    expect(isset($context->channel))->toBeTrue();
});

it('handles magic get', function () {
    $context = contextDataModel();

    expect($context->channel)->toBeInstanceOf(FossabotChannel::class);
});

it('handles get on non-existent property', function () {
    $context = contextDataModel();

    $context->foo;
})->throws(InvalidArgumentException::class);

it('throws on offsetSet', function () {
    $context = contextDataModel();

    $context->offsetSet('channel', null);
})->throws(ImmutableDataModelException::class);

it('throws on offsetUnset', function () {
    $context = contextDataModel();

    $context->offsetUnset('channel', null);
})->throws(ImmutableDataModelException::class);

it('throws on array access set', function () {
    $context = contextDataModel();

    $context['channel'] = null;
})->throws(ImmutableDataModelException::class);

it('throws on array access unset', function () {
    $context = contextDataModel();

    unset($context['channel']);
})->throws(ImmutableDataModelException::class);

it('throws on magic set', function () {
    $context = contextDataModel();

    $context->channel = null;
})->throws(ImmutableDataModelException::class);

it('throws on magic unset', function () {
    $context = contextDataModel();

    unset($context->channel);
})->throws(ImmutableDataModelException::class);

it('casts to string', function () {
    $context = contextDataModel();

    $string = (string) $context;

    expect($string)->toBeString()
        ->and($string)->toMatch('/{.*channel.*}/');
});

it('converts to string', function () {
    $context = contextDataModel();

    $string = $context->toString();

    expect($string)->toBeString()
        ->and($string)->toMatch('/{.*channel.*}/');
});

it('converts to JSON string', function () {
    $context = contextDataModel();

    $string = $context->toJson();

    expect($string)->toBeString()
        ->and($string)->toMatch('/{.*channel.*}/');
});

it('converts to array', function () {
    $context = contextDataModel();

    $array = $context->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('channel');
});

it('serializes via serialize function', function () {
    $context = contextDataModel();

    $serialized = serialize($context);

    expect($serialized)->toBeString()
        ->and($serialized)->toContain('s:7:"channel"');
});

it('unserializes', function () {
    $context = contextDataModel();

    $serialized = serialize($context);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(FossabotContext::class);
});

it('extends Laravel Arrayble', function () {
    $context = contextDataModel();

    expect($context)->toBeInstanceOf(Arrayable::class);
});

it('extends Laravel Jsonable', function () {
    $context = contextDataModel();

    expect($context)->toBeInstanceOf(Jsonable::class);
});

it('extends Stringable', function () {
    $context = contextDataModel();

    expect($context)->toBeInstanceOf(Stringable::class);
});
