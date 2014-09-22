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
 * Extend select viewhelper with default option
 *
 * Class SelectViewHelper
 */
class SelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('defaultOption', 'string', 'value to prepend', FALSE);
	}

	/**
	 * Options
	 *
	 * @return array
	 */
	protected function getOptions() {
		$options = parent::getOptions();
		if (!empty($this->arguments['defaultOption'])) {
			$options = array('' => $this->arguments['defaultOption']) + $options;
		}
		return $options;
	}

	/**
	 * Retrieves the selected value(s)
	 *
	 * @return mixed value string or an array of strings
	 */
	protected function getSelectedValue() {
		$selectedValue = parent::getSelectedValue();

		// set preselection from TypoScript
		if (empty($selectedValue)) {
			$controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
			$cObj = $this->configurationManager->getContentObject();
			$typoScript = $this->configurationManager->getConfiguration(
				\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
			);
			$prefillTypoScript = $typoScript['plugin.']['tx_femanager.']['settings.'][$controllerName . '.']['prefill.'];
			if (!empty($prefillTypoScript[$this->getFieldName()])) {
				$selectedValue = $cObj->cObjGetSingle(
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
	protected function getFieldName() {
		preg_match_all( '/\[.*?\]/i', $this->getNameWithoutPrefix(), $name);
		return str_replace(array('[', ']'), '', $name[0][0]);
	}
}