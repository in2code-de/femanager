<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class JsonEncodeViewHelper
 */
class JsonEncodeViewHelper extends AbstractViewHelper
{

    /**
     * @var null
     */
    protected $escapeOutput = false;

    /**
     * @param array $array
     * @return string
     */
    public function render(array $array): string
    {
        return json_encode($array);
    }
}
