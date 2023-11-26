<!-- markdownlint-disable MD033 -->
<p align="center">
  <a href="https://packagist.org/packages/brandon14/fossabot-commander" target="_blank"><img alt="Packagist PHP Version" src="https://img.shields.io/packagist/dependency-v/brandon14/fossabot-commander/php?style=for-the-badge&cacheSeconds=3600"></a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/fossabot-commander/actions/workflows/run-tests.yml" target="_blank"><img alt="GitHub Workflow Status (with event)" src="https://img.shields.io/github/actions/workflow/status/brandon14/fossabot-commander/run-tests.yml?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://codeclimate.com/github/brandon14/fossabot-commander/maintainability" target="_blank"><img alt="Code Climate maintainability" src="https://img.shields.io/codeclimate/maintainability-percentage/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://codecov.io/gh/brandon14/fossabot-commander" target="_blank"><img alt="Codecov" src="https://img.shields.io/codecov/c/github/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/blob/main/LICENSE" target="_blank"><img alt="GitHub" src="https://img.shields.io/github/license/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/fossabot-commander/issues" target="_blank"><img alt="GitHub issues" src="https://img.shields.io/github/issues/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/issues?q=is%3Aissue+is%3Aclosed" target="_blank"><img alt="GitHub closed issues" src="https://img.shields.io/github/issues-closed/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/pulls" target="_blank"><img alt="GitHub pull requests" src="https://img.shields.io/github/issues-pr/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/pulls?q=is%3Apr+is%3Aclosed" target="_blank"><img alt="GitHub closed pull requests" src="https://img.shields.io/github/issues-pr-closed/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/fossabot-commander/releases" target="_blank"><img alt="GitHub release (with filter)" src="https://img.shields.io/github/v/release/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/commits/main" target="_blank"><img alt="GitHub commit activity (branch)" src="https://img.shields.io/github/commit-activity/m/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/fossabot-commander/commits/main" target="_blank"><img alt="GitHub last commit (by committer)" src="https://img.shields.io/github/last-commit/brandon14/fossabot-commander?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<!-- markdownlint-enable MD033 -->

# brandon14/fossabot-commander

## Source code for [brandon14/fossabot-commander](https://github.com/brandon14/fossabot-commander)

## Table of Contents

