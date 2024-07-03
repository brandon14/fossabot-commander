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

/**
 * Fossabot message context data model. This is only present when the custom API request was invoked from a chat message
 * or command. When dispatched from an automated action, such as a timer, this will be null.
 *
 * @see https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface FossabotMessage extends FossabotDataModel
{
    /**
     * Message ID.
     */
    public function id(): string;

    /**
     * Message contents.
     */
    public function content(): string;

    /**
     * Message prvider (i.e. twitch, etc.).
     */
    public function provider(): string;

    /**
     * User who invoked the message.
     */
    public function user(): FossabotUser;
}
