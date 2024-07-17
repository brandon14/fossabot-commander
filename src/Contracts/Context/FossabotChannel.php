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

namespace Brandon14\FossabotCommander\Contracts\Context;

use DateTimeImmutable;

/**
 * Fossabot channel context data model. Contains informationa bout the channel in which the custom API was invoked in.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface FossabotChannel extends FossabotDataModel
{
    /**
     * Channel ID.
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function id(): string;

    /**
     * Channel log in name. This is the lowercase version of the broadcaster's username.
     */
    public function login(): string;

    /**
     * The uppercased or internationalized version of the boradcaster's username.
     */
    public function displayName(): string;

    /**
     * URL to the user's profile avatar.
     */
    public function avatarUrl(): string;

    /**
     * The fossabot channel URL of the broadcaster.
     */
    public function slug(): string;

    /**
     * Broadcaster type (i.e. affiliate, partner, etc.).
     */
    public function broadcasterType(): string;

    /**
     * Broadcaster provider (i.e. twitch, etc.).
     */
    public function provider(): string;

    /**
     * Provider ID.
     */
    public function providerId(): string;

    /**
     * Time the request was created at.
     */
    public function createdAt(): DateTimeImmutable;

    /**
     * Stream timestamp.
     */
    public function streamTimestamp(): DateTimeImmutable;

    /**
     * Whether channel is live or not currently.
     */
    public function isLive(): bool;
}
