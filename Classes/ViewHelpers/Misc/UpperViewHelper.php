<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class UpperViewHelper
 */
class UpperViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'string', false);
    }

    public function render(): string
    {
        return ucfirst((string)($this->arguments['string'] ?? ''));
    }
}
