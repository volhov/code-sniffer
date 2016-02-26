<?php

namespace Spryker\Sniffs\Commenting;

abstract class AbstractDemoshopFileDocBlockSniff implements \PHP_CodeSniffer_Sniff
{

    const EXPECTED_COMMENT_FIRST_LINE_STRING = 'This file is part of the Spryker Demoshop.';
    const EXPECTED_COMMENT_SECOND_LINE_STRING = 'For full license information, please view the LICENSE file that was distributed with this source code.';

    const DEMOSHOP_NAMESPACE = 'Pyz';

    /**
     * @var array
     */
    protected $sprykerTestNamespaces = [
        'Unit',
        'Functional',
    ];

    /**
     * @var array
     */
    protected $sprykerApplications = [
        'Client',
        'Shared',
        'Yves',
        'Zed',
    ];

    /**
     * @return array
     */
    public function register()
    {
        return [
            T_NAMESPACE
        ];
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param int $stackPointer
     *
     * @return bool
     */
    protected function isPyzNamespace(\PHP_CodeSniffer_File $phpCsFile, $stackPointer)
    {
        $firstNamespaceTokenPosition = $phpCsFile->findNext(T_STRING, $stackPointer);
        if ($firstNamespaceTokenPosition) {
            $firstNamespaceString = $phpCsFile->getTokens()[$firstNamespaceTokenPosition]['content'];
            $secondNamespaceTokenPosition = $phpCsFile->findNext(T_STRING, $firstNamespaceTokenPosition + 1);

            if (!$secondNamespaceTokenPosition) {
                return false;
            }

            $secondNamespaceString = $phpCsFile->getTokens()[$secondNamespaceTokenPosition]['content'];

            $isSprykerClass = ($firstNamespaceString === self::DEMOSHOP_NAMESPACE && in_array($secondNamespaceString, $this->sprykerApplications));
            $isSprykerTestClass = (in_array($firstNamespaceString, $this->sprykerTestNamespaces) && $secondNamespaceString === self::DEMOSHOP_NAMESPACE);

            return ($isSprykerClass || $isSprykerTestClass);
        }

        return false;
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param int $stackPointer
     *
     * @return bool
     */
    protected function existsFileDocBlock(\PHP_CodeSniffer_File $phpCsFile, $stackPointer)
    {
        $fileDocBlockStartPosition = $phpCsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPointer);

        return ($fileDocBlockStartPosition !== false);
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param int $stackPointer
     *
     * @return void
     */
    protected function addFileDocBlock(\PHP_CodeSniffer_File $phpCsFile, $stackPointer)
    {
        $phpCsFile->fixer->beginChangeset();

        $this->clearFileDocBlock($phpCsFile, $stackPointer);

        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->addContent($stackPointer, '/**');
        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->addContent($stackPointer, ' * ' . self::EXPECTED_COMMENT_FIRST_LINE_STRING);
        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->addContent($stackPointer, ' * ' . self::EXPECTED_COMMENT_SECOND_LINE_STRING);
        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->addContent($stackPointer, ' */');
        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->addNewline($stackPointer);
        $phpCsFile->fixer->endChangeset();
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param int $stackPointer
     *
     * @return void
     */
    protected function clearFileDocBlock(\PHP_CodeSniffer_File $phpCsFile, $stackPointer)
    {
        $fileDocBlockStartPosition = $phpCsFile->findPrevious(T_OPEN_TAG, $stackPointer) + 1;

        $currentPosition = $fileDocBlockStartPosition;
        $endPosition = $phpCsFile->findNext([T_NAMESPACE], $currentPosition);
        do {
            $phpCsFile->fixer->replaceToken($currentPosition, '');
            $currentPosition++;
        } while ($currentPosition < $endPosition);
    }

}
