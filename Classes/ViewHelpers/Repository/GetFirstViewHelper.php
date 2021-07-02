<?php
declare(strict_types = 1);
namespace In2code\Femanager\ViewHelpers\Repository;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetFirstViewHelper
 */
class GetFirstViewHelper extends AbstractViewHelper
{

    /**
     * Call getFirst() method of object storage
     *
     * @return object|null
     */
    public function render()
    {
        $objects = $this->arguments['objects'];
        if (method_exists($objects, 'getFirst')) {
            return $objects->getFirst();
        }
        return null;
    }

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('objects', 'object ', 'Call getFirst() method of object storage', true);
    }
}
