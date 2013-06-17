<?php
namespace In2\Femanager\Domain\Validator;

class GeneralValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * userRepository
	 *
	 * @var \In2\Femanager\Domain\Repository\UserRepository
	 * @inject
	 */
	protected $userRepository;

	/**
	 * configurationManager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	public $configurationManager;

	/**
	 * Former known as piVars
	 *
	 * @var array
	 */
	public $pluginVariables;

	/**
	 * Validationsettings
	 */
	public $validationSettings = array();

	/**
	 * Is Valid
	 */
	protected $isValid = TRUE;

	/**
	 * Must be there
	 */
	public function isValid($value) {
		return parent::isValid($value);
	}

	/**
	 * Validation for required
	 *
	 * @param \string $value
	 * @return \bool
	 */
	protected function validateRequired($value) {
		if (!is_object($value)) {
			return !empty($value);
		} elseif (count($value)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Validation for email
	 *
	 * @param \string $value
	 * @return \bool
	 */
	protected function validateEmail($value) {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($value);
	}

	/**
	 * Validation for Minimum of characters
	 *
	 * @param \string $value
	 * @param \string $validationSetting
	 * @return \bool
	 */
	protected function validateMin($value, $validationSetting) {
		if (strlen($value) < $validationSetting) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Validation for Maximum of characters
	 *
	 * @param \string $value
	 * @param \string $validationSetting
	 * @return \bool
	 */
	protected function validateMax($value, $validationSetting) {
		if (strlen($value) > $validationSetting) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Validation for Numbers only
	 *
	 * @param \string $value
	 * @return \bool
	 */
	protected function validateInt($value) {
		return is_numeric($value);
	}

	/**
	 * Validation for Letters only
	 *
	 * @param \string $value
	 * @return \bool
	 */
	protected function validateLetters($value) {
		if (preg_replace('/[^a-zA-Z_-]/', '', $value) == $value) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Validation for Unique in sysfolder
	 *
	 * @param \string $value
	 * @param \string $field
	 * @return \bool
	 */
	protected function validateUniquePage($value, $field) {
		$user = $this->userRepository->{'findOneBy' . ucfirst($field)}($value);
		return !is_object($user);
	}

	/**
	 * Validation for Unique in the db
	 *
	 * @param \string $value
	 * @param \string $field			Fieldname like "username" or "email"
	 * @return \bool
	 */
	protected function validateUniqueDb($value, $field) {
		$user = $this->userRepository->checkUniqueDb($field, $value);
		return !count($user);
	}

	/**
	 * Validation for "Must include some characters)
	 *
	 * @param \string $value
	 * @param \string $validationSettingList
	 * @return \bool
	 */
	protected function validateMustInclude($value, $validationSettingList) {
		$isValid = TRUE;
		$validationSettings = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $validationSettingList, 1);

		foreach ($validationSettings as $validationSetting) {

			switch ($validationSetting) {

				// value must include numbers
				case 'number':
					if (strlen(preg_replace('/[^0-9]/', '', $value)) === 0) {
						$isValid = false;
					}
					break;

				// value must include letters
				case 'letter':
					if (strlen(preg_replace('/[^a-zA-Z_-]/', '', $value)) === 0) {
						$isValid = false;
					}
					break;

				// value must include special characters (like .:,&äö#*+)
				case 'special':
					if (strlen(preg_replace('/[^a-zA-Z0-9]/', '', $value)) === strlen($value)) {
						$isValid = false;
					}
					break;
			}
		}
		return $isValid;
	}

	/**
	 * Validation for checking if values are in a given list
	 *
	 * @param \string $value
	 * @param \string $validationSettingList
	 * @return \bool
	 */
	protected function validateInList($value, $validationSettingList) {
		$validationSettings = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $validationSettingList, 1);
		return in_array($value, $validationSettings);
	}

	/**
	 * Init
	 */
	protected function init() {
		$config = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->pluginVariables = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_femanager_pi1');
		$action = 'new';
		if ($this->pluginVariables['__referrer']['@action'] == 'edit') {
			$action = 'edit';
		}
		$this->validationSettings = $config['settings'][$action]['validation'];
	}
}
?>