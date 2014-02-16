<?php
namespace In2\Femanager\Domain\Validator;

/**
 * Class CaptchaValidator
 */
class CaptchaValidator extends \In2\Femanager\Domain\Validator\AbstractValidator {

	/**
	 * Validation of given Params
	 *
	 * @param $user
	 * @return bool
	 */
	public function isValid($user) {
		$this->init();

		if (!$this->captchaEnabled()) {
			return TRUE;
		}
		$captchaCode = $this->pluginVariables['captcha'];

		$freecapCaptchaValidator = $this->objectManager->get('SJBR\SrFreecap\Validation\Validator\CaptchaValidator');
		if ($freecapCaptchaValidator->isValid($captchaCode)) {
			return TRUE;
		}

		$this->addError('validationErrorCaptcha', 'captcha');
		return FALSE;
	}

	/**
	 * Check if captcha is enabled (TypoScript, and sr_freecap loaded)
	 *
	 * @return bool
	 */
	protected function captchaEnabled() {
		// if sr_freecap is not loaded
		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_freecap')) {
			return FALSE;
		}

		// if not enabled via TypoScript
		if (empty($this->validationSettings['captcha']['captcha'])) {
			return FALSE;
		}

		return TRUE;
	}
}