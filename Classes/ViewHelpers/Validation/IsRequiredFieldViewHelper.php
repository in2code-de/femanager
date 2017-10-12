<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Validation;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class IsRequiredFieldViewHelper
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
        return (array)$this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            'Pi1'
        );
    }
}
