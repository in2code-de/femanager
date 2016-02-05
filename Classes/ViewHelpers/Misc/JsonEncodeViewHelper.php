<?php
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper like json_encode()
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class JsonEncodeViewHelper extends AbstractViewHelper
{

    /**
     * View helper like json_encode()
     *
     * @param array $array
     * @return string
     */
    public function render($array)
    {
        return json_encode($array);
    }
}
