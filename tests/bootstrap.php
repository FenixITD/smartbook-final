<?php

declare(strict_types=1);

use DG\BypassFinals;

require __DIR__ . '/../vendor/autoload.php';

BypassFinals::denyPaths([
    '*/vendor/phpunit/*',
    '*/vendor/sebastian/*',
    '*/vendor/phar-io/*',
    '*/vendor/theseer/*',
    '*/vendor/myclabs/*',
]);

BypassFinals::enable();
