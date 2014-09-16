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
	 * @param \array $settings TypoScript
	 * @param \string $fieldName Fieldname
	 * @param \array $additionalAttributes AdditionalAttributes
	 * @return \array
	 */
	public function render($settings, $fieldName, $additionalAttributes = array()) {
		$array = $additionalAttributes;

		$controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
		if ($settings[$controllerName]['validation']['_enable']['client'] !== '1') {
			return $array;
		}

		$validationString = $this->getValidationString($settings, $fieldName, $controllerName);
		if (!empty($validationString)) {
			$array['data-validation'] = $validationString;
			if (!empty($additionalAttributes['data-validation'])) {
				$array['data-validation'] .= ',' . $additionalAttributes['data-validation'];
			}
		}
		return $array;
	}

	/**
	 * Get validation string like
	 * 		required, email, min(10), max(10), intOnly,
	 * 		lettersOnly, uniqueInPage, uniqueInDb, date,
	 * 		mustInclude(number|letter|special), inList(1|2|3)
	 *
	 * @param \array $settings Validation TypoScript
	 * @param \string $fieldName Fieldname
	 * @param \string $controllerName "new", "edit", "invitation"
	 * @return \string
	 */
	protected function getValidationString($settings, $fieldName, $controllerName) {
		$string = '';

		// for each field
		foreach ((array) $settings[$controllerName]['validation'][$fieldName] as $validation => $configuration) {

			switch ($validation) {
				case 'required':
					// or
				case 'email':
					// or
				case 'intOnly':
					// or
				case 'lettersOnly':
				// or
				case 'uniqueInPage':
				// or
				case 'uniqueInDb':
				// or
				case 'date':
					if ($configuration == 1) {
						$string .= $validation;
					}
					break;

				case 'min':
					// or
				case 'max':
					// or
				case 'mustInclude':
					// or
				case 'inList':
				default:
					$string .= $validation;
					$string .= '(' . str_replace(',', '|', $configuration) . ')';
			}

			$string .= ',';
		}

		return substr($string, 0, -1);
	}
}