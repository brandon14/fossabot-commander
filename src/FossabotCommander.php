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

use function compact;

use DateTimeImmutable;
use DateTimeInterface;

use function get_class;
use function urlencode;

use Psr\Log\LoggerTrait;

use function array_merge;
use function json_decode;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use function date_create_immutable;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Brandon14\FossabotCommander\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException;
use Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException;
use Brandon14\FossabotCommander\Contracts\Exceptions\InvalidTokenException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotExecuteCommandException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException;
use Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException;
use Brandon14\FossabotCommander\Contracts\FossabotCommander as FossabotCommanderInterface;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext as FossabotContextInterface;

/**
 * Main class to invoke a given {@link \Brandon14\FossabotCommander\Contracts\FossabotCommand} instance.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class FossabotCommander implements FossabotCommanderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * Fossabot API base URL.
     */
    private const FOSSABOT_API_BASE_URL = 'https://api.fossabot.com/v2/customapi';

    /**
     * PSR HTTP client instance.
     */
    private ClientInterface $httpClient;

    /**
     * PSR HTTP request factory instance.
     */
    private RequestFactoryInterface $requestFactory;

    /**
     * Whether to enable logging or not.
     */
    private bool $logging = false;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param \Psr\Http\Client\ClientInterface          $httpClient     PSR HTTP client instance
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory PSR HTTP request factory instance
     * @param \Psr\Log\LoggerInterface|null             $logger         PSR logger instance
     * @param bool                                      $logging        Whether to enable logging or not
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\NoValidLoggerProvidedException
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        ?LoggerInterface $logger = null,
        bool $logging = false
    ) {
        $this->setHttpClient($httpClient)
            ->setRequestFactory($requestFactory)
            ->setLog($logger)
            ->setLogging($logging);
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function setHttpClient(ClientInterface $httpClient): FossabotCommanderInterface
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): FossabotCommanderInterface
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setLog(?LoggerInterface $logger): FossabotCommanderInterface
    {
        if ($this->logging && $logger === null) {
            throw new NoValidLoggerProvidedException('No PSR compliant logger provided.');
        }

        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function enableLogging(): FossabotCommanderInterface
    {
        return $this->setLogging(true);
    }

    /**
     * {@inheritDoc}
     */
    public function disableLogging(): FossabotCommanderInterface
    {
        return $this->setLogging(false);
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * {@inheritDoc}
     */
    public function setLogging(bool $logging): FossabotCommanderInterface
    {
        if ($logging === true && $this->logger === null) {
            throw new NoValidLoggerProvidedException('No PSR compliant logger provided.');
        }

        $this->logging = $logging;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLogging(): bool
    {
        return $this->logging;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * {@inheritDoc}
     */
    public function runCommand(
        FossabotCommand $command,
        string $customApiToken,
        bool $getContext = true
    ): string {
        $context = null;
        $body = null;

        $this->info('Sending request to validate incoming Fossabot request.');

        try {
            // Send validate request. Will throw exception if unable to validate.
            $this->sendValidateRequest($customApiToken);

            $this->info('Validated Fossabot request.');
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception during validation with message [{$exception->getMessage()}].",
                compact('exception'),
            );

            // Allow rate limit exceptions to be rethrown here.
            if ($exception instanceof RateLimitException) {
                $this->debug('Rethrowing ['.RateLimitException::class.'] exception.');

                throw $exception;
            }

            $this->debug('Transforming exception of class ['.get_class($exception).'] to ['.CannotValidateRequestException::class.'].');

            // Rethrow API exception as a CannotValidateRequestException.
            if ($exception instanceof FossabotApiException) {
                throw new CannotValidateRequestException($exception->fossabotCode(), $exception->errorClass(), $exception->errorMessage(), $exception->statusCode(), $exception->body(), $exception);
            }

            // Transform all other exceptions into a CannotValidateRequestException.
            throw new CannotValidateRequestException('unknown', 'unknown_error', $exception->getMessage(), 400, $body ?? null, $exception);
        }

        // Get context if needed.
        if ($getContext) {
            $context = $this->getContext($customApiToken);
        }

        $this->info('Invoking FossabotCommand.', [
            'command' => get_class($command),
        ]);

        // Invoke command.
        try {
            return $command->getResponse($context);
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
            $this->error(
                "Caught exception during command execution with message [{$message}].",
                compact('exception'),
            );

            $this->debug(
                'Transforming exception of class ['.get_class($exception).'] to ['.CannotExecuteCommandException::class.'].'
            );

            throw new CannotExecuteCommandException($message, $exception->getCode(), $exception);
        }
    }

    /**
     * Makes and sends a request to validate the Fossabot custom API token provided in the request.
     *
     * @param string $customApiToken Fossabot custom API token
     *
     * @throws Throwable
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\InvalidTokenException
     */
    private function sendValidateRequest(string $customApiToken): void
    {
        // Validate Fossabot request using the validate API call.
        ['body' => $body, 'statusCode' => $statusCode, 'headers' => $headers] = $this->sendRequest(
            self::FOSSABOT_API_BASE_URL.'/validate/'.urlencode($customApiToken)
        );

        if ($statusCode !== 200) {
            $this->info("Received non-200 HTTP response code [{$statusCode}] back from validation.");

            throw $this->getExceptionFromBody($body, $statusCode, $headers);
        }
    }

    /**
     * Sends a Fossabot API request.
     *
     * @param string $url Fossabot API url
     *
     * @throws Throwable
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException
     *
     * @return array{
     *     body: array,
     *     statusCode: int,
     *     headers: array,
     * } Response data
     */
    private function sendRequest(string $url): array
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $url,
        );

        $this->debug("Sending Fossabot API request to [{$request->getUri()}].", compact('request'));

        // Make request to validate
        $response = $this->httpClient->sendRequest($request);
        $body = $this->getResponseBody($response->getBody()->getContents());
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();

        return compact('body', 'statusCode', 'headers');
    }

    /**
     * Get additional message context from a Fossabot request.
     *
     * @param string $customApiToken Fossabot custom API token
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException
     *
     * @return \Brandon14\FossabotCommander\Contracts\Context\FossabotContext Fossabot context
     */
    private function getContext(string $customApiToken): FossabotContextInterface
    {
        $this->info('Sending request to get additional context.');

        try {
            $body = $this->sendContextRequest($customApiToken);

            $this->info('Successfully received context response.');
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception getting context with message [{$exception->getMessage()}].",
                compact('exception'),
            );

            // Allow rate limit exceptions to be rethrown here.
            if ($exception instanceof RateLimitException) {
                $this->debug('Rethrowing ['.RateLimitException::class.'] exception.');

                throw $exception;
            }

            $this->debug('Transforming exception of class ['.get_class($exception).'] to ['.CannotGetContextException::class.'].');

            // Rethrow API exception as a CannotGetContextException.
            if ($exception instanceof FossabotApiException) {
                throw new CannotGetContextException($exception->fossabotCode(), $exception->errorClass(), $exception->errorMessage(), $exception->statusCode(), $exception->body(), $exception);
            }

            // Transform all other exceptions into a CannotGetContextException.
            throw new CannotGetContextException('unknown', 'unknown_error', $exception->getMessage(), 400, $body ?? null, $exception);
        }

        $this->info('Creating context data model from context response.');

        try {
            return FossabotContext::createFromBody($body);
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception creating context data model with message [{$exception->getMessage()}].",
                compact('exception'),
            );

            throw new CannotCreateContextException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Makes and sends a request to get the additional Fossabot context and returns the parsed JSON body as an array.
     *
     * @param string $customApiToken Fossabot custom API token
     *
     * @throws Throwable
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\InvalidTokenException
     *
     * @return array{
     *     channel: array{
     *         id: string,
     *         login: string,
     *         display_name: string,
     *         avatar: string,
     *         slug: string,
     *         broadcaster_type: string,
     *         provider: string,
     *         provider_id: string,
     *         created_at: string,
     *         stream_timestamp: string,
     *         is_live: bool,
     *     },
     *     message: array{
     *         id: string,
     *         content: string,
     *         provider: string,
     *         user: array{
     *             provider_id: string,
     *             login: string,
     *             display_name: string,
     *             roles: array{
     *                 array{
     *                     id: string,
     *                     name: string,
     *                     type: string,
     *                 }
     *             },
     *         },
     *     }|null,
     * } Fossabot context API response
     */
    private function sendContextRequest(string $customApiToken): array
    {
        ['body' => $body, 'statusCode' => $statusCode, 'headers' => $headers] = $this->sendRequest(
            self::FOSSABOT_API_BASE_URL.'/context/'.urlencode($customApiToken)
        );

        if ($statusCode !== 200) {
            $this->info("Received non-200 HTTP response code [{$statusCode}] back from context.");

            throw $this->getExceptionFromBody($body, $statusCode, $headers);
        }

        return $body;
    }

    /**
     * Parses JSON body from Fossabot API.
     *
     * @param string $body JSON body content
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException
     *
     * @return array Parsed body content
     */
    private function getResponseBody(string $body): array
    {
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception decoding JSON response body with message [{$exception->getMessage()}].",
                compact('exception')
            );

            throw new JsonParsingException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Gets the exception context data from a given Fossabot API error response and creates the appropriate exception
     * class.
     *
     * @param array $body       Parsed JSON body
     * @param int   $statusCode HTTP status code
     * @param array $headers    HTTP response headers
     *
     * @returns \Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException Exception
     */
    private function getExceptionFromBody(array $body, int $statusCode, array $headers): FossabotApiException
    {
        // Get exception details from response body.
        $fossabotCode = $body['code'] ?? 'unknown';
        $errorClass = $body['error'] ?? 'Unknown Error';
        $errorMessage = $body['message'] ?? 'An unknown error occurred. Could not get message from Fossabot response.';

        // Return the proper exception for the given status code.
        switch ($statusCode) {
            case 400:
                return new InvalidTokenException(
                    $fossabotCode,
                    $errorClass,
                    $errorMessage,
                    $body,
                );
            case 429:
                return new RateLimitException(
                    $fossabotCode,
                    $errorClass,
                    $errorMessage,
                    (int) $headers['x-ratelimit-total'][0],
                    (int) $headers['x-ratelimit-remaining'][0],
                    (int) $headers['x-ratelimit-reset'][0],
                    $body,
                );
            default:
                return new FossabotApiException(
                    $fossabotCode,
                    $errorClass,
                    $errorMessage,
                    $statusCode,
                    $body,
                );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void // @pest-ignore-type
    {
        // Only log if logging is enabled and we have a logger instance.
        if (! $this->logging || $this->logger === null) {
            return;
        }

        $class = static::class;

        // This should never happen.
        // @codeCoverageIgnoreStart
        try {
            $timestamp = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
        } catch (Throwable $exception) {
            $timestamp = date_create_immutable()->format(DateTimeInterface::ATOM);
        }
        // @codeCoverageIgnoreEnd

        $message = "[{$class}] {$message}";

        $context = array_merge($this->getLoggingContext(), compact('timestamp'), $context);

        $this->logger->log($level, (string) $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getLoggingContext(): array
    {
        return [
            'class' => static::class,
            'http_client' => get_class($this->httpClient),
            'request_factory' => get_class($this->requestFactory),
            'logger' => $this->logger === null ? null : get_class($this->logger),
            'logging' => $this->logging,
            'api_url' => self::FOSSABOT_API_BASE_URL,
        ];
    }
}
