<?php
declare(strict_types = 1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ValidationSettingsService
 */
class ValidationSettingsService
{

    /**
     * Needed for validation settings. Should be "new", "edit" or "invitation"
     *
     * @var string
     */
    protected $controllerName = '';

    /**
     * Needed for validation settings. Should be "validation" or "validationEdit"
     *
     * @var string
     */
    protected $validationName = '';

    /**
     * Validation names with simple configuration
     *
     * @var array
     */
    protected $simpleValidations = [
        'date',
        'email',
        'intOnly',
        'lettersOnly',
        'unicodeLettersOnly',
        'required',
        'uniqueInDb',
        'uniqueInPage'
    ];

    /**
     * @param string $controllerName
     * @param string $validationName
     */
    public function __construct(string $controllerName, string $validationName)
    {
        $this->controllerName = $controllerName;
        $this->validationName = $validationName;
    }

    /**
     * Get validation string like
     *        required, email, min(10), max(10), intOnly,
     *        lettersOnly, unicodeLettersOnly, uniqueInPage, uniqueInDb, date,
     *        mustInclude(number|letter|special), inList(1|2|3)
     *
     * @param string $fieldName Fieldname
     * @return string
     */
    public function getValidationStringForField(string $fieldName): string
    {
        $string = '';
        $validationSettings = $this->getSettings()[$this->controllerName][$this->validationName][$fieldName];
        if (is_array($validationSettings)) {
            foreach ($validationSettings as $validation => $configuration) {
                if (!empty($string)) {
                    $string .= ',';
                }
                $string .= $this->getSingleValidationString($validation, $configuration);
            }
        }
        return $string;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isValidationEnabled(string $type = 'client'): bool
    {
        return $this->getSettings()[$this->controllerName]['validation']['_enable'][$type] === '1';
    }

    /**
     * @param string $validation
     * @param string $configuration
     * @return string
     */
    protected function getSingleValidationString($validation, $configuration)
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
     *
     * @param string $validation
     * @return bool
     */
    protected function isSimpleValidation($validation): bool
    {
        if (in_array($validation, $this->simpleValidations)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    protected function getSettings(): array
    {
        $configurationManager = ObjectUtility::getObjectManager()->get(ConfigurationManagerInterface::class);
        return (array)$configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'femanager',
            'femanager_pi1'
        );
    }
}
