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
use CaptainHook\App\Runner\Util;
use SebastianFeldmann\Git\Repository;
use Symfony\Component\Console\Terminal;

use function boolval;
use function count;
use function file_exists;
use function function_exists;
use function getcwd;
use function is_array;
use function mb_strwidth;
use function microtime;
use function realpath;
use function rtrim;
use function sprintf;
use function strlen;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
class BinPlugin extends Plugin\Hook\Base implements Plugin\Hook
{
    private const DEFAULT_VERBOSITY_MESSAGE = IO::VERY_VERBOSE;
    private float $startTime;
    private float $previousTime;
    private string $dependencyManager;
    private Formatter $formatter;

    public function configure(Config $config, IO $io, Repository $repository, Config\Plugin $plugin): void
    {
        parent::configure($config, $io, $repository, $plugin);

        $this->formatter = new Formatter($io, $config, $repository);

        $this->dependencyManager = $plugin->getOptions()->get(
            'dependency-manager',
            DependencyManagerType::Composer->value,
        );

        $configDirectory = Util::getEnv(
            'XDG_CONFIG_HOME',
            $plugin->getOptions()->get('config-directory', getcwd()),
        );
        $_ENV['XDG_CONFIG_HOME'] = realpath($this->formatter->format($configDirectory));

        /** @var string $binaryDirectory */
        $binaryDirectory = $plugin->getOptions()->get('binary-directory', getcwd());
        $binaryDirectory = $this->formatter->format($binaryDirectory);

        $_ENV['XDG_BIN_HOME'] = rtrim($binaryDirectory, '/\\') . DIRECTORY_SEPARATOR;

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
        $this->settings();
    }

    public function beforeAction(RunnerHook $hook, Config\Action $action): void
    {
        $this->previousTime = microtime(true);
        $this->justify(
            "Before action " . "<comment>{$action->getLabel()}</comment> runs",
            "<fg=blue>[0.00s]</>",
        );
        $this->settings($action);

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
    }

    public function afterAction(RunnerHook $hook, Config\Action $action): void
    {
        $this->justify(
            "After action " . "<comment>{$action->getLabel()}</comment> runs",
            sprintf("<fg=blue>[%01.2fs]</>", $this->previousTime ? microtime(true) - $this->previousTime : 0.0),
        );
        unset($_ENV['NO_COLOR'], $_ENV['FORCE_COLOR']);
    }

    public function afterHook(RunnerHook $hook): void
    {
        $this->justify(
            "After hook " . "<comment>{$hook->getName()}</comment> runs",
            sprintf("<fg=blue>[%01.2fs]</>", microtime(true) - $this->startTime),
        );

        unset($_ENV['XDG_BIN_HOME'], $_ENV['XDG_CONFIG_HOME']);
    }

    private function settings(?Config\Action $action = null): void
    {
        $this->io->write('  <fg=cyan>Settings:</>', true, self::DEFAULT_VERBOSITY_MESSAGE);

        if (null === $action) {
            $this->justify(
                '[<comment>dependency-manager</comment>]',
                '<info>' . $this->dependencyManager . '</info>',
            );
            $this->justify(
                '[<comment>binary-directory (XDG_BIN_HOME)</comment>]',
                '<info>' . $_ENV['XDG_BIN_HOME'] . '</info>',
            );
            $this->justify(
                '[<comment>config-directory (XDG_CONFIG_HOME)</comment>]',
                '<info>' . $_ENV['XDG_CONFIG_HOME'] . '</info>',
            );
            return;
        }

        $configFile = $action->getOptions()->get('config-file');

        if (null !== $configFile) {
            $this->justify(
                '[<comment>config-file</comment>]',
                '<info>' . $configFile . '</info>',
            );
            $configPath = $configFile;
            $configPath = $this->formatter->format($configPath);
            $_ENV['\\' . __CLASS__ . '.config-file'] = $configPath;

            $this->justify(
                '[<comment>config-file (resolved)</comment>]',
                file_exists($configPath)
                    ? '<info>' . $configPath . '</info>'
                    : sprintf('<fg=yellow>%s</>', 'not found'),
            );
        }

        $forceColor = boolval(Util::getEnv('FORCE_COLOR', '0'));
        $default = $forceColor ? 'always' : 'auto';

        $colors = $action->getOptions()->get('colors', $default);
        $this->justify(
            '[<comment>colors</comment>]',
            '<info>' . $colors . '</info>',
        );

        if (!$this->config->useAnsiColors()) {
            $colors = 'never';
        }

        $colorized = match ($colors) {
            'never' => false,
            default => true, // always or auto values are acceptable
        };
        $_ENV['NO_COLOR'] = !$colorized;
        $_ENV['FORCE_COLOR'] = 'force' === $colors ? '1' : '0';

        if ('force' === $colors || $forceColor) {
            // fallback to color flag syntax, if the CLI tools used does not provide FORCE_COLOR env var support
            $colors = 'always';
        }

        $this->justify(
            '[<comment>colors (NO_COLOR)</comment>]',
            '<info>' . ($_ENV['NO_COLOR'] ? 'true' : 'false') . '</info>',
        );
        $this->justify(
            '[<comment>colors (FORCE_COLOR)</comment>]',
            '<info>' . ($_ENV['FORCE_COLOR'] ? 'true' : 'false') . '</info>',
        );

        $packageRequirement = $action->getOptions()->get('package-require', []);
        if (!is_array($packageRequirement)) {
            $packageRequirement = [$packageRequirement];
        }
        $this->justify(
            '[<comment>package-require</comment>]',
            rtrim(
                '<info>' . ($packageRequirement[0] ?? 'false') . '</info>'
                . ' ' . ($packageRequirement[1] ?? ''),
            ),
        );

        $this->io->write('  <fg=cyan>Command: </>', false, self::DEFAULT_VERBOSITY_MESSAGE);
        $this->io->write($action->getAction(), true, self::DEFAULT_VERBOSITY_MESSAGE);

        $colorsFlag = $this->plugin->getOptions()->get($colors . '-colors-flag', '');
        if ('' === $colorsFlag) {
            return;
        }
        $_ENV['\\' . __CLASS__ . '.colors-flag'] = $colorsFlag;
    }

    private function justify(string $first, ?string $second = null, ?int $verbosity = self::DEFAULT_VERBOSITY_MESSAGE): void
    {
        $second        = (string) $second;
        $dashWidth     = (new Terminal())->getWidth() - ($this->strwidth($first) + $this->strwidth($second));
        // remove left and right margins because we're going to add 1 space on each side (after/before the text).
        // if we don't have a second element, we just remove the left margin
        $dashWidth -= $second === '' ? 1 : 2;
        $sep = $dashWidth >= 0 ? str_repeat('.', $dashWidth) : '';

        $this->io->write([$first, $sep, $second], false, $verbosity);
        $this->io->write('', true, $verbosity);
    }

    private function strwidth(string $string): int
    {
        if (function_exists('mb_strwidth')) {
            return mb_strwidth($string);
        }
        return strlen($string);
    }
}
