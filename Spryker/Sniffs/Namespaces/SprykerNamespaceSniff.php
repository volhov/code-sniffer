<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Spryker\Traits\BasicsTrait;

/**
 * Makes sure the namespace declared in each class file fits to the folder structure.
 */
class SprykerNamespaceSniff implements Sniff
{
    use BasicsTrait;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_CLASS, T_INTERFACE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $namespaceStatement = $this->getNamespaceStatement($phpcsFile);
        if (!$namespaceStatement) {
            return;
        }

        $filename = $phpcsFile->getFilename();

        preg_match('#/(src|tests)/(YvesUnit|YvesFunctional|Spryker|Unit/Spryker|Functional/Spryker|Acceptance)/(.+)#', $filename, $matches);
        if (!$matches) {
            return;
        }

        $extractedPath = $matches[2] . '/' . $matches[3];
        $pathWithoutFilename = substr($extractedPath, 0, strrpos($extractedPath, DIRECTORY_SEPARATOR));

        $namespace = $namespaceStatement['namespace'];
        $pathToNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $pathWithoutFilename);
        if ($namespace === $pathToNamespace) {
            return;
        }

        $error = sprintf('Namespace `%s` does not fit to folder structure `%s`', $namespace, $pathToNamespace);
        $phpcsFile->addError($error, $namespaceStatement['start'], 'NamespaceFolderMismatch');
    }
}
