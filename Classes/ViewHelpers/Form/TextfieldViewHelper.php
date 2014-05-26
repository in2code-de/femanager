<?php
namespace In2\Femanager\ViewHelpers\Form;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class TextfieldViewHelper
 */
class TextfieldViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\TextfieldViewHelper {

	/**
	 * Get the value of this form element (changed to prefill from TypoScript)
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @param boolean $convertObjects whether to convert objects to identifiers
	 * @return mixed Value
	 */
	protected function getValue($convertObjects = TRUE) {
		$value = parent::getValue($convertObjects);

		// prefill value from TypoScript
		if (empty($value) && $this->getValueFromTypoScript()) {
			$value = $this->getValueFromTypoScript();
		}

		return $value;
	}

	/**
	 * Read value from TypoScript
	 *
	 * @return \string Value from TypoScript
	 */
	protected function getValueFromTypoScript() {
		$controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
		$cObj = $this->configurationManager->getContentObject();
		$typoScript = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		$prefillTypoScript = $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'];
		$value = $cObj->cObjGetSingle(
			$prefillTypoScript[$this->arguments['property']],
			$prefillTypoScript[$this->arguments['property'] . '.']
		);
		return $value;
	}
}