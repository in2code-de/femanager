<?php
namespace In2code\Femanager\ViewHelpers\Validation;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Check if this field is a required field
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class IsRequiredFieldViewHelper extends AbstractValidationViewHelper
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * Check if this field is a required field
     *
     * @param string $fieldName
     * @return bool
     */
    public function render($fieldName)
    {
        $settings = $this->getSettingsConfiguration();
        return !empty($settings[$this->getControllerName()][$this->getValidationName()][$fieldName]['required']);
    }

    /**
     * @return array
     */
    protected function getSettingsConfiguration()
    {
        $configuration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        return (array) $configuration['settings'];
    }
}
