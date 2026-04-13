<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$baseDir = dirname(__DIR__, 2);

return ECSConfig::configure()
    ->withPaths([
        $baseDir . '/examples',
        $baseDir . '/src',
        $baseDir . '/tests',
    ])

    ->withSkip([
        SingleLineEmptyBodyFixer::class,
    ])

    ->withPhpCsFixerSets(
        perCS30: true,
    )
;
