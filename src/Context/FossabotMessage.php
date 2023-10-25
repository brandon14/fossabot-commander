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

use function compact;

use Brandon14\FossabotCommander\Contracts\Context\FossabotUser as FossabotUserInterface;
use Brandon14\FossabotCommander\Contracts\Context\FossabotMessage as FossabotMessageInterface;

/**
 * Fossabot message context data model. This is only present when the custom API request was invoked from a chat message
 * or command. When dispatched from an automated action, such as a timer, this will be null.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotMessage extends FossabotDataModel implements FossabotMessageInterface
{
    /**
     * @param string                                                      $id       Message ID
     * @param string                                                      $content  Message content
     * @param string                                                      $provider Provider
     * @param \Brandon14\FossabotCommander\Contracts\Context\FossabotUser $user     User data
     */
    private function __construct(
        string $id,
        string $content,
        string $provider,
        FossabotUserInterface $user,
    ) {
        $this->data = compact('id', 'content', 'provider', 'user');
    }

    /**
     * {@inheritDoc}
     *
     * @param array{
     *     id: string,
     *     content: string,
     *     provider: string,
     *     user: array,
     * } $body
     */
    public static function createFromBody(array $body): FossabotMessageInterface
    {
        return new self(
            $body['id'] ?? null,
            $body['content'] ?? null,
            $body['provider'] ?? null,
            FossabotUser::createFromBody($body['user'] ?? null),
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
    public function content(): string
    {
        return $this->data['content'];
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
    public function user(): FossabotUserInterface
    {
        return $this->data['user'];
    }
}
