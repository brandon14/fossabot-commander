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

use Throwable;
use ReturnTypeWillChange;

use function method_exists;
use function array_key_exists;

use Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException;
use Brandon14\FossabotCommander\Contracts\Exceptions\InvalidArgumentException;
use Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException;
use Brandon14\FossabotCommander\Contracts\Context\FossabotDataModel as FossabotDataModelInterface;

/**
 * Base Fossabot custom API data model.
 *
 * @see    https://docs.fossabot.com/variables/customapi#getting-context
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
abstract class FossabotDataModel implements FossabotDataModelInterface
{
    /**
     * Data model.
     */
    protected array $data = [];

    /**
     * Whether an offset exists.
     *
     * @param string $offset Offset name
     */
    public function offsetExists($offset): bool // @pest-ignore-type
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @param string $offset Offset name
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset) // @pest-ignore-type
    {
        // Check for property named method.
        if (method_exists($this, $offset)) {
            return $this->$offset();
        }

        // This shouldn't ever be run here since all the models have all their property methods.
        // @codeCoverageIgnoreStart
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }
        // @codeCoverageIgnoreEnd

        throw new InvalidArgumentException("Cannot find data model data at offset [{$offset}].");
    }

    /**
     * Offset to set.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $offset Offset name
     * @param mixed  $value  Property value
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException
     */
    public function offsetSet($offset, $value): void // @pest-ignore-type
    {
        throw new ImmutableDataModelException("Cannot mutate Fossabot data model with name [{$offset}].");
    }

    /**
     * Offset to unset.
     *
     * @param string $offset Offset name
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException
     */
    public function offsetUnset($offset): void // @pest-ignore-type
    {
        throw new ImmutableDataModelException("Cannot mutate Fossabot data model with name [{$offset}].");
    }

    /**
     * Serializes data.
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * Unserializes data.
     *
     * @param array $data Unserialized data
     */
    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Gets property.
     *
     * @param string $name Offset name
     */
    public function __get($name) // @pest-ignore-type
    {
        return $this->offsetGet($name);
    }

    /**
     * Sets property.
     *
     * @param string $name  Offset name
     * @param mixed  $value Offset value
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException
     */
    public function __set($name, $value): void // @pest-ignore-type
    {
        throw new ImmutableDataModelException("Cannot mutate Fossabot data model with name [{$name}].");
    }

    /**
     * Checks for existence of property.
     *
     * @param string $name Offset name
     */
    public function __isset($name): bool // @pest-ignore-type
    {
        return $this->offsetExists($name);
    }

    /**
     * Unsets property.
     *
     * @param string $name Offset name
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\ImmutableDataModelException
     */
    public function __unset($name): void // @pest-ignore-type
    {
        throw new ImmutableDataModelException("Cannot mutate Fossabot data model with name [{$name}].");
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return $this->toJson();
    }

    /**
     * Magic method https://www.php.net/manual/en/language.oop5.magic.php#object. toString  allows a class to decide
     * how it will react when it is treated like a string.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(): string
    {
        try {
            return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
            // Should never be able to have invalid JSON here unless some property can't be converted to JSON.
        } catch (Throwable $exception) { // @codeCoverageIgnoreStart
            throw new JsonParsingException($exception->getMessage(), $exception->getCode(), $exception);
        } // @codeCoverageIgnoreEnd
    }
}
