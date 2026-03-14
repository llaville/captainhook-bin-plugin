<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bartlett\CaptainHookBinPlugin\Condition;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Condition;
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use SebastianFeldmann\Git\Repository;
use OutOfBoundsException;

use function file_exists;
use function sprintf;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
class PackageInstalled implements Condition
{
    public function __construct(private readonly string $packageName, private readonly string $constraint = '*')
    {
    }

    public function isTrue(IO $io, Repository $repository): bool
    {
        $isApplied = false;

        foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
            $installedFile = $vendorDir . '/composer/installed.php';

            if (file_exists($installedFile)) {
                $installed = require $installedFile;
                InstalledVersions::reload($installed);  // @phpstan-ignore argument.type

                try {
                    if (InstalledVersions::satisfies(new VersionParser(), $this->packageName, $this->constraint)) {
                        $isApplied = true;
                        break;
                    }
                } catch (OutOfBoundsException) {
                    // @mago-expect lint:no-empty-catch-clause
                    // catch from InstalledVersions to avoid CaptainHook to prints failed action rather than skipped
                }
            }
        }

        $io->write(
            sprintf(
                '  <fg=cyan>Applied: Composer package %s</> %s (with constraint "%s")',
                $isApplied ? 'installed' : 'not installed',
                $this->packageName,
                $this->constraint,
            ),
            true,
            IO::VERBOSE,
        );

        return $isApplied;
    }
}
