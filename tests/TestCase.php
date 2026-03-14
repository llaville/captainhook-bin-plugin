<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bartlett\CaptainHookBinPluginTest;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\DefaultIO;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Runner\Hook;
use PHPUnit\Framework\MockObject\MockObject;
use SebastianFeldmann\Git\Repository;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected MockObject $hook;
    protected MockObject $repo;
    protected MockObject $config;
    protected MockObject $io;

    protected function setUp(): void
    {
        $this->hook   = $this->createHookMock();
        $this->repo   = $this->createRepositoryMock();
        $this->config = $this->createConfigMock();
        $this->io     = $this->createIOMock();
    }

    /**
     * Create a hook mock
     */
    protected function createHookMock(): MockObject&Hook
    {
        return $this->getMockBuilder(Hook::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create a repository mock
     */
    protected function createRepositoryMock(string $root = '', string $hooksDir = ''): MockObject&Repository
    {
        $repo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->method('getRoot')->willReturn($root);
        $repo->method('getHooksDir')->willReturn(empty($hooksDir) ? $root . '/.git/hooks' : $hooksDir);

        return $repo;
    }

    /**
     * Create a config mock
     */
    protected function createConfigMock(bool $loadedFromFile = false, string $path = ''): MockObject&Config
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config->method('isLoadedFromFile')->willReturn($loadedFromFile);
        $config->method('getPath')->willReturn($path);

        return $config;
    }

    /**
     * Create an IO mock
     */
    protected function createIOMock(): MockObject&IO
    {
        return $this->getMockBuilder(DefaultIO::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
