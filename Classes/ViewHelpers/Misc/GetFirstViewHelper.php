<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * @deprecated will be removed with V14
 */
class GetFirstViewHelper extends AbstractFormFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('objectStorage', 'object', 'first subobject of objectStorage');
    }

    /**
     * View helper to get first subobject of objectstorage
     */
    public function render(): mixed
    {
        trigger_error('This viewHelper will be removed with V14. Use objects.0 to access the first object', E_USER_DEPRECATED);

        $objectStorage = $this->arguments['objectStorage'];
        if ($objectStorage === null) {
            return '';
        }

        foreach ($objectStorage as $object) {
            return $object;
        }

        // try to get value from originalRequest
        // seperate if version is 6.2 or lower
        if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper') && ((method_exists($this, 'hasMappingErrorOccured') && $this->hasMappingErrorOccured()) ||
            (method_exists($this, 'hasMappingErrorOccurred') && $this->hasMappingErrorOccurred()))) {
            return $this->getValueAttribute();
        }

        return '';
    }
}
