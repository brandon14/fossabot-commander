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

namespace Brandon14\FossabotCommander\Contracts\Exceptions;

use Throwable;

/**
 * Exception thrown when making a Fossabot API call results in a 400 response status.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class InvalidTokenException extends FossabotApiException
{
    /**
     * @param string         $fossabotCode Error code from Fossabot API response
     * @param string         $errorClass   Error class (error param) from Fossabot API response
     * @param string         $errorMessage Error message from Fossabot API response
     * @param array|null     $body         Parsed HTTP body from Fossabot API exception
     * @param Throwable|null $previous     Previous exception
     */
    public function __construct(
        string $fossabotCode,
        string $errorClass,
        string $errorMessage,
        array|null $body = null,
        Throwable|null $previous = null,
    ) {
        parent::__construct(
            $fossabotCode,
            $errorClass,
            $errorMessage,
            400,
            $body,
            $previous,
        );
    }
}
