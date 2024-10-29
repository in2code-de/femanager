<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\PluginRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\PluginService;
use In2code\Femanager\Event\UniqueUserEvent;
use In2code\Femanager\Utility\FrontendUtility;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;

/**
 * Class GeneralValidator
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractValidator extends AbstractValidatorExtbase
{
    protected ?UserRepository $userRepository = null;

    public ?ConfigurationManagerInterface $configurationManager = null;

    protected ?EventDispatcherInterface $eventDispatcher = null;

    protected ?PluginService $pluginService = null;

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

    public function injectUserRepository(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function injectConfigurationManagerInterface(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectPluginService(PluginService $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    public function injectEventDispatcherInterface(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function initializeObject(): void
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        }
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        }
        if ($this->userRepository === null) {
            $this->userRepository = GeneralUtility::makeInstance(UserRepository::class);
        }
        if ($this->pluginService === null) {
            $this->pluginService = GeneralUtility::makeInstance(PluginService::class);
        }
    }

    /**
     * Validation for required
     */
    protected function validateRequired(mixed $value): bool
    {
        if (!is_object($value)) {
            if (is_numeric($value)) {
                return true;
            }
            return !empty($value);
        }
        if ((is_countable($value)) && count($value) > 0) {
            return true;
        }
        if ($value instanceof \DateTime) {
            return true;
        }
        return false;
    }

    /**
     * Validation for required
     */
    protected function validateFileRequired(mixed $value, string $fieldName): bool
    {
        if (empty($value)) {
            $request = $GLOBALS['TYPO3_REQUEST'];
            $uploadedFiles = $request->getUploadedFiles();

            if (isset($uploadedFiles[$this->pluginService->getFemanagerPluginNameFromRequest()][$fieldName])) {
                return true;
            }
        }
        else {
            return $this->validateRequired($value);
        }
        return false;
    }

    /**
     * Validation for email
     *
     * @param string $value
     * @return bool
     */
    protected function validateEmail($value): bool
    {
        return GeneralUtility::validEmail($value);
    }

    /**
     * Validation for Minimum of characters
     *
     * @param string $value
     * @param string $validationSetting
     */
    protected function validateMin($value, $validationSetting): bool
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
     */
    protected function validateMax($value, $validationSetting): bool
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
     */
    protected function validateInt($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validation for Letters (a-zA-Z), hyphen and underscore
     *
     * @param string $value
     */
    protected function validateLetters($value): bool
    {
        if (preg_replace('/[^a-zA-Z_-]/', '', $value) === $value) {
            return true;
        }
        return false;
    }

    /**
     * Validation for all Unicode letters, hyphen and underscore
     *
     * @param string $value
     */
    protected function validateUnicodeLetters($value): bool
    {
        return (bool)preg_match('/^[\pL_-]+$/u', $value);
    }

    /**
     * Validation for Unique in sysfolder
     */
    protected function validateUniquePage(string $value, string $field, User $user = null): bool
    {
        $foundUser = $this->userRepository->checkUniquePage($field, $value, $user);
        return !is_object($foundUser);
    }

    /**
     * Validation for Unique in the db
     */
    protected function validateUniqueDb(string $value, string $field, User $user = null): bool
    {
        $foundUser = $this->userRepository->checkUniqueDb($field, $value, $user);

        $uniqueDb = !is_object($foundUser);

        //  return false, if is not unique
        return $this->eventDispatcher->dispatch(new UniqueUserEvent($value, $field, $user, $uniqueDb))->isUnique();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * PHPMD.BooleanArgumentFlag issue has come ob because Copy&Paste Detector issue
     * Commit: 55fd29fb082ba13ba5c1234240d7e74e78ee9dd4
     */
    protected function validateString(string $value, string $validationSettingList, bool $mustInclude = true): bool
    {
        $isValid = true;
        $validationSettings = GeneralUtility::trimExplode(',', $validationSettingList, true);
        foreach ($validationSettings as $validationSetting) {
            switch ($validationSetting) {
                case 'number':
                    $containsNumber = $this->stringContainsNumber($value);
                    if ($mustInclude && !$containsNumber || !$mustInclude && $containsNumber) {
                        $isValid = false;
                    }
                    break;
                case 'letter':
                    $containsLetter = $this->stringContainsLetter($value);
                    if ($mustInclude && !$containsLetter || !$mustInclude && $containsLetter) {
                        $isValid = false;
                    }
                    break;
                case 'uppercase':
                    $containsUppercase = $this->stringContainsUppercase($value);
                    if ($mustInclude && !$containsUppercase || !$mustInclude && $containsUppercase) {
                        $isValid = false;
                    }
                    break;
                case 'special':
                    $containsSpecialCharacter = $this->stringContainsSpecialCharacter($value);
                    if ($mustInclude && !$containsSpecialCharacter || !$mustInclude && $containsSpecialCharacter) {
                        $isValid = false;
                    }
                    break;
                case 'space':
                    $containsSpaceCharacter = $this->stringContainsSpaceCharacter($value);
                    if ($mustInclude && !$containsSpaceCharacter || !$mustInclude && $containsSpaceCharacter) {
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
        return strlen(preg_replace('/[^0-9]/', '', $value)) > 0;
    }

    /**
     * String contains letter?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsLetter($value)
    {
        return strlen(preg_replace('/[^a-zA-Z_-]/', '', $value)) > 0;
    }

    /**
     * String contains uppercase letter?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsUppercase($value)
    {
        return strlen(preg_replace('/[^A-Z]/', '', $value)) > 0;
    }

    /**
     * String contains special character?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsSpecialCharacter($value)
    {
        return strlen(preg_replace('/[^a-zA-Z0-9]/', '', $value)) !== strlen($value);
    }

    /**
     * String contains space character?
     *
     * @param string $value
     * @return bool
     */
    protected function stringContainsSpaceCharacter($value)
    {
        return str_contains($value, ' ');
    }

    /**
     * Validation for checking if values are in a given list
     */
    protected function validateInList(mixed $value, mixed $validationSettingList): bool
    {
        $valueList = GeneralUtility::trimExplode(',', (string)$value, true);
        $validationSettings = GeneralUtility::trimExplode(',', (string)$validationSettingList, true);
        $diff = array_diff($valueList, $validationSettings);

        return empty($diff);
    }

    /**
     * Validation for comparing two fields
     */
    protected function validateSameAs(mixed $value, mixed $value2): bool
    {
        if ($value === $value2) {
            return true;
        }
        return false;
    }

    /**
     * Validation for checking if values is in date format
     */
    protected function validateDate(string $value, string $validationSetting): bool
    {
        $dateParts = [];
        switch ($validationSetting) {
            case 'd.m.Y':
                if (preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $value, $dateParts)) {
                    if (checkdate((int)$dateParts[2], (int)$dateParts[1], (int)$dateParts[3])) {
                        return true;
                    }
                }
                break;

            case 'm/d/Y':
                if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', $value, $dateParts)) {
                    if (checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[3])) {
                        return true;
                    }
                }
                break;

            default:
        }
        return false;
    }

    protected function init()
    {
        $this->initializeObject();
        $this->setPluginVariables();
        $this->setValidationSettings();
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setPluginVariables()
    {
        $allParams = [];
        // Get the current request object
        $request = $GLOBALS['TYPO3_REQUEST'];
        if (ApplicationType::fromRequest($request)->isFrontend()) {
            $queryParams = $request->getQueryParams(); // GET parameters
            $parsedBody = $request->getParsedBody(); // POST parameters
            $allParams = array_merge($queryParams, $parsedBody);
        }
        // collect variables from plugins starting with tx_femanager
        $this->pluginVariables = [];
        foreach ($allParams as $key => $value) {
            if (str_starts_with($key, 'tx_femanager')) {
                $this->pluginVariables[$key] = $value;
            }
        }
    }

    protected function setValidationSettings()
    {
        $pluginName = $this->pluginService->getFemanagerPluginNameFromRequest();
        if ($pluginName !== '') {
            $config = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Femanager',
                null
            );
            $controllerName = $this->getControllerName();
            $validationName = $this->getValidationName();
            $this->validationSettings = $config[$controllerName][$validationName];
        }
    }

    /**
     * @return string
     */
    protected function getValidationName(): string
    {
        $validationName = 'validation';
        if ($this->getControllerName() === 'invitation' && $this->getActionName() === 'edit') {
            $validationName = 'validationEdit';
        }
        return $validationName;
    }

    /**
     * @return string
     */
    protected function getActionName(): string
    {
        $pluginName = $this->pluginService->getFemanagerPluginNameFromRequest();
        return $this->pluginVariables[$pluginName]['__referrer']['@action'] ?? '';
    }

    /**
     * Get controller name in lowercase
     *
     * @return string
     */
    protected function getControllerName(): string
    {
        // Security check: see Commit ae6c8d0b390a96d11fe7e3a524b0ace2cb23b7da
        // only allow controller names 'new', 'edit' and 'invitation
        $controllerName = 'new';
        $pluginName = $this->pluginService->getFemanagerPluginNameFromRequest();
        $controllerNameInPlugin = $this->pluginVariables[$pluginName]['__referrer']['@controller'] ?? '';
        if ($controllerNameInPlugin === 'Edit') {
            $controllerName = 'edit';
        } elseif ($controllerNameInPlugin === 'Invitation') {
            $controllerName = 'invitation';
        }
        $this->checkAllowedPluginName($pluginName);
        return $controllerName;
    }

    protected function getControllerNameByPlugin(string $plugin): string
    {
        // Security check: see Commit ae6c8d0b390a96d11fe7e3a524b0ace2cb23b7da
        // only allow controller names 'new', 'edit' and 'invitation
        if ($plugin === 'tx_femanager_edit') {
            return 'edit';
        }
        if ($plugin === 'tx_femanager_invitation') {
            return 'invitation';
        }
        return 'new';
    }

    protected function checkAllowedPluginName(string $pluginName)
    {
        $pluginRepository = GeneralUtility::makeInstance(PluginRepository::class);
        $pageIdentifier = FrontendUtility::getCurrentPid();
        if ($pluginRepository->isPluginWithViewOnGivenPage($pageIdentifier, $pluginName) === false) {
            throw new LogicException('PluginName is not allowed', 1683551467);
        }
    }
}
