<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\Comment;

use PhpCsFixer\Fixer\Comment\FileDocblockBeforeDeclareFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Comment\FileDocblockBeforeDeclareFixer
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\Comment\FileDocblockBeforeDeclareFixer>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[CoversClass(FileDocblockBeforeDeclareFixer::class)]
final class FileDocblockBeforeDeclareFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    #[DataProvider('provideFixCases')]
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: null|string}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'file docblock after declare is moved before it' => [
            "<?php\n\n/**\n * Bootstrap for the application.\n */\n\ndeclare(strict_types=1);\n\nnamespace App;\n",
            "<?php\n\ndeclare(strict_types=1);\n\n/**\n * Bootstrap for the application.\n */\n\nnamespace App;\n",
        ];

        yield 'declare with several directives' => [
            "<?php\n\n/**\n * File header.\n */\n\ndeclare(strict_types=1, ticks=1);\n\nnamespace App;\n",
            "<?php\n\ndeclare(strict_types=1, ticks=1);\n\n/**\n * File header.\n */\n\nnamespace App;\n",
        ];

        yield 'already correctly ordered is left untouched' => [
            "<?php\n\n/**\n * Bootstrap.\n */\n\ndeclare(strict_types=1);\n\nnamespace App;\n",
        ];

        yield 'no declare statement is left untouched' => [
            "<?php\n\n/**\n * Bootstrap.\n */\n\nnamespace App;\n",
        ];

        yield 'docblock attached to a class is left untouched' => [
            "<?php\n\ndeclare(strict_types=1);\n\n/**\n * Class docblock.\n */\nclass Foo {}\n",
        ];

        yield 'docblock preceded by a line comment is left untouched' => [
            "<?php\n\ndeclare(strict_types=1);\n\n// note\n/**\n * Bootstrap.\n */\n\nnamespace App;\n",
        ];
    }
}
