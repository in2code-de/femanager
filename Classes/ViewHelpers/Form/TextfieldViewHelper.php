<?php

declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\ViewHelpers\Form\TextfieldViewHelper as OriginalTextfieldViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * Class TextfieldViewHelper
 */
class TextfieldViewHelper extends OriginalTextfieldViewHelper
{

    /**
     * Get the value of this form element (changed to prefill from TypoScript)
     * Either returns arguments['value'], or the correct value for Object Access.
     *
     * @return mixed Value
     * @throws FluidViewHelperException
     */
    protected function getValueAttribute()
    {
        $value = parent::getValueAttribute();

        // prefill value from TypoScript
        if (empty($value) && $this->getValueFromTypoScript()) {
            $value = $this->getValueFromTypoScript();
        }

        return $value;
    }

    /**
     * Read value from TypoScript
     *
     * @return string Value from TypoScript
     * @throws FluidViewHelperException
     */
    protected function getValueFromTypoScript(): string
    {
        if (! $this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341334
            );
        }
        $controllerName = strtolower($this->renderingContext->getRequest()->getControllerName());
        $contentObject = $this->configurationManager->getContentObject();
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $prefillTypoScript = $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'];
        $value = $contentObject->cObjGetSingle(
            $prefillTypoScript[$this->arguments['property']],
            $prefillTypoScript[$this->arguments['property'] . '.']
        );
        return $value;
    }
}
