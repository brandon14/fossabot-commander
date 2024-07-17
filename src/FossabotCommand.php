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

namespace Brandon14\FossabotCommander;

use Throwable;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\FossabotCommand as FossabotCommandInterface;

/**
 * Class to define a response for a Fossabot custom API request. This class is only tasked with returning the response
 * string back to Fossabot, and can use any of the additional context information to make its response.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
abstract class FossabotCommand implements FossabotCommandInterface
{
    /**
     * Get string response for a Fossabot custom API request.
     *
     * **NOTE**: The {@link $context} will only be provided if the
     * {@link \Brandon14\FossabotCommander\Contracts\FossabotCommander::runCommand()} is invoked with the $getContext
     * method as true, which is default.
     *
     * @param \Brandon14\FossabotCommander\Contracts\Context\FossabotContext|null $context Request context
     *
     * @throws Throwable
     *
     * @return string Custom API response
     */
    public function __invoke(?FossabotContext $context = null): string
    {
        return $this->getResponse($context);
    }
}
