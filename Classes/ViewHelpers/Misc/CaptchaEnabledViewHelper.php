<?php
namespace In2\Femanager\ViewHelpers\Misc;

/**
 * Look if captcha is enabled
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class CaptchaEnabledViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Check if captcha is enabled
	 *
	 * @param \array $settings TypoScript
	 * @return \bool
	 */
	public function render($settings) {
		// if sr_freecap is not loaded
		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_freecap')) {
			return FALSE;
		}

		// is captcha enabled in TypoScript
		$controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
		if (empty($settings[$controllerName]['validation']['captcha']['captcha'])) {
			return FALSE;
		}

		return TRUE;
	}
}