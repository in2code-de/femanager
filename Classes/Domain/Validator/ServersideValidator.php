<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ServersideValidator
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ServersideValidator extends AbstractValidator
{
    /**
     * Validation of given Params
     *
     * @param User $user
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function isValid($user): void
    {
        $this->init();
        if ($this->validationSettings['_enable']['server'] === '1') {
            foreach ($this->validationSettings as $fieldName => $validations) {
                if ($this->shouldBeValidated($user, $fieldName, $validations)) {
                    $value = $this->getValue($user, $fieldName);

                    foreach ($validations as $validation => $validationSetting) {
                        switch ($validation) {
                            case 'required':
                                $this->checkRequiredValidation($validationSetting, $value, $fieldName);
                                break;

                            case 'fileRequired':
                                $this->checkFileRequiredValidation($validationSetting, $value, $fieldName);
                                break;

                            case 'email':
                                $this->checkEmailValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'min':
                                $this->checkMinValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'max':
                                $this->checkMaxValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'intOnly':
                                $this->checkIntOnlyValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'lettersOnly':
                                $this->checkLetterOnlyValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'unicodeLettersOnly':
                                $this->checkUnicodeLetterOnlyValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'uniqueInPage':
                                $this->checkUniqueInPageValidation($user, $value, $validationSetting, $fieldName);
                                break;

                            case 'uniqueInDb':
                                $this->checkUniqueInDbValidation($user, $value, $validationSetting, $fieldName);
                                break;

                            case 'mustInclude':
                                $this->checkMustIncludeValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'mustNotInclude':
                                $this->checkMustNotIncludeValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'inList':
                                $this->checkInListValidation($value, $validationSetting, $fieldName);
                                break;

                            case 'sameAs':
                                $this->checkSameAsValidation($user, $validationSetting, $value, $fieldName);
                                break;

                            case 'date':
                                // Nothing to do. ServersideValidator runs after converter
                                // If dateTimeConverter exception $value is the old DateTime Object => True
                                // If dateTimeConverter runs well we have an DateTime Object => True
                                break;

                            default:
                                // e.g. search for method validateCustom()
                                $this->checkAnyValidation($validation, $value, $validationSetting, $fieldName);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $validationSetting
     * @param $value
     * @param $fieldName
     */
    protected function checkRequiredValidation($validationSetting, $value, string|array $fieldName)
    {
        if ($validationSetting === '1' && !$this->validateRequired($value)) {
            $this->addErrorForProperty($fieldName, 'validationErrorRequired', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $validationSetting
     * @param $value
     * @param $fieldName
     */
    protected function checkFileRequiredValidation($validationSetting, $value, $fieldName)
    {
        if ($validationSetting === '1' && !$this->validateFileRequired($value, $fieldName)) {
            $this->addErrorForProperty($fieldName, 'validationErrorRequired', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkEmailValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && $validationSetting === '1' && !$this->validateEmail($value)) {
            $this->addErrorForProperty($fieldName, 'validationErrorEmail', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkMinValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && !$this->validateMin($value, $validationSetting)) {
            $this->addErrorForProperty($fieldName, 'validationErrorMin', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkMaxValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && !$this->validateMax($value, $validationSetting)) {
            $this->addErrorForProperty($fieldName, 'validationErrorMax', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkIntOnlyValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && $validationSetting === '1' && !$this->validateInt($value)) {
            $this->addErrorForProperty($fieldName, 'validationErrorInt', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkLetterOnlyValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && $validationSetting === '1' && !$this->validateLetters($value)) {
            $this->addErrorForProperty($fieldName, 'validationErrorLetters', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkUnicodeLetterOnlyValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && $validationSetting === '1' && !$this->validateUnicodeLetters($value)) {
            $this->addErrorForProperty($fieldName, 'validationErrorLetters', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $user
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkUniqueInPageValidation($user, $value, $validationSetting, $fieldName)
    {
        if (
            !empty($value) &&
            $validationSetting === '1' &&
            !$this->validateUniquePage($value, $fieldName, $user)
        ) {
            $this->addErrorForProperty($fieldName, 'validationErrorUniquePage', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $user
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkUniqueInDbValidation($user, $value, $validationSetting, $fieldName)
    {
        if (
            !empty($value) &&
            $validationSetting === '1' &&
            !$this->validateUniqueDb($value, $fieldName, $user)
        ) {
            $this->addErrorForProperty($fieldName, 'validationErrorUniqueDb', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkMustIncludeValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && !$this->validateString($value, $validationSetting, true)) {
            $this->addErrorForProperty($fieldName, 'validationErrorMustInclude', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkMustNotIncludeValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!empty($value) && !$this->validateString($value, $validationSetting, false)) {
            $this->addErrorForProperty($fieldName, 'validationErrorMustNotInclude', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkInListValidation($value, $validationSetting, string|array $fieldName)
    {
        if (!$this->validateInList($value, $validationSetting)) {
            $this->addErrorForProperty($fieldName, 'validationErrorInList', 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @param $user
     * @param $validationSetting
     * @param $value
     * @param $fieldName
     */
    protected function checkSameAsValidation($user, $validationSetting, $value, string|array $fieldName)
    {
        if (method_exists($user, 'get' . ucfirst((string)$validationSetting))) {
            $valueToCompare = $user->{'get' . ucfirst((string)$validationSetting)}();
            if (!$this->validateSameAs($value, $valueToCompare)) {
                $this->addErrorForProperty($fieldName, 'validationErrorSameAs', 0, ['field' => $fieldName]);
                $this->isValid = false;
            }
        }
    }

    /**
     * @param $validation
     * @param $value
     * @param $validationSetting
     * @param $fieldName
     */
    protected function checkAnyValidation($validation, $value, $validationSetting, string|array $fieldName)
    {
        if (method_exists($this, 'validate' . ucfirst((string)$validation)) && !$this->{'validate' . ucfirst((string)$validation)}($value, $validationSetting)) {
            $this->addErrorForProperty($fieldName, 'validationError' . ucfirst((string)$validation), 0, ['field' => $fieldName]);
            $this->isValid = false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getValue(User $user, string $fieldName): mixed
    {
        $value = $this->getValueFromProperty($user, $fieldName);

        if ($value instanceof ObjectStorage) {
            $values = [];

            foreach ($value as $object) {
                if (method_exists($object, 'getUid')) {
                    $values[] = $object->getUid();
                }

                if ($object instanceof FileReference) {
                    return true;
                }
            }

            return implode(',', $values);
        }

        if (is_object($value)) {
            if (method_exists($value, 'getUid')) {
                return $value->getUid();
            }

            if (method_exists($value, 'getFirst')) {
                return $value->getFirst()->getUid();
            }

            if (method_exists($value, 'current')) {
                $current = $value->current();

                if ($current instanceof FileReference) {
                    return '';
                }

                if ($current !== null && method_exists($current, 'getUid')) {
                    return $current->getUid();
                }
            }
        }

        return $value;
    }

    /**
     * @param $user
     * @param $fieldName
     */
    protected function shouldBeValidated($user, string $fieldName, array $validationSettings): bool
    {
        return is_object($user)
            && $this->propertyHasGetterMethod($user, $fieldName)
            && $this->evaluateConditions($user, $fieldName, $validationSettings);
    }

    protected function evaluateConditions(User $user, string $fieldName, array $validationSettings)
    {
        if (array_key_exists('if', $validationSettings) && class_exists($validationSettings['if'])) {
            $object = GeneralUtility::makeInstance($validationSettings['if']);
            if ($object instanceof ValidationConditionInterface) {
                return $object->shouldValidate($user, $fieldName, $validationSettings);
            }
        }

        return true;
    }

    /**
     * @param $user
     * @param $fieldName
     * @return mixed
     */
    protected function getValueFromProperty(object|array $user, string $fieldName)
    {
        $value = '';
        try {
            $value = ObjectAccess::getProperty($user, $fieldName);
        } catch (\Exception $exception) {
            unset($exception);
        }

        return $value;
    }

    protected function propertyHasGetterMethod(object|array $user, string $fieldName): bool
    {
        try {
            ObjectAccess::getProperty($user, $fieldName);
            $getterExists = true;
        } catch (\Exception $exception) {
            unset($exception);
            $getterExists = false;
        }

        return $getterExists;
    }
}
