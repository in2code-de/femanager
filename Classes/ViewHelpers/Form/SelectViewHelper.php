<?php

declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper as OriginalSelectViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * Class SelectViewHelper
 */
class SelectViewHelper extends OriginalSelectViewHelper
{
    /**
     * Initialize
     * @throws FluidViewHelperException
     */
    public function initialize()
    {
        parent::initialize();

        $this->tag->addAttribute('data-selected-value', $this->getSelectedValue());
    }

    /**
     * Initialize
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('defaultOption', 'string', 'value to prepend', false);
    }

    /**
     * Options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        if (!empty($this->arguments['defaultOption'])) {
            $options = ['' => $this->arguments['defaultOption']] + $options;
        }
        return $options;
    }

    /**
     * Retrieves the selected value(s)
     *
     * @return mixed value string or an array of strings
     * @throws FluidViewHelperException
     */
    protected function getSelectedValue()
    {
        $selectedValue = parent::getSelectedValue();

        // set preselection from TypoScript
        if (empty($selectedValue)) {
            if (! $this->renderingContext instanceof RenderingContext) {
                throw new FluidViewHelperException(
                    'Something went wrong; RenderingContext should be available in ViewHelper',
                    1638341673
                );
            }
            $controllerName = strtolower($this->renderingContext->getRequest()->getControllerName());
            $contentObject = $this->configurationManager->getContentObject();
            $typoScript = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );

            $prefillTypoScript =
                $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'] ?? [];

            if (!empty($prefillTypoScript[$this->getFieldName()])) {
                $selectedValue = $contentObject->cObjGetSingle(
                    $prefillTypoScript[$this->getFieldName()],
                    $prefillTypoScript[$this->getFieldName() . '.']
                );
            }
        }

        return $selectedValue;
    }

    /**
     * Get Field name
     *
     * @return string
     */
    protected function getFieldName()
    {
        preg_match_all('/\[.*?\]/i', $this->getNameWithoutPrefix(), $name);
        return str_replace(['[', ']'], '', $name[0][0]);
    }
}
