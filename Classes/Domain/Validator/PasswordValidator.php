<?php
namespace In2\Femanager\Domain\Validator;

class PasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * Validation of given Params
	 *
	 * @param $user
	 * @return bool
	 */
	public function isValid($user) {
		$piVars = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_femanager_pi1');
		$password = $user->getPassword();
		$passwordRepeat = $piVars['password_repeat'];

		if ($password != $passwordRepeat) {
			$this->addError('validationErrorPasswordRepeat', 'password');
			return FALSE;
		}

		return TRUE;
	}
}
?>