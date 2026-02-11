<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ExplodeViewHelper extends AbstractViewHelper
{
    /**
     * @deprecated Argument seperator will be removed V14
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'Any list (e.g. "a,b,c,d")', true);
        $this->registerArgument('seperator', 'string', 'Separator sign (e.g. ",")', false, ',');
        $this->registerArgument('separator', 'string', 'Separator sign (e.g. ",")', false, ',');
        $this->registerArgument('trim', 'bool', 'Should be trimmed?', false, true);
    }

    public function render(): array
    {
        if ($this->arguments['seperator'] !== ',') {
            trigger_error('Argument seperator will be replaced with "separator" in V14', E_USER_DEPRECATED);
            $this->arguments['separator'] = $this->arguments['seperator'];
        }

        $string = $this->arguments['string'];
        $separator = $this->arguments['separator'];
        $trim = $this->arguments['trim'];

        return $trim ? GeneralUtility::trimExplode($separator, $string, true) : explode($separator, (string)$string);
    }
}
