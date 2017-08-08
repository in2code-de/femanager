<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Repository;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetFirstViewHelper
 */
class GetFirstViewHelper extends AbstractViewHelper
{

    /**
     * Call getFirst() method of object storage
     *
     * @param object $objects
     * @return object|null
     */
    public function render($objects)
    {
        if (method_exists($objects, 'getFirst')) {
            return $objects->getFirst();
        }
        return null;
    }
}
