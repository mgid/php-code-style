<?php

namespace Mgid\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class EmptyLinesSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_WHITESPACE];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $this->assertMaximumOneEmptyLineBetweenContent($phpcsFile, $stackPtr);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int                         $stackPtr
     *
     * @return void
     */
    protected function assertMaximumOneEmptyLineBetweenContent(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['content'] !== $phpcsFile->eolChar) {
            return;
        }

        if (false === isset($tokens[($stackPtr + 1)]) || $tokens[($stackPtr + 1)]['content'] !== $phpcsFile->eolChar) {
            return;
        }

        if (false === isset($tokens[($stackPtr + 2)]) || $tokens[($stackPtr + 2)]['content'] !== $phpcsFile->eolChar) {
            return;
        }

        $error = 'Found more than a single empty line between content';

        $fix = $phpcsFile->addFixableError($error, ($stackPtr + 2), 'EmptyLines');

        if ($fix) {
            $phpcsFile->fixer->replaceToken($stackPtr + 2, '');
        }
    }
}
