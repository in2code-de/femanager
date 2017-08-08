<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class UpperViewHelper
 */
class UpperViewHelper extends AbstractViewHelper
{

    /**
     * @param string $string
     * @return string
     */
    public function render(string $string = ''): string
    {
        return ucfirst($string);
    }
}
