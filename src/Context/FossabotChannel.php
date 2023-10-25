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

namespace Brandon14\FossabotCommander\Context;

use Exception;
use DateTimeImmutable;
use Brandon14\FossabotCommander\Contracts\Context\FossabotChannel as FossabotChannelInterface;

/**
 * Fossabot channel context data model. Contains informationa bout the channel in which the custom API was invoked in.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotChannel extends FossabotDataModel implements FossabotChannelInterface
{
    /**
     * @param string            $id              Channel ID
     * @param string            $login           Login name
     * @param string            $displayName     Display name
     * @param string            $avatarUrl       Avatar URL
     * @param string            $slug            Fossabot channel slug
     * @param string            $broadcasterType Broadcaster type
     * @param string            $provider        Provider
     * @param string            $providerId      Provider ID
     * @param DateTimeImmutable $createdAt       Date request was created
     * @param DateTimeImmutable $streamTimestamp Stream timestamp
     * @param bool              $isLive          Whether channel is live
     */
    private function __construct(
        string $id,
        string $login,
        string $displayName,
        string $avatarUrl,
        string $slug,
        string $broadcasterType,
        string $provider,
        string $providerId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $streamTimestamp,
        bool $isLive,
    ) {
        $this->data = compact(
            'id',
            'login',
            'displayName',
            'avatarUrl',
            'slug',
            'broadcasterType',
            'provider',
            'providerId',
            'createdAt',
            'streamTimestamp',
            'isLive',
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param array{
     *     id: string,
     *     login: string,
     *     display_name: string,
     *     avatar: string,
     *     slug: string,
     *     broadcaster_type: string,
     *     provider: string,
     *     provider_id: string,
     *     created_at: string,
     *     stream_timestamp: string,
     *     is_live: bool,
     * } $body
     *
     * @throws Exception
     */
    public static function createFromBody(array $body): FossabotChannelInterface
    {
        return new self(
            $body['id'] ?? null,
            $body['login'] ?? null,
            $body['display_name'] ?? null,
            $body['avatar'] ?? null,
            $body['slug'] ?? null,
            $body['broadcaster_type'] ?? null,
            $body['provider'] ?? null,
            $body['provider_id'] ?? null,
            new DateTimeImmutable($body['created_at']),
            new DateTimeImmutable($body['stream_timestamp']),
            $body['is_live'] ?? null,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function id(): string
    {
        return $this->data['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function login(): string
    {
        return $this->data['login'];
    }

    /**
     * {@inheritDoc}
     */
    public function displayName(): string
    {
        return $this->data['displayName'];
    }

    /**
     * {@inheritDoc}
     */
    public function avatarUrl(): string
    {
        return $this->data['avatarUrl'];
    }

    /**
     * {@inheritDoc}
     */
    public function slug(): string
    {
        return $this->data['slug'];
    }

    /**
     * {@inheritDoc}
     */
    public function broadcasterType(): string
    {
        return $this->data['broadcasterType'];
    }

    /**
     * {@inheritDoc}
     */
    public function provider(): string
    {
        return $this->data['provider'];
    }

    /**
     * {@inheritDoc}
     */
    public function providerId(): string
    {
        return $this->data['providerId'];
    }

    /**
     * {@inheritDoc}
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->data['createdAt'];
    }

    /**
     * {@inheritDoc}
     */
    public function streamTimestamp(): DateTimeImmutable
    {
        return $this->data['streamTimestamp'];
    }

    /**
     * {@inheritDoc}
     */
    public function isLive(): bool
    {
        return $this->data['isLive'];
    }
}
