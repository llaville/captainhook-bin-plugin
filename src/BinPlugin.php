<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bartlett\CaptainHookBinPlugin;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionNotApplicable;
use CaptainHook\App\Plugin;
use CaptainHook\App\Runner\Action\Cli\Command\Formatter;
use CaptainHook\App\Runner\Condition;
use CaptainHook\App\Runner\Hook as RunnerHook;
use CaptainHook\App\Runner\Hook\Printer;
use SebastianFeldmann\Git\Repository;
use Symfony\Component\Console\Terminal;

use function count;
use function file_exists;
use function function_exists;
use function getcwd;
use function is_array;
use function mb_strwidth;
use function microtime;
use function putenv;
use function rtrim;
use function sprintf;
use function strlen;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
class BinPlugin extends Plugin\Hook\Base implements Plugin\Hook
{
    private float $startTime;
    private float $previousTime;
    private string $configDirectory;
    private string $dependencyManager;

    public function configure(Config $config, IO $io, Repository $repository, Config\Plugin $plugin): void
    {
        parent::configure($config, $io, $repository, $plugin);

        $formatter = new Formatter($io, $config, $repository);

        $this->dependencyManager = $plugin->getOptions()->get(
            'dependency-manager',
            DependencyManagerType::Composer->value,
        );

        /** @var string $configDirectory */
        $configDirectory = $plugin->getOptions()->get('config-directory', getcwd());
        $this->configDirectory = $formatter->format($configDirectory);

        /** @var string $binaryDirectory */
        $binaryDirectory = $plugin->getOptions()->get('binary-directory', getcwd());
        $binaryDirectory = $formatter->format($binaryDirectory);

        putenv('XDG_BIN_HOME=' . rtrim($binaryDirectory, '/\\') . DIRECTORY_SEPARATOR);

        // Due to potential issue
        // (@see https://github.com/captainhook-git/captainhook/issues/293#issuecomment-3949257149)
        // that raise a "Typed property Bartlett\CaptainHookBinPlugin\BinPlugin::$previousTime must not be accessed
        // before initialization"
        // Be sure to initialize property first
        $this->previousTime = 0.0;

        $this->startTime = microtime(true);
    }

    public function beforeHook(RunnerHook $hook): void
    {
        $this->justify(
            "Before hook " . "<comment>{$hook->getName()}</comment> runs",
            "<fg=blue>[0.00s]</>",
        );
    }

    public function beforeAction(RunnerHook $hook, Config\Action $action): void
    {
        $this->previousTime = microtime(true);
        $this->justify(
            "Before action " . "<comment>{$action->getLabel()}</comment> runs",
            "<fg=blue>[0.00s]</>",
        );

        $packageRequirement = $action->getOptions()->get('package-require', []);
        if (!is_array($packageRequirement)) {
            $packageRequirement = [$packageRequirement];
        }
        if (count($packageRequirement) > 0) {
            if (count($action->getConditions()) === 0) {
                $condition = DependencyManagerType::getCondition($this->dependencyManager, $packageRequirement);

                // Due to @link https://github.com/captainhook-git/captainhook/issues/309,
                // following syntax won't work as expected
                // $collectorIO = new IO\CollectorIO($this->io);
                $collectorIO = $this->io;
                $conditionRunner = new Condition($collectorIO, $this->repository, $this->config, $hook->getName());

                $collectorIO->write('  <fg=cyan>Condition: ' . $condition->getExec() . '</>', true, IO::VERBOSE);
                if (!$conditionRunner->doesConditionApply($condition)) {
                    // Do not use Exception syntax, for reason given at
                    // @link https://github.com/captainhook-git/captainhook/issues/309#issuecomment-4052951959
                    // CAUTION:
                    // preferred solution to solve contextual issue
                    // @see https://github.com/captainhook-git/captainhook/discussions/310
                    throw new ActionNotApplicable();

                    // For following reason
                    // @link https://github.com/captainhook-git/captainhook/issues/309#issuecomment-4072610082
                    // we cannot use this alternative that break Plugin Behavior/Goals
                    /** Alternative to Exception is to handle skipped action and print message yourself */
                    //$hook->shouldSkipActions(true);
                    //(new Printer($this->io))->actionSkipped($action);
                    //return;
                }
            }
        }

        $configFile = $action->getOptions()->get('config-file');

        if (null !== $configFile) {
            $configPath = rtrim($this->configDirectory, '/\\') . DIRECTORY_SEPARATOR . $configFile;
            if (file_exists($configPath)) {
                putenv('XDG_CONFIG_HOME=' . $configPath);
            }
        }

        $colors = $action->getOptions()->get('colors', 'auto');
        if ('force' === $colors) {
            putenv('FORCE_COLOR=1');
        } else {
            $colorized = match ($colors) {
                'never' => false,
                default => true, // always or auto values are acceptable
            };
            putenv('NO_COLOR=' . !$colorized);
        }
    }

    public function afterAction(RunnerHook $hook, Config\Action $action): void
    {
        $this->justify(
            "After action " . "<comment>{$action->getLabel()}</comment> runs",
            sprintf("<fg=blue>[%01.2fs]</>", $this->previousTime ? microtime(true) - $this->previousTime : 0.0),
        );
        putenv('XDG_CONFIG_HOME');
        putenv('NO_COLOR');
    }

    public function afterHook(RunnerHook $hook): void
    {
        $this->justify(
            "After hook " . "<comment>{$hook->getName()}</comment> runs",
            sprintf("<fg=blue>[%01.2fs]</>", microtime(true) - $this->startTime),
        );
    }

    private function justify(string $first, ?string $second = null, array $options = []): void
    {
        $options = [
            'first'  => ($options['first'] ?? []) + ['bg' => null, 'fg' => 37, 'bold' => 0],
            'second' => ($options['second'] ?? []) + ['bg' => null, 'fg' => 37, 'bold' => 1],
            'sep'    => $options['sep'] ?? '.',
        ];

        $second        = (string) $second;
        $dashWidth     = (new Terminal())->getWidth() - ($this->strwidth($first) + $this->strwidth($second));
        // remove left and right margins because we're going to add 1 space on each side (after/before the text).
        // if we don't have a second element, we just remove the left margin
        $dashWidth -= $second === '' ? 1 : 2;
        $sep = $dashWidth >= 0 ? str_repeat((string) $options['sep'], $dashWidth) : '';

        $this->io->write([$first, $sep, $second], false, IO::VERY_VERBOSE);
        $this->io->write('', true, IO::VERY_VERBOSE);
    }

    private function strwidth(string $string): int
    {
        if (function_exists('mb_strwidth')) {
            return mb_strwidth($string);
        }
        return strlen($string);
    }
}
