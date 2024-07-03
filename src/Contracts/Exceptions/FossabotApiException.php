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

namespace Brandon14\FossabotCommander\Contracts\Exceptions;

use Throwable;

use function json_encode;

/**
 * Generic Fossabot API exception thrown when making Fossabot custom API requests.
 *
 * @noinspection PhpClassNamingConventionInspection
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class FossabotApiException extends FossabotCommanderException
{
    /**
     * Error code from Fossabot API response.
     */
    private string $fossabotCode;

    /**
     * Error class (error param) from Fossabot API response.
     */
    private string $errorClass;

    /**
     * Error message from Fossabot API response.
     */
    private string $errorMessage;

    /**
     * Parsed HTTP body from Fossabot API exception.
     */
    private ?array $body;

    /**
     * HTTP status code from Fossabot API exception.
     */
    private int $statusCode;

    /**
     * Constructs a new FossabotApiException class.
     *
     * @param string         $fossabotCode Error code from Fossabot API response
     * @param string         $errorClass   Error class (error param) from Fossabot API response
     * @param string         $errorMessage Error message from Fossabot API response
     * @param int            $statusCode   HTTP status code from Fossabot API exception
     * @param array|null     $body         Parsed HTTP body from Fossabot API exception
     * @param Throwable|null $previous     Previous exception
     */
    public function __construct(
        string $fossabotCode,
        string $errorClass,
        string $errorMessage,
        int $statusCode,
        ?array $body = null,
        ?Throwable $previous = null
    ) {
        $this->fossabotCode = $fossabotCode;
        $this->errorClass = $errorClass;
        $this->errorMessage = $errorMessage;
        $this->statusCode = $statusCode;
        $this->body = $body;

        $message = "Fossabot API error occurred with status code [{$statusCode}] and message [{$errorMessage}].";

        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Error code from Fossabot API response.
     *
     * @return string Fossabot error code
     */
    public function fossabotCode(): string
    {
        return $this->fossabotCode;
    }

    /**
     * Error class (error param) from Fossabot API response.
     *
     * @return string Error class
     */
    public function errorClass(): string
    {
        return $this->errorClass;
    }

    /**
     * Error message from Fossabot API response.
     *
     * @return string Error message
     */
    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * HTTP status code from Fossabot API exception.
     *
     * @return int HTTP status code
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Parsed HTTP body from Fossabot API exception.
     *
     * @return array|null HTTP body
     */
    public function body(): ?array
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $parent = parent::__toString();

        if ($this->body === null) {
            return $parent;
        }

        $body = '';

        // Try to parse the body into a JSON string to add to the string representation of the exception.
        try {
            $body = json_encode($this->body, JSON_THROW_ON_ERROR);
            // We ignore this catch here since in our code before any FossabotApiException is made, the body would
            // have already been either validated as valid JSON, or an exception would have been thrown.
        } /** @noinspection BadExceptionsProcessingInspection */ catch (Throwable $exception) { // @codeCoverageIgnoreStart
            // Ignore exception and don't add on body.
        } // @codeCoverageIgnoreEnd

        // Add in full Fossabot response to string representation of exception.
        return "{$parent} Response: {$body}";
    }
}
