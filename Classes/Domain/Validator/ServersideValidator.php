<?php
namespace In2\Femanager\Domain\Validator;

/**
 * Class ServersideValidator
 */
class ServersideValidator extends \In2\Femanager\Domain\Validator\AbstractValidator {

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
							if ($validationSetting == 1 && !$this->validateRequired($value)) {
								$this->addError('validationErrorRequired', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'email':
							if (!empty($value) && $validationSetting == 1 && !$this->validateEmail($value)) {
								$this->addError('validationErrorEmail', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'min':
							if (!empty($value) && !$this->validateMin($value, $validationSetting)) {
								$this->addError('validationErrorMin', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'max':
							if (!empty($value) && !$this->validateMax($value, $validationSetting)) {
								$this->addError('validationErrorMax', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'intOnly':
							if (!empty($value) && $validationSetting == 1 && !$this->validateInt($value)) {
								$this->addError('validationErrorInt', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'lettersOnly':
							if (!empty($value) && $validationSetting == 1 && !$this->validateLetters($value)) {
								$this->addError('validationErrorLetters', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'uniqueInPage':
							if (!empty($value) && $validationSetting == 1 && !$this->validateUniquePage($value, $field, $user)) {
								$this->addError('validationErrorUniquePage', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'uniqueInDb':
							if (!empty($value) && $validationSetting == 1 && !$this->validateUniqueDb($value, $field, $user)) {
								$this->addError('validationErrorUniqueDb', $field);
								$this->isValid = FALSE;
							}
							break;

						case 'mustInclude':
							if (!empty($value) && !$this->validateMustInclude($value, $validationSetting)) {
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

						case 'sameAs':
							if (method_exists($user, 'get' . ucfirst($validationSetting))) {
								$valueToCompare = $user->{'get' . ucfirst($validationSetting)}();
								if (!$this->validateSameAs($value, $valueToCompare)) {
									$this->addError('validationErrorSameAs', $field);
									$this->isValid = FALSE;
								}
							}
							break;

						case 'date':
							// Nothing to do. ServersideValidator runs after converter
							// If dateTimeConverter exception $value is the old DateTime Object => True
							// If dateTimeConverter runs well we have an DateTime Object => True
							break;

						default:
							// e.g. search for method validateCustom()
							if (method_exists($this, 'validate' . ucfirst($validation))) {
								if (!$this->{'validate' . ucfirst($validation)}($value, $validationSetting)) {
									$this->addError('validationError' . ucfirst($validation), $field);
									$this->isValid = FALSE;
								}
							}
					}
				}
			}
		}

		return $this->isValid;
	}
}