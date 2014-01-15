<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * Check if this field is a required field
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class IsRequiredFieldViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Check if this field is a required field
	 *
	 * @param \string $fieldName
	 * @param \string $actionName
	 * @return \bool
	 */
	public function render($fieldName, $actionName = 'editAction') {
		$action = str_replace('Action', '', $actionName);
		$configuration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		if (
			isset($configuration['settings'][$action]['validation'][$fieldName]['required']) &&
			$configuration['settings'][$action]['validation'][$fieldName]['required'] === '1'
		) {
			return TRUE;
		}
		return FALSE;
	}
}