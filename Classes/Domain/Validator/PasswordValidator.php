<?php
namespace In2\Femanager\Domain\Validator;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PasswordValidator
 */
class PasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * configurationManager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	public $configurationManager;

	/**
	 * Content Object
	 *
	 * @var object
	 */
	public $cObj;

	/**
	 * Action Name
	 *
	 * @var \string
	 */
	protected $actionName;

	/**
	 * Validation of given Params
	 *
	 * @param $user
	 * @return bool
	 */
	public function isValid($user) {
		$this->init();

		// if password fields are not shown
		if (!$this->passwordFieldsAdded()) {
			return TRUE;
		}

		$piVars = GeneralUtility::_GP('tx_femanager_pi1');
		$password = $user->getPassword();
		$passwordRepeat = $piVars['password_repeat'];

		if ($password != $passwordRepeat) {
			$this->addError('validationErrorPasswordRepeat', 'password');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check if password fields are added with flexform
	 *
	 * @return bool
	 */
	protected function passwordFieldsAdded() {
		$flexFormValues = GeneralUtility::xml2array($this->cObj->data['pi_flexform']);
		if (is_array($flexFormValues)) {
			$fields = $flexFormValues['data'][$this->actionName]['lDEF']['settings.' . $this->actionName . '.fields']['vDEF'];
			if (empty($fields) || GeneralUtility::inList($fields, 'password')) {
				// password fields are added to form
				return TRUE;
			}
		}

		// password fields are not added to form
		return FALSE;
	}

	/**
	 * Initialize Validator Function
	 *
	 * @return void
	 */
	protected function init() {
		$this->cObj = $this->configurationManager->getContentObject();
		$piVars = GeneralUtility::_GP('tx_femanager_pi1');
		$this->actionName = $piVars['__referrer']['@action'];
	}
}