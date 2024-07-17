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

namespace Brandon14\FossabotCommander\Contracts;

use function interface_exists;

// Exclude PHP Code Sniffer standards here.
// @codingStandardsIgnoreStart

// Extends Laravel Jsonable interface if it exists, otherwise we define our own.
if (interface_exists('\Illuminate\Contracts\Support\Jsonable')) {
    /**
     * Contract to define a "Jsonable" object that can be converted to a JSON string.
     *
     * @author Brandon Clothier <brandon14125@gmail.com>
     */
    interface Jsonable extends \Illuminate\Contracts\Support\Jsonable
    {
        // Intentionally left blank.
    }
} else {
    /**
     * Contract to define a "Jsonable" object that can be converted to a JSON string.
     *
     * @author Brandon Clothier <brandon14125@gmail.com>
     */
    interface Jsonable
    {
        /**
         * Convert the object to its JSON representation.
         *
         * @param int $options JSON encode options
         *
         * @return string JSON string
         */
        public function toJson($options = 0);
    }
}

// @codingStandardsIgnoreEnd
