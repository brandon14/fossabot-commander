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

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Brandon14\FossabotCommander\Context\FossabotContext;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext as FossabotContextInterface;

// Custom functions for mocking data.
/**
 * Get mock custom token.
 *
 * @return string Token
 */
function customToken(): string
{
    return '2c14e0e0-b827-458a-b31e-573105153a85';
}

/**
 * Get mocked rate limit total header value.
 *
 * @return int Rate limit total
 */
function rateLimitTotal(): int
{
    return 30;
}

/**
 * Get mocked rate limit remaining header value.
 *
 * @return int Rate limit remaining
 */
function rateLimitRemaining(): int
{
    return 29;
}

/**
 * Get mocked rate limit resets at timestamp header value.
 *
 * @return int Rate limit resets at timestamp
 */
function rateLimitResetsAt(): int
{
    return 1698137068;
}

/**
 * Get mocked full context body response.
 *
 * @return string JSON body
 */
function contextBody(): string
{
    return '{
        "channel": {
            "id": "1",
            "login": "aiden",
            "display_name": "Aiden",
            "avatar": "https://static-cdn.jtvnw.net/jtv_user_pictures/aiden-profile_image-6d03ccc5d668cc80-300x300.jpeg",
            "slug": "aiden",
            "broadcaster_type": "affiliate",
            "provider": "twitch",
            "provider_id": "87763385",
            "created_at": "2021-07-10T04:20:05.599789Z",
            "stream_timestamp": "2022-09-17T19:17:27Z",
            "is_live": true
        },
        "message": {
            "id": "ae9a4e3e-d495-4d75-aec6-8965e7c4ccd0",
            "content": "!testcommand",
            "provider": "twitch",
            "user": {
                "provider_id": "87763385",
                "login": "aiden",
                "display_name": "Aiden",
                "roles": [
                    {
                        "id": "1",
                        "name": "Broadcaster",
                        "type": "broadcaster"
                    },
                    {
                        "id": "3",
                        "name": "Moderator",
                        "type": "moderator"
                    },
                    {
                        "id": "5",
                        "name": "Subscriber",
                        "type": "subscriber"
                    },
                    {
                        "id": "269",
                        "name": "test",
                        "type": "custom"
                    },
                    {
                        "id": "14",
                        "name": "Admin",
                        "type": "custom"
                    }
                ]
            }
        }
    }';
}

/**
 * Get mocked valid context with no message portion present.
 *
 * @return string JSON body
 */
function contextNoMessageBody(): string
{
    return '{
        "channel": {
            "id": "1",
            "login": "aiden",
            "display_name": "Aiden",
            "avatar": "https://static-cdn.jtvnw.net/jtv_user_pictures/aiden-profile_image-6d03ccc5d668cc80-300x300.jpeg",
            "slug": "aiden",
            "broadcaster_type": "affiliate",
            "provider": "twitch",
            "provider_id": "87763385",
            "created_at": "2021-07-10T04:20:05.599789Z",
            "stream_timestamp": "2022-09-17T19:17:27Z",
            "is_live": true
        }
    }';
}

/**
 * Get mocked invalid token JSOn body.
 *
 * @return string JSON body
 */
function invalidTokenBody(): string
{
    return '{
        "code": "token_invalid",
        "error": "Bad Request",
        "message": "Invalid token",
        "status": 400
    }';
}

/**
 * Get mocked valid token validate response body.
 *
 * @return string JSON body
 */
function validTokenBody(): string
{
    return '{
        "context_url": "https://api.fossabot.com/v2/customapi/context/'.urlencode(customToken()).'"
    }';
}

/**
 * Get mocked rate limited response body.
 *
 * @return string JSON body
 */
function rateLimitedBody(): string
{
    return '{
        "code": "ratelimit",
        "error": "Too Many Requests",
        "message": "You are being rate limited.",
        "status": 429
    }';
}

/**
 * Get a mocked invalid context body. This shouldn't ever come back from their API, but is included to ensure we can
 * either make the context objects, or throw an exception.
 *
 * @return string JSON body
 */
function invalidContextBody(): string
{
    return '{
        "channel": {
            "display_name": "Aiden",
            "avatar": "https://static-cdn.jtvnw.net/jtv_user_pictures/aiden-profile_image-6d03ccc5d668cc80-300x300.jpeg",
            "slug": "aiden",
            "broadcaster_type": "affiliate",
            "provider": "twitch",
            "provider_id": "87763385",
            "created_at": "2021-07-10T04:20:05.599789Z",
            "stream_timestamp": "2022-09-17T19:17:27Z",
            "is_live": true
        }
    }';
}

