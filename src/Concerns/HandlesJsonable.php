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

namespace Brandon14\FossabotCommander\Concerns;

use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

use Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException;

/**
 * Trait to consolidate functionality of converting a class to JSON representation.
 *
 * @mixin \Brandon14\FossabotCommander\Contracts\Arrayable
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
trait HandlesJsonable
{
    /**
     * {@inheritDoc}
     */
    public function toJson($options = 0): string
    {
        try {
            return json_encode($this->toArray(), JSON_THROW_ON_ERROR | $options);
            // Should never be able to have invalid JSON here unless some property can't be converted to JSON.
        } catch (Throwable $exception) { // @codeCoverageIgnoreStart
            throw new JsonParsingException($exception->getMessage(), $exception->getCode(), $exception);
        } // @codeCoverageIgnoreEnd
    }
}
