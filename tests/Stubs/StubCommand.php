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

namespace Brandon14\FossabotCommander\Tests\Stubs;

use Throwable;
use Brandon14\FossabotCommander\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;

/**
 * Stubbed command used for testing.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class StubCommand extends FossabotCommand
{
    /**
     * Optional exception to be thrown during the response.
     */
    private ?Throwable $exception;

    /**
     * @param Throwable|null $exception Optional exception to throw during response
     */
    public function __construct(?Throwable $exception = null)
    {
        $this->exception = $exception;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse(?FossabotContext $context = null): string
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return 'Foo.';
    }
}
