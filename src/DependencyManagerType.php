<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bartlett\CaptainHookBinPlugin;

use Bartlett\CaptainHookBinPlugin\Condition\PackageInstalled;
use Bartlett\CaptainHookBinPlugin\Condition\PharInstalled;
use CaptainHook\App\Config\Condition;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
enum DependencyManagerType: string
{
    case Composer = 'composer';
    case Phive = 'phive';

    public static function getCondition(string $dependencyManager, array $packageRequirement): Condition
    {
        return match (DependencyManagerType::tryFrom($dependencyManager)) {
            // Dependency Managers Composer and Phive are supported by default
            DependencyManagerType::Composer => new Condition('\\' . PackageInstalled::class, $packageRequirement),
            DependencyManagerType::Phive => new Condition('\\' . PharInstalled::class, $packageRequirement),
            // Any valid CaptainHook's User Condition classes are also supported
            default => new Condition('\\' . \ltrim($dependencyManager, '\\'), $packageRequirement),
        };
    }
}
