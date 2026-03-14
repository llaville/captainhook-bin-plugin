<?php

declare(strict_types=1);

/**
 * This file is part of the CaptainHook-Bin-Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bartlett\CaptainHookBinPluginTest;

use Bartlett\CaptainHookBinPlugin\BinPlugin;
use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Plugin;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since Release 1.0.0
 * @author Laurent Laville
 */
#[CoversClass(BinPlugin::class)]
class BinPluginTest extends TestCase
{
    public function testBeforeHook(): void
    {
        $this->io->expects($this->atLeastOnce())->method('write');

        $plugin = new BinPlugin();
        $plugin->configure($this->config, $this->io, $this->repo, new Plugin(BinPlugin::class));

        $plugin->beforeHook($this->hook);
    }

    public function testAfterHook(): void
    {
        $this->io->expects($this->atLeastOnce())->method('write');

        $plugin = new BinPlugin();
        $plugin->configure($this->config, $this->io, $this->repo, new Plugin(BinPlugin::class));

        $plugin->afterHook($this->hook);
    }

    public function testActionWasSkipped(): void
    {
        $action = new Action('foo', ['package-require' => ['vendorName/PackageName']]);

        // Invocation Rule is important :
        // @see https://github.com/captainhook-git/captainhook/issues/293#issuecomment-4052943850
        // @see https://github.com/captainhook-git/captainhook/issues/293#issuecomment-4053100543
        $this->hook->expects($this->never())
            ->method('beforeAction')
            ->with($action);

        $plugin = new BinPlugin();
        $plugin->configure($this->config, $this->io, $this->repo, new Plugin(BinPlugin::class));

        $plugin->beforeAction($this->hook, $action);
    }

    public function testAfterAction(): void
    {
        $this->io->expects($this->atLeastOnce())->method('write');

        $plugin = new BinPlugin();
        $plugin->configure($this->config, $this->io, $this->repo, new Plugin(BinPlugin::class));

        $plugin->afterAction($this->hook, new Action('foo'));
    }
}
