<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Validation;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class IsRequiredFieldViewHelper
 */
class IsRequiredFieldViewHelper extends AbstractValidationViewHelper
{
    protected ConfigurationManagerInterface $configurationManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function __construct(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Check if this field is a required field
     *
     * @return bool
     */
    public function render()
    {
        $fieldName = $this->arguments['fieldName'];
        $settings = $this->getSettingsConfiguration();

        return !empty($settings[$this->getControllerName()][$this->getValidationName()][$fieldName]['required']);
    }

    protected function getSettingsConfiguration(string $pluginName = 'Pi1'): array
    {
        return (array)$this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            $pluginName
        );
    }

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string ', 'Check if this field is a required field', true);
    }
}
