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
use Composer\InstalledVersions;
use Composer\Semver\Semver;
use PharIo\Phive\Cli\Request;
use PharIo\Phive\ComposerAlias;
use PharIo\Phive\EnvironmentLocator;
use PharIo\Phive\Factory;
use PharIo\Phive\LocalPhiveXmlConfig;
use PharIo\Phive\PhiveXmlConfigFileLocator;
use PharIo\Phive\StatusCommandConfig;
use PharIo\Phive\XmlFile;
use PharIo\Version\VersionConstraintParser;
use SebastianFeldmann\Git\Repository;
use Throwable;

use function in_array;
use const PHP_OS;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
class PharInstalled implements Condition
{
    public function __construct(private readonly string $packageName, private readonly string $constraint = '*')
    {
    }

    public function isTrue(IO $io, Repository $repository): bool
    {
        $isPhiveAvailable = InstalledVersions::isInstalled('phar-io/phive');

        if (!$isPhiveAvailable) {
            $io->write(
                '  <fg=cyan>Applied: PHIVE is not installed</>',
                true,
                IO::VERBOSE,
            );
            return false;
        }

        $request = new Request([]);

        $factory = new Factory($request);

        // \PharIo\Phive\Factory::getEnvironment
        $environment = (new EnvironmentLocator())->getEnvironment(PHP_OS);

        // \PharIo\Phive\Factory::getConfig
        $config = $factory->getConfig();

        // \PharIo\Phive\Factory::getOutput
        $output = $factory->getOutput();

        // \PharIo\Phive\Factory::getPhiveXmlConfigFileLocator
        $xmlConfigFileLocator = new PhiveXmlConfigFileLocator(
            $environment,
            $config,
            $output
        );

        $xmlConfig = new LocalPhiveXmlConfig(
            new XmlFile(
                $xmlConfigFileLocator->getFile(),
                'https://phar.io/phive',
                'phive'
            ),
            new VersionConstraintParser(),
            $environment
        );

        $pharRegistry = $factory->getPharRegistry();

        $statusCommandConfig = new StatusCommandConfig(
            $request->getOptions(),
            $xmlConfig,
            $pharRegistry
        );

        // \PharIo\Phive\Factory::getSourcesList
        $sourcesList = $factory->getRemoteSourcesListFileLoader()->load();

        $isApplied = false;
        $aliases = [];

        try {
            $aliases[] = $sourcesList->getAliasForComposerAlias(new ComposerAlias($this->packageName));

            if (in_array('phpcbf', $aliases, true)) {
                // "squizlabs/php_codesniffer" is the only Composer package that provide two aliases
                // (for current Phive v0.16)
                // but only the first one is retrieved by \PharIo\Phive\SourcesList::getAliasForComposerAlias
                // @see https://github.com/phar-io/phive/blob/0.16.0/src/shared/sources/SourcesList.php#L66
                // @link FYI: this problem was referenced by https://github.com/phar-io/phive/issues/458
                $aliases[] = 'phpcs';
            }

            foreach ($statusCommandConfig->getPhars() as $phar) {
                if (!in_array($phar->getName(), $aliases, true)) {
                    continue;
                }
                if (!$phar->isInstalled()) {
                    continue;
                }
                $versionInstalled = $phar->getInstalledVersion();

                $isApplied = Semver::satisfies(
                    $versionInstalled->getOriginalString(),
                    $phar->getVersionConstraint()->asString()
                );
                if ($isApplied) {
                    break;
                }
            }
        } catch (Throwable) {
            // @mago-expect lint:no-empty-catch-clause
            // catch to avoid CaptainHook to prints failed action rather than skipped
        }

        $io->write(
            sprintf(
                '  <fg=cyan>Applied: PHAR tool %s</> %s (with constraint "%s")',
                $isApplied ? 'installed' : 'not installed',
                $this->packageName,
                $this->constraint
            ),
            true,
            IO::VERBOSE,
        );

        return $isApplied;
    }
}
