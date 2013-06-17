<?php
namespace In2\Femanager\Domain\Validator;

class MainValidator extends \In2\Femanager\Domain\Validator\GeneralValidator {

	/**
	 * Validation of given Params
	 *
	 * @param $user
	 * @return bool
	 */
	public function isValid($user) {
		$this->init();

		if ($this->validationSettings['_enable']['server'] != 1) {
			return $this->isValid;
		}

		foreach ($this->validationSettings as $field => $validations) {
			if (is_object($user) && method_exists($user, 'get' . ucfirst($field))) {

				$value = $user->{'get' . ucfirst($field)}();
				if (is_object($value)) {
					if (method_exists($value, 'getUid')) {
						$value = $value->getUid();
					}
					if (method_exists($value, 'getFirst')) {
						$value = $value->getFirst()->getUid();
					}
					if (method_exists($value, 'current')) {
						$current = $value->current();
						if (method_exists($current, 'getUid')) {
							$value = $current->getUid();
						}
					}
				}

				foreach ($validations as $validation => $validationSetting) {
					switch ($validation) {

						case 'required':
							if (!$this->validateRequired($value)) {
								$this->addError('validationErrorRequired', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'email':
							if (!$this->validateEmail($value)) {
								$this->addError('validationErrorEmail', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'min':
							if (!$this->validateMin($value, $validationSetting)) {
								$this->addError('validationErrorMin', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'max':
							if (!$this->validateMax($value, $validationSetting)) {
								$this->addError('validationErrorMax', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'intOnly':
							if (!$this->validateInt($value)) {
								$this->addError('validationErrorInt', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'lettersOnly':
							if (!$this->validateLetters($value)) {
								$this->addError('validationErrorLetters', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'uniqueInPage':
							if (!$this->validateUniquePage($value, $field)) {
								$this->addError('validationErrorUniquePage', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'uniqueInDb':
							if (!$this->validateUniqueDb($value, $field)) {
								$this->addError('validationErrorUniqueDb', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'mustInclude':
							if (!$this->validateMustInclude($value, $validationSetting)) {
								$this->addError('validationErrorMustInclude', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'inList':
							if (!$this->validateInList($value, $validationSetting)) {
								$this->addError('validationErrorInList', $field);
								$this->isValid = FALSE;
							}
							break;
					}
				}
			}
		}

		return $this->isValid;
	}
}
?>