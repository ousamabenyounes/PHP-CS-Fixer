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

namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Ousama Ben Younes <benyounes.ousama@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileDocblockBeforeDeclareFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A file-level docblock placed after `declare` statements must be moved before them, as required by PSR-12.',
            [
                new CodeSample(
                    "<?php\n\ndeclare(strict_types=1);\n\n/**\n * Bootstrap for the application.\n */\n\nnamespace App;\n",
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after DeclareStrictTypesFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isMonolithicPhp()
            && $tokens->isTokenKindFound(\T_DECLARE)
            && $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $declareIndex = $tokens->getNextMeaningfulToken(0);
        if (null === $declareIndex || !$tokens[$declareIndex]->isGivenKind(\T_DECLARE)) {
            return;
        }

        $openParenthesisIndex = $tokens->getNextMeaningfulToken($declareIndex);
        if (null === $openParenthesisIndex || !$tokens[$openParenthesisIndex]->equals('(')) {
            return;
        }

        $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesisIndex);
        $semicolonIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
        if (null === $semicolonIndex || !$tokens[$semicolonIndex]->equals(';')) {
            return; // e.g. block form `declare(ticks=1) { ... }` — out of scope
        }

        // A doc comment is not a "meaningful" token, so it is skipped here and the next
        // meaningful token following the `declare` statement is the `namespace` keyword.
        $namespaceIndex = $tokens->getNextMeaningfulToken($semicolonIndex);
        if (null === $namespaceIndex || !$tokens[$namespaceIndex]->isGivenKind(\T_NAMESPACE)) {
            return; // a docblock followed by anything but a namespace belongs to that structure
        }

        // Between the `declare` statement and the namespace there must be exactly one
        // doc comment (the file-level docblock) surrounded only by whitespace.
        $docBlockIndex = null;
        for ($index = $semicolonIndex + 1; $index < $namespaceIndex; ++$index) {
            if ($tokens[$index]->isWhitespace()) {
                continue;
            }

            if (null === $docBlockIndex && $tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                $docBlockIndex = $index;

                continue;
            }

            return; // e.g. a leading line comment or a second docblock — out of scope
        }

        $whitespaceBeforeDocBlockIndex = $docBlockIndex - 1;
        $whitespaceAfterDocBlockIndex = $docBlockIndex + 1;
        if (null === $docBlockIndex
            || !$tokens[$whitespaceBeforeDocBlockIndex]->isWhitespace()
            || !$tokens[$whitespaceAfterDocBlockIndex]->isWhitespace()
        ) {
            return;
        }

        $reordered = [
            $tokens[$docBlockIndex],
            $tokens[$whitespaceBeforeDocBlockIndex],
        ];
        for ($declareTokenIndex = $declareIndex; $declareTokenIndex <= $semicolonIndex; ++$declareTokenIndex) {
            $reordered[] = $tokens[$declareTokenIndex];
        }
        $reordered[] = $tokens[$whitespaceAfterDocBlockIndex];

        $tokens->overrideRange($declareIndex, $whitespaceAfterDocBlockIndex, $reordered);
    }
}
