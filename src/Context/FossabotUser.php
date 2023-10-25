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

use Brandon14\FossabotCommander\Contracts\Context\FossabotUser as FossabotUserInterface;

/**
 * Fossabot user context data model. Contains information about the user that invoked the custom API request.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotUser extends FossabotDataModel implements FossabotUserInterface
{
    /**
     * @param string $providerId  Provider ID
     * @param string $login       Login name
     * @param string $displayName Display name
     * @param array  $roles       User roles
     */
    private function __construct(
        string $providerId,
        string $login,
        string $displayName,
        array $roles = [],
    ) {
        $this->data = compact('providerId', 'login', 'displayName', 'roles');
    }

    /**
     * {@inheritDoc}
     *
     * @param array{
     *     provider_id: string,
     *     login: string,
     *     display_name: string,
     *     roles: array,
     * } $body
     */
    public static function createFromBody(array $body): FossabotUserInterface
    {
        $roles = [];

        foreach ($body['roles'] ?? [] as $role) {
            $roles[] = FossabotRole::createFromBody($role);
        }

        return new self(
            $body['provider_id'] ?? null,
            $body['login'] ?? null,
            $body['display_name'] ?? null,
            $roles,
        );
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
    public function roles(): array
    {
        return $this->data['roles'];
    }
}
