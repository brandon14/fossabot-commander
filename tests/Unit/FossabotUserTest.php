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

it('gets provider ID', function () {
    $user = contextDataModel()->message()->user();

    expect($user->providerId())->toBeString()
        ->and($user->providerId())->not()->toBeEmpty();
});

it('gets login name', function () {
    $user = contextDataModel()->message()->user();

    expect($user->login())->toBeString()
        ->and($user->login())->not()->toBeEmpty();
});

it('gets display name', function () {
    $user = contextDataModel()->message()->user();

    expect($user->displayName())->toBeString()
        ->and($user->displayName())->not()->toBeEmpty();
});

it('gets roles', function () {
    $user = contextDataModel()->message()->user();

    expect($user->roles())->toBeArray()
        ->and($user->roles())->not()->toBeEmpty();
});

it('gets roles (no roles present)', function () {
    $user = contextNoRolesDataModel()->message()->user();

    expect($user->roles())->toBeArray()
        ->and($user->roles())->toBeEmpty();
});
