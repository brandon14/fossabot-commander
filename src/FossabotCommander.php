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

namespace Brandon14\FossabotCommander;

use Exception;
use Throwable;

use function compact;
use function get_class;
use function urlencode;

use Psr\Log\LoggerTrait;

use function json_decode;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Brandon14\FossabotCommander\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Exceptions\RateLimitException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotApiException;
use Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException;
use Brandon14\FossabotCommander\Contracts\Exceptions\InvalidTokenException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\FossabotCommanderException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException;
use Brandon14\FossabotCommander\Contracts\Exceptions\CannotValidateRequestException;
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
     * @param \Psr\Http\Client\ClientInterface          $httpClient     PSR HTTP client instance
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory PSR HTTP request factory instance
     * @param \Psr\Log\LoggerInterface|null             $logger         PSR logger instance
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function runCommand(
        FossabotCommand $command,
        string $customApiToken,
        bool $getContext = true
    ): string {
        $context = null;
        $body = null;

        $this->debug('Sending request to validate incoming Fossabot request.');

        // Validate Fossabot request using the $validateUrl.
        $validateRequest = $this->requestFactory->createRequest(
            'GET',
            self::FOSSABOT_API_BASE_URL.'/validate/'.urlencode($customApiToken),
        );

        try {
            // Make request to validate
            $validateResponse = $this->httpClient->sendRequest($validateRequest);
            $body = $this->getResponseBody($validateResponse->getBody()->getContents());
            $statusCode = $validateResponse->getStatusCode();
            $headers = $validateResponse->getHeaders();

            if ($statusCode !== 200) {
                $this->debug("Received non-200 HTTP response code [{$statusCode}] back from validation.");

                throw $this->getExceptionFromBody($body, $statusCode, $headers);
            }

            $this->debug('Validated Fossabot request.');
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception during validation with message [{$exception->getMessage()}].",
                compact('exception'),
            );

            $this->debug('Transforming exception of class ['.get_class($exception).'] to ['.CannotValidateRequestException::class.'].');

            // Allow rate limit exceptions to be rethrown here.
            if ($exception instanceof RateLimitException) {
                throw $exception;
            }

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

        $this->debug('Invoking FossabotCommand.', [
            'command' => get_class($command),
        ]);

        // Invoke command.
        return $command->getResponse($context);
    }

    /**
     * Get additional message context from a Fossabot request.
     *
     * @param string $token Custom API token
     *
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\JsonParsingException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotGetContextException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\FossabotCommanderException
     * @throws \Brandon14\FossabotCommander\Contracts\Exceptions\CannotCreateContextException
     *
     * @return \Brandon14\FossabotCommander\Contracts\Context\FossabotContext Fossabot context
     */
    private function getContext(string $token): FossabotContextInterface
    {
        $this->debug('Attempting to get context.');

        $contextRequest = $this->requestFactory->createRequest(
            'GET',
            self::FOSSABOT_API_BASE_URL.'/context/'.urlencode($token),
        );

        try {
            $contextResponse = $this->httpClient->sendRequest($contextRequest);
            $body = $this->getResponseBody($contextResponse->getBody()->getContents());
            $statusCode = $contextResponse->getStatusCode();
            $headers = $contextResponse->getHeaders();

            if ($statusCode !== 200) {
                throw $this->getExceptionFromBody($body, $statusCode, $headers);
            }

            $this->debug('Successfully received context response.');
        } catch (Throwable $exception) {
            $this->error(
                "Caught exception getting context with message [{$exception->getMessage()}].",
                compact('exception'),
            );

            // Allow rate limit exceptions to be rethrown here.
            if ($exception instanceof RateLimitException) {
                throw $exception;
            }

            // Rethrow API exception as a CannotGetContextException.
            if ($exception instanceof FossabotApiException) {
                throw new CannotGetContextException($exception->fossabotCode(), $exception->errorClass(), $exception->errorMessage(), $exception->statusCode(), $exception->body(), $exception);
            }

            // Rethrow any generic FossabotCommandException.
            if ($exception instanceof FossabotCommanderException) {
                throw $exception;
            }

            // Transform all other exceptions into a FossabotCommanderException.
            throw new FossabotCommanderException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->debug('Creating context data model from context response.');

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
     * @throws Exception
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
        if ($this->logger === null) {
            return;
        }

        $this->logger->log($level, (string) $message, $context);
    }
}
