<?php
declare(strict_types = 1);
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * Class GetFirstViewHelper
 */
class GetFirstViewHelper extends AbstractFormFieldViewHelper
{

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('objectStorage', 'object', 'first subobject of objectstorage', false);
    }

    /**
     * View helper to get first subobject of objectstorage
     *
     * @return \mixed
     */
    public function render()
    {
        $objectStorage = $this->arguments['objectStorage'];
        if ($objectStorage === null) {
            return null;
        }
        foreach ($objectStorage as $object) {
            return $object;
        }

        // try to get value from originalRequest
        if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {
            // seperate if version is 6.2 or lower
            if ((method_exists($this, 'hasMappingErrorOccured') && $this->hasMappingErrorOccured()) ||
                (method_exists($this, 'hasMappingErrorOccurred') && $this->hasMappingErrorOccurred())
            ) {
                return $this->getValueAttribute();
            }
        }

        return null;
    }
}