/**
 * Mocked common Fossabot API headers.
 *
 * @return array|string[][] Array of headers
 */
function standardHeaders(): array
{
    return [
        'x-fossabot-channelid' => [
            '1',
        ],
        'x-fossabot-channeldisplayname' => [
            'Aiden',
        ],
        'x-fossabot-channellogin' => [
            'aiden',
        ],
        'x-fossabot-channelslug' => [
            'aiden',
        ],
        'x-fossabot-channelprovider' => [
            'twitch',
        ],
        'x-fossabot-channelproviderid' => [
            '87763385',
        ],
        'x-fossabot-customapitoken' => [
            customToken(),
        ],
        'x-fossabot-hasmessage' => [
            true,
        ],
        'x-fossabot-validateurl' => [
            'https://api.fossabot.com/v2/customapi/validate/'.urlencode(customToken()),
        ],
        'user-agent' => [
            'Fossabot Web Proxy',
        ],
    ];
}

/**
 * Mocked rate limiting Fossabot API headers.
 *
 * @return array|string[][] Array of headers
 */
function rateLimitingHeaders(): array
{
    return [
        'x-ratelimit-total' => [
            rateLimitTotal(),
        ],
        'x-ratelimit-remaining' => [
            rateLimitRemaining(),
        ],
        'x-ratelimit-reset' => [
            (string) rateLimitResetsAt(),
        ],
    ];
}

/**
 * Mocked rate message Fossabot API headers.
 *
 * @return array|string[][] Array of headers
 */
function messageHeaders(): array
{
    return [
        'x-fossabot-message-id' => [
            'ae9a4e3e-d495-4d75-aec6-8965e7c4ccd0',
        ],
        'x-fossabot-message-userlogin' => [
            'aiden',
        ],
        'x-fossabot-message-userdisplayname' => [
            'Aiden',
        ],
        'x-fossabot-message-userprovider' => [
            'twitch',
        ],
        'x-fossabot-message-userproviderid' => [
            '87763385',
        ],
    ];
}

/**
 * Get a Fossabot custom API URL.
 *
 * @param string $additional Additional URL
 *
 * @return string URL
 */
function getFossabotUrl(string $additional = ''): string
{
    $baseUrl = 'https://api.fossabot.com/v2/customapi';

    if (empty($additional)) {
        return $baseUrl;
    }

    return $baseUrl.'/'.urlencode($additional);
}

/**
 * Make a PSR compliant request object.
 *
 * @param string $url    Request URL
 * @param string $method HTTP method
 *
 * @return \Psr\Http\Message\RequestInterface Request instance
 */
function makeRequest(string $url, string $method = 'GET'): RequestInterface
{
    return new Request($method, $url);
}

/**
 * Make a PSR compliant response object.
 *
 * @param int         $status  Status code
 * @param array       $headers HTTP headers
 * @param string|null $body    Response body
 * @param string      $version HTTP version
 * @param string|null $reason  HTTP reason
 */
function makeResponse(
    int $status,
    array $headers,
    ?string $body = null,
    string $version = '1.1',
    ?string $reason = null
): ResponseInterface {
    return new Response($status, $headers, $body, $version, $reason);
}

/**
 * Make a mock {@link \Brandon14\FossabotCommander\Contracts\Context\FossabotContext} instance for testing.
 *
 * @throws \JsonException
 *
 * @return \Brandon14\FossabotCommander\Contracts\Context\FossabotContext Fossabot context data
 */
function contextDataModel(): FossabotContextInterface
{
    $body = contextBody();

    $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

    return FossabotContext::createFromBody($body);
}

/**
 * Make a mock {@link \Brandon14\FossabotCommander\Contracts\Context\FossabotContext} instance for testing with no
 * message context included.
 *
 * @throws \JsonException
 *
 * @return \Brandon14\FossabotCommander\Contracts\Context\FossabotContext Fossabot context data
 */
function contextNoMessageDataModel(): FossabotContextInterface
{
    $body = contextNoMessageBody();

    $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

    return FossabotContext::createFromBody($body);
}

/**
 * Make a mock {@link \Brandon14\FossabotCommander\Contracts\Context\FossabotContext} instance for testing with no roles
 * on user context data.
 *
 * @throws \JsonException
 *
 * @return \Brandon14\FossabotCommander\Contracts\Context\FossabotContext Fossabot context data
 */
function contextNoRolesDataModel(): FossabotContextInterface
{
    $body = contextBody();

    $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

    $body['message']['user']['roles'] = [];

    return FossabotContext::createFromBody($body);
}
