<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

foreach (glob(dirname(__DIR__) . '/vendor-bin/*/vendor/autoload.php') as $autoloadFile) {
    require $autoloadFile;
}
