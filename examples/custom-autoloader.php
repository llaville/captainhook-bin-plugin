<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Condition;
use SebastianFeldmann\Git\Repository;

class MyDependencyManager implements Condition
{
    public function isTrue(IO $io, Repository $repository): bool
    {
        // TODO: Implement isTrue() method.
        return true;
    }
}
