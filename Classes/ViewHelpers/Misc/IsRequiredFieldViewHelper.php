<?php
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Check if this field is a required field
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class IsRequiredFieldViewHelper extends AbstractViewHelper
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * Register argument "actionName" for older Partials
     *
     * Todo: Remove this function in one of the next minor versions
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('actionName', 'string', 'Action name');
    }

    /**
     * Check if this field is a required field
     *
     * @param string $fieldName
     * @return bool
     */
    public function render($fieldName)
    {
        $settings = $this->getSettingsConfiguration();
        return !empty($settings[$this->getControllerName()]['validation'][$fieldName]['required']);
    }

    /**
     * Get lowercase controller name
     *
     * @return string
     */
    protected function getControllerName()
    {
        return strtolower($this->controllerContext->getRequest()->getControllerName());
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

    /**
     * Register a new argument. Call this method from your ViewHelper subclass
     * inside the initializeArguments() method.
     *
     * Todo: Remove this function in one of the next minor versions
     *
     * @param string $name Name of the argument
     * @param string $type Type of the argument
     * @param string $description Description of the argument
     * @param bool $required If TRUE, argument is required. Defaults to FALSE.
     * @param mixed $defaultValue Default value of argument
     * @return \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper $this, to allow chaining.
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @api
     */
    protected function registerArgument($name, $type, $description, $required = false, $defaultValue = null)
    {
        if ($name === 'actionName') {
            GeneralUtility::deprecationLog(
                'Extension femanager: The call of IsRequiredFieldViewHelper with argument actionName is deprecated. ' .
                'Please remove this parameter or use original Templates and Partials of the extension.'
            );
        }
        return parent::registerArgument($name, $type, $description, $required, $defaultValue);
    }
}
