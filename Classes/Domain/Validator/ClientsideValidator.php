<?php
namespace In2\Femanager\Domain\Validator;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \In2\Femanager\Utility\Div;

class ClientsideValidator extends \In2\Femanager\Domain\Validator\GeneralValidator {

	/**
	 * Validation settings string
	 * 		possible validations for each field are:
	 * 		required, email, min(12), max(13), intOnly, lettersOnly, uniqueInPage, uniqueInDb, mustInclude(number,letter,special), inList(1,2,3)
	 *
	 * @var \string
	 */
	protected $validationSettingsString;

	/**
	 * Field Value
	 *
	 * @var \string
	 */
	protected $value;

	/**
	 * Field Name
	 *
	 * @var \string
	 */
	protected $fieldName;

	/**
	 * Error message container
	 *
	 * @var \array
	 */
	protected $messages = array();

	/**
	 * Validate Field
	 */
	public function validateField() {
		$validationSettings = GeneralUtility::trimExplode(',', $this->validationSettingsString, 1);
		$validationSettings = str_replace('|', ',', $validationSettings);

		foreach ($validationSettings as $validationSetting) {

			switch ($validationSetting) {

				case 'required':
					if (!$this->validateRequired($this->getValue())) {
						$this->addMessage('validationErrorRequired');
						$this->isValid = FALSE;
					}
					break;

				case 'email':
					if (!$this->validateEmail($this->getValue())) {
						$this->addMessage('validationErrorEmail');
						$this->isValid = FALSE;
					}
					break;

				case stristr($validationSetting, 'min('):
					if (!$this->validateMin($this->getValue(), Div::getValuesInBrackets($validationSetting))) {
						$this->addMessage('validationErrorMin');
						$this->isValid = FALSE;
					}
					break;

				case stristr($validationSetting, 'max('):
					if (!$this->validateMax($this->getValue(), Div::getValuesInBrackets($validationSetting))) {
						$this->addMessage('validationErrorMax');
						$this->isValid = FALSE;
					}
					break;

				case 'intOnly':
					if (!$this->validateInt($this->getValue())) {
						$this->addMessage('validationErrorInt');
						$this->isValid = FALSE;
					}
					break;

				case 'lettersOnly':
					if (!$this->validateLetters($this->getValue())) {
						$this->addMessage('validationErrorLetters');
						$this->isValid = FALSE;
					}
					break;

				case 'uniqueInPage':
					if (!$this->validateUniquePage($this->getValue(), $this->getFieldName())) {
						$this->addMessage('validationErrorUniquePage');
						$this->isValid = FALSE;
					}
					break;

				case 'uniqueInDb':
					if (!$this->validateUniqueDb($this->getValue(), $this->getFieldName())) {
						$this->addMessage('validationErrorUniqueDb');
						$this->isValid = FALSE;
					}
					break;

				case stristr($validationSetting, 'mustInclude('):
					if (!$this->validateMustInclude($this->getValue(), Div::getValuesInBrackets($validationSetting))) {
						$this->addMessage('validationErrorMustInclude');
						$this->isValid = FALSE;
					}
					break;

				case stristr($validationSetting, 'inList('):
					if (!$this->validateInList($this->getValue(), Div::getValuesInBrackets($validationSetting))) {
						$this->addMessage('validationErrorInList');
						$this->isValid = FALSE;
					}
					break;

				default:
					// e.g. search for method validateCustom()
					print_r(get_class_methods($this));
					if (method_exists($this, 'validate' . ucfirst(Div::getValuesBeforeBrackets($validationSetting)))) {
						if (!$this->{'validate' . ucfirst(Div::getValuesBeforeBrackets($validationSetting))}($this->getValue(), Div::getValuesInBrackets($validationSetting))) {
							$this->addMessage('validationError' . ucfirst($validationSetting));
							$this->isValid = FALSE;
						}
					}
					break;
			}
		}

		return $this->isValid;
	}

	/**
	 * Set validation
	 *
	 * @param \string $validation
	 * @return void
	 */
	public function setValidationSettingsString($validationSettingsString) {
		$this->validationSettingsString = $validationSettingsString;
	}

	/**
	 * Get validation
	 *
	 * @return string
	 */
	public function getValidationSettingsString() {
		return $this->validationSettingsString;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Add a message to the errormessage array
	 *
	 * @param \string $message
	 */
	public function addMessage($message) {
		$this->messages = array_merge($this->messages, array($message));
	}

	/**
	 * @param array $messages
	 */
	public function setMessages($messages) {
		$this->messages = $messages;
	}

	/**
	 * @return array
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @param string $field
	 */
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
	}

	/**
	 * @return string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}
}
?>