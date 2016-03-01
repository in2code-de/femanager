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
     * Initialize arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('controllerName', 'string', 'Controller name');

        // Todo: Remove deprecated call of actionName in one of the next minor versions
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
     * @return mixed
     */
    protected function getControllerName()
    {
        if ($this->arguments['controllerName'] !== null) {
            $controllerName = strtolower($this->arguments['controllerName']);
        }

        // Todo: Remove deprecated call of actionName in one of the next minor versions
        if (empty($controllerName) && $this->arguments['actionName'] !== null) {
            $controllerName = str_replace('Action', '', $this->arguments['actionName']);
            GeneralUtility::deprecationLog(
                'Extension femanager: The call of IsRequiredFieldViewHelper with argument actionName is deprecated. ' .
                'Please use controllerName or the original Templates and Partials of the extension.'
            );
        }

        return $controllerName;
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
