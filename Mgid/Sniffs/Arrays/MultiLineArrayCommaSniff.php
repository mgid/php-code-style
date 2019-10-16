<?php

namespace Mgid\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws warnings if the last item in a multi line array does not have a trailing comma.
 */
class MultiLineArrayCommaSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $open = $tokens[$stackPtr];

        if ($open['code'] === T_ARRAY) {
            $closePtr = $open['parenthesis_closer'];
        } else {
            $closePtr = $open['bracket_closer'];
        }

        if ($open['line'] <> $tokens[$closePtr]['line']) {
            $arrayIsNotEmpty = $phpcsFile->findPrevious(
                [
                    T_WHITESPACE,
                    T_COMMENT,
                    T_ARRAY,
                    T_OPEN_PARENTHESIS,
                    T_OPEN_SHORT_ARRAY,
                ],
                $closePtr - 1,
                $stackPtr,
                true
            );

            if ($arrayIsNotEmpty !== false) {
                $lastCommaPtr = $phpcsFile->findPrevious(T_COMMA, $closePtr, $stackPtr);

                while ($lastCommaPtr < $closePtr - 1) {
                    $lastCommaPtr++;

                    if ($tokens[$lastCommaPtr]['code'] !== T_WHITESPACE
                        && $tokens[$lastCommaPtr]['code'] !== T_COMMENT
                    ) {
                        $fix = $phpcsFile->addFixableError(
                            'Add a comma after each item in a multi-line array',
                            $stackPtr,
                            'Invalid'
                        );

                        if ($fix === true) {
                            $ptr = $phpcsFile->findPrevious(
                                [T_WHITESPACE, T_COMMENT],
                                $closePtr - 1,
                                $stackPtr,
                                true
                            );

                            $phpcsFile->fixer->addContent($ptr, ',');
                            $phpcsFile->fixer->endChangeset();
                        }

                        break;
                    }
                }
            }
        }
    }
}
