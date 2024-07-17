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

/**
 * Simple script to add project license markdown into the generated packages licenses markdown file.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
echo "Getting project license file...\n";

$license = file_get_contents(__DIR__.'/../LICENSE');

if (! $license) {
    echo "Could not find project license file. Please make sure there is a `LICENSE` text file in the root of the project.\n";

    exit(1);
}

try {
    $composer = json_decode(
        file_get_contents(__DIR__.'/../composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
} catch (Throwable $exception) {
    echo "Unable to parse composer.json file with JSON error [{$exception->getFile()}].\n";

    exit(1);
}

$name = $composer['name'] ?? 'Not configured.';
$description = $composer['description'] ?? 'Not configured.';
$homePage = isset($composer['homepage']) ? "[{$name}]({$composer['homepage']})" : 'Not configured.';
$sourceLicense = 'Not configured.';

if (isset($composer['license'])) {
    $sourceLicense = is_array($composer['license'])
        ? implode(', ', $composer['license'])
        : $composer['license'];
}

$license = "### {$name}\n{$description}\nHomepage: {$homePage}\nLicenses Used: {$sourceLicense}\n\n".rtrim($license);

$licenses = file_get_contents(__DIR__.'/../licenses.md');

if ($licenses) {
    $licenses = trim(
        preg_replace('/#\sProject\sLicenses.*##\sDependencies/sm', '', $licenses)
    );
}

// Wrap all URLs detected with markdown URLs.
$licenses = preg_replace_callback(
    '/\b(?:https?:\/\/)(?:www\d?\.)?[-\w\/?\.=&\+]+\b/',
    static function (array $matches) {
        return "[{$matches[0]}]($matches[0])";
    },
    $licenses
);

$licenses = '# Project Licenses

This file was custom generated using the [PHP Legal Licenses](https://github.com/Comcast/php-legal-licenses) utility and
some custom logic to add in the source license as well. It contains the name, version and commit sha, description,
homepage, and license information for every dependency in this project.

## Source

'.$license.(mb_strlen($licenses) > 0 ? "\n\n## Dependencies\n\n{$licenses}" : '');

echo "Writing new `licenses.md` file...\n";

file_put_contents(__DIR__.'/../licenses.md', $licenses);

exit(0);
