<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;

/**
 * Class GeneralValidator
 */
abstract class AbstractValidator extends AbstractValidatorExtbase
{

    /**
     * userRepository
     *
     * @var \In2code\Femanager\Domain\Repository\UserRepository
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
    public $validationSettings = [];

    /**
     * Is Valid
     */
    protected $isValid = true;

    /**
     * Validation for required
     *
     * @param string $value
     * @return \bool
     */
    protected function validateRequired($value)
    {
        if (!is_object($value)) {
            return !empty($value);
        } elseif (count($value)) {
            return true;
        }
        return false;
    }

    /**
     * Validation for email
     *
     * @param string $value
     * @return \bool
     */
    protected function validateEmail($value)
    {
        return GeneralUtility::validEmail($value);
    }

    /**
     * Validation for Minimum of characters
     *
     * @param string $value
     * @param string $validationSetting
     * @return \bool
     */
    protected function validateMin($value, $validationSetting)
    {
        if (mb_strlen($value) < $validationSetting) {
            return false;
        }
        return true;
    }

    /**
     * Validation for Maximum of characters
     *
     * @param string $value
     * @param string $validationSetting
     * @return \bool
     */
    protected function validateMax($value, $validationSetting)
    {
        if (mb_strlen($value) > $validationSetting) {
            return false;
        }
        return true;
    }

    /**
     * Validation for Numbers only
     *
     * @param string $value
     * @return \bool
     */
    protected function validateInt($value)
    {
        return is_numeric($value);
    }

    /**
     * Validation for Letters only
     *
     * @param string $value
     * @return \bool
     */
    protected function validateLetters($value)
    {
        if (preg_replace('/[^a-zA-Z_-]/', '', $value) === $value) {
            return true;
        }
        return false;
    }

    /**
     * Validation for Unique in sysfolder
     *
     * @param string $value
     * @param string $field
     * @param User $user Existing User
     * @return \bool
     */
    protected function validateUniquePage($value, $field, User $user = null)
    {
        $foundUser = $this->userRepository->checkUniquePage($field, $value, $user);
        return !is_object($foundUser);
    }

    /**
     * Validation for Unique in the db
     *
     * @param string $value
     * @param string $field Fieldname like "username" or "email"
     * @param User $user Existing User
     * @return \bool
     */
    protected function validateUniqueDb($value, $field, User $user = null)
    {
        $foundUser = $this->userRepository->checkUniqueDb($field, $value, $user);
        return !is_object($foundUser);
    }

    /**
     * Validation for "Must include some characters"
     *
     * @param string $value
     * @param string $validationSettingList
     * @return \bool
     */
    protected function validateMustInclude($value, $validationSettingList)
    {
        $isValid = true;
        $validationSettings = GeneralUtility::trimExplode(',', $validationSettingList, true);
        foreach ($validationSettings as $validationSetting) {
            switch ($validationSetting) {
                case 'number':
                    if (!$this->stringContainsNumber($value)) {
                        $isValid = false;
                    }
                    break;
                case 'letter':
                    if (!$this->stringContainsLetter($value)) {
                        $isValid = false;
                    }
                    break;
                case 'special':
                    if (!$this->stringContainsSpecialCharacter($value)) {
                        $isValid = false;
                    }
                    break;
                case 'space':
                    if (!$this->stringContainsSpaceCharacter($value)) {
                        $isValid = false;
                    }
                    break;
                default:
            }
        }
        return $isValid;
    }

    /**
     * Validation for "Must not include some characters"
     *
     * @param string $value
     * @param string $validationSettingList
     * @return \bool
     */
    protected function validateMustNotInclude($value, $validationSettingList)
    {
        $isValid = true;
        $validationSettings = GeneralUtility::trimExplode(',', $validationSettingList, true);
        foreach ($validationSettings as $validationSetting) {
            switch ($validationSetting) {
                case 'number':
                    if ($this->stringContainsNumber($value)) {
                        $isValid = false;
                    }
                    break;
                case 'letter':
                    if ($this->stringContainsLetter($value)) {
                        $isValid = false;
                    }
                    break;
                case 'special':
                    if ($this->stringContainsSpecialCharacter($value)) {
                        $isValid = false;
                    }
                    break;
                case 'space':
                    if ($this->stringContainsSpaceCharacter($value)) {
                        $isValid = false;
                    }
                    break;
                default:
            }
        }
        return $isValid;
    }

    /**
     * String contains number?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsNumber($value)
    {
        return (strlen(preg_replace('/[^0-9]/', '', $value)) > 0);
    }

    /**
     * String contains letter?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsLetter($value)
    {
        return (strlen(preg_replace('/[^a-zA-Z_-]/', '', $value)) > 0);
    }

    /**
     * String contains special character?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsSpecialCharacter($value)
    {
        return (strlen(preg_replace('/[^a-zA-Z0-9]/', '', $value)) !== strlen($value));
    }

    /**
     * String contains space character?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsSpaceCharacter($value)
    {
        return (strpos($value, ' ') !== false);
    }

    /**
     * Validation for checking if values are in a given list
     *
     * @param string $value
     * @param string $validationSettingList
     * @return \bool
     */
    protected function validateInList($value, $validationSettingList)
    {
        $validationSettings = GeneralUtility::trimExplode(',', $validationSettingList, true);
        return in_array($value, $validationSettings);
    }

    /**
     * Validation for comparing two fields
     *
     * @param string $value
     * @param string $value2
     * @return \bool
     */
    protected function validateSameAs($value, $value2)
    {
        if ($value === $value2) {
            return true;
        }
        return false;
    }

    /**
     * Validation for checking if values is in date format
     *
     * @param string $value
     * @param string $validationSetting
     * @return \bool
     */
    protected function validateDate($value, $validationSetting)
    {
        $dateParts = [];
        switch ($validationSetting) {
            case 'd.m.Y':
                if (preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $value, $dateParts)) {
                    if (checkdate($dateParts[2], $dateParts[1], $dateParts[3])) {
                        return true;
                    }
                }
                break;

            case 'm/d/Y':
                if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', $value, $dateParts)) {
                    if (checkdate($dateParts[1], $dateParts[2], $dateParts[3])) {
                        return true;
                    }
                }
                break;

            default:
        }
        return false;
    }

    /**
     * Initialize Method
     *
     * @return void
     */
    protected function init()
    {
        $config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            'Pi1'
        );
        $this->pluginVariables = GeneralUtility::_GP('tx_femanager_pi1');
        $controllerName = 'new';
        $validationName = 'validation';
        if ($this->pluginVariables['__referrer']['@controller'] !== 'New') {
            $controllerName = strtolower($this->pluginVariables['__referrer']['@controller']);
            if ($controllerName === 'invitation' && $this->pluginVariables['__referrer']['@action'] === 'edit') {
                $validationName = 'validationEdit';
            }
        }
        $this->validationSettings = $config[$controllerName][$validationName];
    }
}
