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
	 * Plugin Variables
	 *
	 * @var array
	 */
	public $piVars = array();

	/**
	 * TypoScript Configuration
	 *
	 * @var array
	 */
	public $configuration = array();

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

		// if password fields are not active or if keep function active
		if (!$this->passwordFieldsAdded() || $this->keepPasswordIfEmpty()) {
			return TRUE;
		}

		$password = $user->getPassword();
		$passwordRepeat = $this->piVars['password_repeat'];

		if ($password !== $passwordRepeat) {
			$this->addError('validationErrorPasswordRepeat', 'password');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check if Passwords are empty and if keep configuration is active
	 *
	 * @return bool
	 */
	protected function keepPasswordIfEmpty() {
		if (
			isset($this->configuration['settings']['edit']['misc']['keepPasswordIfEmpty']) &&
			$this->configuration['settings']['edit']['misc']['keepPasswordIfEmpty'] == 1 &&
			isset($this->piVars['user']['password']) &&
			$this->piVars['user']['password'] === '' &&
			isset($this->piVars['password_repeat']) &&
			$this->piVars['password_repeat'] === ''
		) {
			return TRUE;
		}
		return FALSE;
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
		$this->configuration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$this->cObj = $this->configurationManager->getContentObject();
		$this->piVars = GeneralUtility::_GP('tx_femanager_pi1');
		$this->actionName = $this->piVars['__referrer']['@action'];
	}
}