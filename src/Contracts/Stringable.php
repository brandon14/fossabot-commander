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

// Exclude PHP Code Sniffer standards here.
// @codingStandardsIgnoreStart

namespace {
    // Polyfill the Stringable interface.
    if (\PHP_VERSION_ID < 80000) {
        /**
         * The Stringable interface denotes a class as having a __toString() method. Unlike most interfaces, Stringable
         * is implicitly present on any class that has the magic __toString() method defined, although it can and should
         * be declared explicitly.
         *
         * Its primary value is to allow functions to type check against the union type string|Stringable to accept
         * either a string primitive or an object that can be cast to a string.
         *
         * @see https://www.php.net/manual/en/class.stringable.php
         */
        interface Stringable
        {
            /**
             * String representation of the object.
             *
             * @noinspection MissingReturnTypeInspection
             *
             * @return string String representation of the object
             */
            public function __toString();
        }
    }
}

// Back to our namespace after polyfilling in the global Stringable interface.

namespace Brandon14\FossabotCommander\Contracts {
    use Stringable as BaseStringable;

    /**
     * Defines a "stringable" class which is one that can be converted to a string or cast to a string.
     *
     * @author Brandon Clothier <brandon14125@gmail.com>
     */
    interface Stringable extends BaseStringable
    {
        /**
         * {@inheritDoc}
         */
        public function __toString();

        /**
         * String representation of the object. For complex objects, this would normally be a JSON string.
         *
         * @return string String representation of the object
         */
        public function toString(): string;
    }
}

// @codingStandardsIgnoreEnd
