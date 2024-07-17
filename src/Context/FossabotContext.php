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

use Exception;
use Brandon14\FossabotCommander\Contracts\Context\FossabotChannel as FossabotChannelInterface;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext as FossabotContextInterface;
use Brandon14\FossabotCommander\Contracts\Context\FossabotMessage as FossabotMessageInterface;

/**
 * Fossabot API context data model. Contains additional context about the Fossabot custom API request for more rich
 * integrations.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotContext extends FossabotDataModel implements FossabotContextInterface
{
    /**
     * Constructs a new FossabotContext class.
     *
     * @param \Brandon14\FossabotCommander\Context\FossabotChannel      $channel Channel data
     * @param \Brandon14\FossabotCommander\Context\FossabotMessage|null $message Message data
     */
    private function __construct(FossabotChannel $channel, ?FossabotMessage $message = null)
    {
        $this->data['channel'] = $channel;
        $this->data['message'] = $message;
    }

    /**
     * {@inheritDoc}
     *
     * @param array{
     *     channel: array{
     *         id: string,
     *         login: string,
     *         display_name: string,
     *         avatar: string,
     *         slug: string,
     *         broadcaster_type: string,
     *         provider: string,
     *         provider_id: string,
     *         created_at: string,
     *         stream_timestamp: string,
     *         is_live: bool,
     *     },
     *     message: array{
     *         id: string,
     *         content: string,
     *         provider: string,
     *         user: array{
     *             provider_id: string,
     *             login: string,
     *             display_name: string,
     *             roles: array{
     *                 array{
     *                     id: string,
     *                     name: string,
     *                     type: string,
     *                 },
     *             },
     *         },
     *     }|null
     * } $body
     *
     * @throws Exception
     */
    public static function createFromBody(array $body): FossabotContextInterface
    {
        return new self(
            FossabotChannel::createFromBody($body['channel'] ?? []),
            isset($body['message']) ? FossabotMessage::createFromBody($body['message']) : null,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function channel(): FossabotChannelInterface
    {
        return $this->data['channel'];
    }

    /**
     * {@inheritDoc}
     */
    public function message(): ?FossabotMessageInterface
    {
        return $this->data['message'] ?? null;
    }
}
