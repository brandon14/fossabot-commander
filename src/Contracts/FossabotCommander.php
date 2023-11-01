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

namespace Brandon14\FossabotCommander\Contracts;

use Psr\Log\LoggerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Main class to invoke a given {@link \Brandon14\FossabotCommander\Contracts\FossabotCommand} instance.
 *
 * @see https://docs.fossabot.com/variables/customapi
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface FossabotCommander
{
    /**
     * Get {@link \Psr\Http\Client\ClientInterface} instance.
     *
     * @return \Psr\Http\Client\ClientInterface PSR HTTP client instance
     */
    public function getHttpClient(): ClientInterface;

    /**
     * Set a new {@link \Psr\Http\Client\ClientInterface} instance.
     *
     * @param \Psr\Http\Client\ClientInterface $httpClient PSR HTTP client interface
     *
     * @return $this
     */
    public function setHttpClient(ClientInterface $httpClient): self;

    /**
     * Get {@link \Psr\Http\Message\RequestFactoryInterface} instance.
     *
     * @return \Psr\Http\Message\RequestFactoryInterface Request factory instance
     */
    public function getRequestFactory(): RequestFactoryInterface;

    /**
     * Set a new {@link \Psr\Http\Message\RequestFactoryInterface} instance.
     *
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory PSR request factory instance
     *
     * @return $this
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): self;

    /**
     * Get {@link \Psr\Log\LoggerInterface} instance.
     *
     * @return \Psr\Log\LoggerInterface|null Logger instance
     */
    public function getLogger(): ?LoggerInterface;

    /**
     * Fluent setter for setting a new {@link \Psr\Log\LoggerInterface} instance.
     *
     * @param \Psr\Log\LoggerInterface|null $logger PSR logger instance
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException
     *
     * @return $this
     */
    public function setLog(?LoggerInterface $logger): self;

    /**
     * Enable logging. Will throw an {@link \Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException}
     * when enabling this without providing a valid {@link \Psr\Log\LoggerInterface} instance.
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException
     *
     * @return $this
     */
    public function enableLogging(): self;

    /**
     * Disables logging for the {@link \Brandon14\FossabotCommander\Contracts\FossabotCommander} instance.
     */
    public function disableLogging(): self;

    /**
     * Sets whether to log or not for the {@link \Brandon14\FossabotCommander\Contracts\FossabotCommander} instance.
     * If enabling, and no valid PSR logging is provided, it will throw an exception.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $logging Whether to enable logging or not
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException
     */
    public function setLogging(bool $logging): self;

    /**
     * Gets the configured logging setting.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @return bool Logging
     */
    public function getLogging(): bool;

    /**
     * Will execute a given {@link \Brandon14\FossabotCommander\Contracts\FossabotCommand} instance. If this method
     * throws a {@link \Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException}, then it was
     * unable to verify that the request came from Fossabot, and should be treated as a critical error.
     *
     * If this method throws a {@link \Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException} then that
     * means that Fossabot's API is throttling your requests, adn you should check the rate limit parameters available
     * in the exception to determine how to handle it.
     *
     * To get the {@link $customApiToken}, you can obtain it from the Fossabot request header
     * 'x-fossabot-customapitoken'.
     *
     * @see https://docs.fossabot.com/variables/customapi#list-of-common-headers
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param \Brandon14\FossabotCommander\Contracts\FossabotCommand $command        Fossabot command instance
     * @param string                                                 $customApiToken Fossabot API token to validate and
     *                                                                               get data from
     * @param bool                                                   $getContext     Whether to fetch additional context
     *                                                                               from Fossabot API before invoking
     *                                                                               command
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException
     *
     * @return string Text response to return to Fossabot
     */
    public function runCommand(
        FossabotCommand $command,
        string $customApiToken,
        bool $getContext = true
    ): string;

    /**
     * Gets general class information used for providing additional logging context.
     */
    public function getLoggingContext(): array;
}
