<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Utility\ConfigurationUtility;

class ValidationSettingsService
{
    /**
     * Validation names with simple configuration
     */
    protected array $simpleValidations = [
        'date',
        'email',
        'intOnly',
        'lettersOnly',
        'unicodeLettersOnly',
        'required',
        'uniqueInDb',
        'uniqueInPage',
    ];

    public function __construct(
        /**
         * Needed for validation settings. Should be "new", "edit" or "invitation"
         */
        protected string $controllerName,
        /**
         * Needed for validation settings. Should be "validation" or "validationEdit"
         */
        protected string $validationName
    ) {
    }

    /**
     * Get validation string like
     *        required, email, min(10), max(10), intOnly,
     *        lettersOnly, unicodeLettersOnly, uniqueInPage, uniqueInDb, date,
     *        mustInclude(number|letter|special), inList(1|2|3)
     *
     * @param string $fieldName Fieldname
     */
    public function getValidationStringForField(string $fieldName): string
    {
        $string = '';
        $validationSettings = $this->getValidationSettings()[$fieldName] ?? [];

        if (is_array($validationSettings)) {
            foreach ($validationSettings as $validation => $configuration) {
                if ($string !== '') {
                    $string .= ',';
                }

                $string .= $this->getSingleValidationString($validation, $configuration);
            }
        }

        return $string;
    }

    public function isClientValidationEnabled(): bool
    {
        $validationSetting = ConfigurationUtility::getConfiguration()[$this->controllerName]['validation']['_enable']['client'] ?? '0';
        return $validationSetting === '1';
    }

    public function isServersideValidationEnabled(): bool
    {
        $validationSetting = ConfigurationUtility::getConfiguration()[$this->controllerName]['validation']['_enable']['server'] ?? '0';
        return $validationSetting === '1';
    }

    /**
     * @param string $validation
     * @param string $configuration
     * @return string
     */
    protected function getSingleValidationString(string $validation, string $configuration): string
    {
        $string = '';
        if ($this->isSimpleValidation($validation) && $configuration === '1') {
            $string = $validation;
        }

        if (!$this->isSimpleValidation($validation)) {
            $string = $validation;
            $string .= '(' . str_replace(',', '|', $configuration) . ')';
        }

        return $string;
    }

    /**
     * Check if validation is simple or extended
     */
    protected function isSimpleValidation(string $validation): bool
    {
        return in_array($validation, $this->simpleValidations);
    }

    public function getValidationSettings(): array
    {
        return ConfigurationUtility::getConfiguration()[$this->controllerName][$this->validationName];
    }
}
