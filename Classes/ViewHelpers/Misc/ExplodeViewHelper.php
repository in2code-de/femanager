<?php
declare(strict_types = 1);

namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ExplodeViewHelper
 */
class ExplodeViewHelper extends AbstractViewHelper
{
    /**
     *
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'Any list (e.g. "a,b,c,d")', false);
        $this->registerArgument('seperator', 'string', 'Separator sign (e.g. ",")', false, ',');
        $this->registerArgument('trim', 'bool', 'Should be trimmed?', false, true);
    }

    /**
     * View helper to explode a list
     * @return array
     */
    public function render()
    {
        $string = $this->arguments['string'];
        $separator = $this->arguments['seperator'];
        $trim = $this->arguments['trim'];

        return $trim ? GeneralUtility::trimExplode($separator, $string, true) : explode($separator, $string);
    }
}