1. [Requirements](https://github.com/brandon14/fossabot-commander#requirements)
2. [Purpose](https://github.com/brandon14/fossabot-commander#purpose)
3. [Installation](https://github.com/brandon14/fossabot-commander#installation)
4. [Usage](https://github.com/brandon14/fossabot-commander#usage)
5. [Standards](https://github.com/brandon14/fossabot-commander#standards)
6. [Coverage](https://github.com/brandon14/fossabot-commander#coverage)
7. [Documentation](https://github.com/brandon14/fossabot-commander#documentation)
8. [Contributing](https://github.com/brandon14/fossabot-commander#contributing)
9. [Versioning](https://github.com/brandon14/fossabot-commander#versioning)
10. [Security Vulnerabilities](https://github.com/brandon14/fossabot-commander#security-vulnerabilities)

## Requirements

| Dependency             | Version        |
|------------------------|----------------|
| php                    | ^7.4 \|\| ^8.0 |
| ext-json               | *              |
| psr/http-factory       | ^1.0           |
| psr/http-client        | ^1.0           |
| psr/log                | ^1.0           |
| symfony/polyfill-php80 | ^1.0           |
| symfony/polyfill-php81 | ^1.0           |

## Purpose

I built this library to aid in responding to [Fossabot's](https://docs.fossabot.com/variables/customapi)
`customapi` requests when using PHP. If you are running a webserver and want to send Fossabot `customapi`
requests to that server, this package allows you to easily write commands and run them to return the text
that would display in the chat message. The reason the commands return strings is because Fossabot
Fossabot discards any status codes and other HTTP response content, and only uses the raw response body
which is a string. This string can be JSON, text, etc.

The normal usage for Fossabot's `customapi` might be something like:

Set command `!foo` to `$(customapi https://foo.bar/foo)`.

When someone types `!foo` in your chat, 
Fossabot will make a request to `https://foo.bar/foo` and whatever that URl returns will be used as the
chat message. With this package, you can easily create commands, and invoke them via the
`FossabotCommander::runCommand()` method, and use these utilties in you web framework of choice.

This library validates the Fossabot request using the [request validation](https://docs.fossabot.com/variables/customapi/#validating-requests)
endpoint so you can be sure that the request came from Fossabot. You can also optionally (on by default)
choose to get additional context about the request as outlined [here](https://docs.fossabot.com/variables/customapi/#validating-requests)
to provide more rich integrations with Fossabot. The `FossabotContext` data will be passed into the
command's `getResponse` method.

## Installation

```bash
composer require brandon14/fossabot-commander
```

## Usage

You will first need to get the custom API token from the request header. It will be in the 
`x-fossabot-customapitoken` header.

For a simple command (using Laravel as an example web framework):

```php
// FooCommand.php
<?php

declare(strict_types=1);

namespace App\Fossabot\Commands;

use Brandon14\FossabotCommander\FossabotCommand;
use Brandon14\FossabotCommander\Contracts\Context\FossabotContext;

class FooCommand extends FossabotCommand
{
    /**
     * {@inheritDoc}
     */
    public function getResponse(?FossabotContext $context = null) : string
    {
        return 'Hello chat!';
    }
}

// In some Laravel Controller
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Fossabot\Commands\FooCommand;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Brandon14\FossabotCommander\Contracts\FossabotCommander;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    private FossabotCommander $commander;

    public function __construct(FossabotCommander $commander)
    {
        $this->commander = $commander;
    }
    
    public function fooCommand(Request $request): string
    {
        // Get Fossabot API token.
        $apiToken = $request->header('x-fossabot-customapitoken');

        // Invoke command.
        return $this->commander->runCommand(new FooCommand(), $apiToken);
    }
}
```

The `FossabotCommander` class requires a PSR compliant `ClientInterface` and a PSR compliant
`RequestFactoryInterface`. These can be provided by libraries like `guzzlehttp/guzzle` or other PSR
compliant libraries. In the above example with Laravel we are assuming that the Laravel container
has the `FossabotCommander` instance bound to the container.

For more complicated commands, the sky is the limit. Depending on how you want to build and instantiate
your `FossabotCommand` instances, you can use the `FossabotContext` data to provide rich integration
for your Fossabot chatbot!

### Usage with Laravel:

If you are planning on using `fossabot-commander` in a Laravel project, check out
the Laravel package [fossabot-commander-laravel](https://github.com/brandon14/fossabot-commander-laravel)
that I made for easy integration within the Laravel ecosystem.

## Standards

We strive to meet the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style for PHP projects, and enforce our
coding standard via the [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) linting tool. Our ruleset can be
found in the `.php-cs-fixer.dist.php` file.

## Coverage

The latest code coverage information can be found via [Codecov](https://codecov.io/gh/brandon14/fossabot-commander). We
strive to maintain 100% coverage across the entire library, so if you are
[contributing](https://github.com/brandon14/fossabot-commander#contributing), please make sure to include tests for new
code added.

## Documentation

Documentation to this project can be found [here](https://brandon14.github.io/fossabot-commander/).

## Contributing

Got something you want to add? Found a bug or otherwise bad code? Feel free to submit pull
requests to add in new features, fix bugs, or clean things up. Just be sure to follow the
[Code of Conduct](https://github.com/brandon14/fossabot-commander/blob/master/.github/CODE_OF_CONDUCT.md)
and [Contributing Guide](https://github.com/brandon14/fossabot-commander/blob/master/.github/CONTRIBUTING.md),
and we encourage creating clean and well described pull requests if possible.

If you notice an issues with the library or want to suggest new features, feel free to create issues appropriately using
the [issue tracker](https://github.com/brandon14/fossabot-commander/issues).

In order to run the tests, it is recommended that you sign up for a Cloudinary account (it's a free service), and use that
account to run the full integration tests. In order to do that, you will need to copy `.env.example` to `.env` and fill
in the variables using the details in your account. The integration tests will use random prefixed directories and clean
everything up before and after the tests.

## Versioning

`brandon14/fossabot-commander` uses [semantic versioning](https://semver.org/) that looks like `MAJOR.MINOR.PATCH`.

Major version changes will include backwards-incompatible changes and may require refactoring of projects using it.
Minor version changes will include backwards-compatible new features and changes and will not break existing usages.
Patch version changes will include backwards-compatible bug and security fixes, and should be updated as soon as
possible.

## Security Vulnerabilities

If you discover a vulnerability within this package, please email Brandon Clothier via
[brandon14125@gmail.com](mailto:brandon14125@gmail.com). All security vulnerabilities will be promptly
addressed.

This code is released under the MIT license.

Copyright &copy; 2023 Brandon Clothier

[![X (formerly Twitter) URL](https://img.shields.io/twitter/url?url=https%3A%2F%2Fgithub.com%2Fbrandon14%2Ffossabot-commander&style=for-the-badge&logo=twitter&label=TWEET%20%40inhal3exh4le&cacheSeconds=3600)](https://twitter.com/intent/tweet?url=https%3A%2F%2Fgithub.com%2Fbrandon14%2Ffossabot-commander&text=Hey%20@inhal3exh4le%20this%20fossabot-commander%20package%20is%20pretty%20cool%21)

[![X (formerly Twitter) Follow](https://img.shields.io/twitter/follow/inhal3exh4le?style=for-the-badge&logo=twitter&cacheSeconds=3600)](https://twitter.com/intent/follow?screen_name=inhal3exh4le)
