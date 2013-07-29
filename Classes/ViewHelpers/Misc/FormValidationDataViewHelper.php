<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * Set javascript validation data for input fields
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class FormValidationDataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Set javascript validation data for input fields
	 *
	 * @param \array $settings			TypoScript
	 * @param \string $fieldName		Fieldname
	 * @return \array
	 */
	public function render($settings, $fieldName) {
		$actionName = $this->controllerContext->getRequest()->getControllerActionName();
		if ($settings[$actionName]['validation']['_enable']['client'] != 1) {
			return array();
		}

		$array = array(
			'data-validation' => $this->getValidationString($settings, $fieldName, $actionName)
		);
		return $array;
	}

	/**
	 * Get validation string like
	 * 		required, email, min(10), max(10), intOnly, lettersOnly, uniqueInPage, uniqueInDb, date, mustInclude(number|letter|special), inList(1|2|3)
	 *
	 * @param \array $settings			Validation TypoScript
	 * @param \string $fieldName		Fieldname
	 * @param \string $actionName		"new", "edit"
	 * @return \string
	 */
	protected function getValidationString($settings, $fieldName, $actionName) {
		$string = '';

		// for each field
		foreach ((array) $settings[$actionName]['validation'][$fieldName] as $validation => $configuration) {

			switch ($validation) {
				case 'required':
				case 'email':
				case 'intOnly':
				case 'lettersOnly':
				case 'uniqueInPage':
				case 'uniqueInDb':
				case 'date':
					if ($configuration == 1) {
						$string .= $validation;
					}
					break;

				case 'min':
				case 'max':
				case 'mustInclude':
				case 'inList':
				default:
					$string .= $validation;
					$string .= '(' . str_replace(',', '|', $configuration) . ')';
					break;
			}

			$string .= ',';
		}

		return substr($string, 0, -1);
	}
}

?>
