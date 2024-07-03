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

namespace Brandon14\FossabotCommander\Context;

use function compact;

use Brandon14\FossabotCommander\Contracts\Context\FossabotRole as FossabotRoleInterface;

/**
 * Fossabot role context data model. Defines the ID, name and type of role associated to the user.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotRole extends FossabotDataModel implements FossabotRoleInterface
{
    /**
     * @param string $id   Role ID
     * @param string $name Role name
     * @param string $type Role type
     */
    private function __construct(string $id, string $name, string $type)
    {
        $this->data = compact('id', 'name', 'type');
    }

    /**
     * {@inheritDoc}
     *
     * @param array{
     *     id: string,
     *     name: string,
     *     type: string,
     * } $body API role body
     *
     * @return $this
     */
    public static function createFromBody(array $body): FossabotRoleInterface
    {
        return new self(
            $body['id'],
            $body['name'],
            $body['type'],
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
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * {@inheritDoc}
     */
    public function type(): string
    {
        return $this->data['type'];
    }
}
