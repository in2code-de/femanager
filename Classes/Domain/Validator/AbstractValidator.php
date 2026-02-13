<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Event\UniqueUserEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractValidator extends AbstractValidatorExtbase
{
    final public const ALLOWED_PLUGIN_NAMESPACES = [
        'tx_femanager_registration',
        'tx_femanager_edit',
        'tx_femanager_list',
        'tx_femanager_detail',
        'tx_femanager_invitation',
        'tx_femanager_resendConfirmationMail',
    ];

    protected ?ContentObjectRenderer $currentContentObject = null;
    public array $pluginVariables = [];
    protected bool $isValid = true;
    protected string $referrerActionName = '';
    protected string $pluginName = '';
    /**
     * the extbase plugin namespace (with tx_ prefix) e.g. tx_femanager_registration
     */
    protected string $pluginNamespace = '';
    protected array $typoScriptConfiguration = [];

    public function __construct(
        protected readonly UserRepository $userRepository,
        public readonly ConfigurationManagerInterface $configurationManager,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function init(): void
    {
        $extbaseRequestParameter = $this->request->getAttribute('extbase');
        $referrerArguments = $extbaseRequestParameter?->getInternalArgument('__referrer') ?? null;
        $this->typoScriptConfiguration = ConfigurationUtility::getConfiguration();

        $this->currentContentObject = $this->request->getAttribute('currentContentObject') ?? null;
        $this->pluginNamespace = 'tx_' . $extbaseRequestParameter->getControllerExtensionKey() . '_' . strtolower($extbaseRequestParameter->getPluginName());
        $this->pluginVariables = ($extbaseRequestParameter?->getArguments() ?? false) ? $extbaseRequestParameter?->getArguments() : [];
        $this->referrerActionName = $referrerArguments['@action'] ?? '';
    }

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

        return $value instanceof \DateTime;
    }

    protected function validateFileRequired(mixed $value, string $fieldName): bool
    {
        if (empty($value)) {
            return $this->validateRequired($value);
        }

        if (array_key_exists($fieldName, $this->request->getUploadedFiles())) {
            return true;
        }

        return false;
    }

    protected function validateEmail(string $value): bool
    {
        return GeneralUtility::validEmail($value);
    }

    /**
     * Validation for Minimum of characters
     */
    protected function validateMin(string $value, string $validationSetting): bool
    {
        return mb_strlen($value) >= $validationSetting;
    }

    /**
     * Validation for Maximum of characters
     */
    protected function validateMax(string $value, string $validationSetting): bool
    {
        return mb_strlen($value) <= $validationSetting;
    }

    /**
     * Validation for Minimum of characters
     */
    protected function validateMinInt(int $value, string $validationSetting): bool
    {
        return $value >= $validationSetting;
    }

    /**
     * Validation for Maximum of characters
     */
    protected function validateMaxInt(int $value, string $validationSetting): bool
    {
        return $value <= $validationSetting;
    }

    /**
     * Validation for Numbers only
     */
    protected function validateInt(string $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validation for Letters (a-zA-Z), hyphen and underscore
     */
    protected function validateLetters(string $value): bool
    {
        return preg_replace('/[^a-zA-Z_-]/', '', $value) === $value;
    }

    /**
     * Validation for all Unicode letters, hyphen and underscore
     */
    protected function validateUnicodeLetters(string $value): bool
    {
        return (bool)preg_match('/^[\pL_-]+$/u', $value);
    }

    /**
     * Validation for Unique in sysfolder
     */
    protected function validateUniquePage(string $value, string $field, ?User $user = null): bool
    {
        $foundUser = $this->userRepository->checkUniquePage($field, $value, $user);
        return !is_object($foundUser);
    }

    /**
     * Validation for Unique in the db
     */
    protected function validateUniqueDb(string $value, string $field, ?User $user = null): bool
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
     */
    protected function stringContainsNumber(string $value): bool
    {
        return strlen((string)preg_replace('/[^0-9]/', '', $value)) > 0;
    }

    /**
     * String contains letter?
     */
    protected function stringContainsLetter(string $value): bool
    {
        return strlen((string)preg_replace('/[^a-zA-Z_-]/', '', $value)) > 0;
    }

    /**
     * String contains uppercase letter?
     */
    protected function stringContainsUppercase(string $value): bool
    {
        return strlen((string)preg_replace('/[^A-Z]/', '', $value)) > 0;
    }

    /**
     * String contains special character?
     */
    protected function stringContainsSpecialCharacter(string $value): bool
    {
        return strlen((string)preg_replace('/[^a-zA-Z0-9]/', '', $value)) !== strlen($value);
    }

    /**
     * String contains space character?
     */
    protected function stringContainsSpaceCharacter(string $value): bool
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

        return $diff === [];
    }

    /**
     * Validation for comparing two fields
     */
    protected function validateSameAs(mixed $value, mixed $value2): bool
    {
        return $value === $value2;
    }

    /**
     * Validation for checking if values is in date format
     */
    protected function validateDate(string $value, string $validationSetting): bool
    {
        $dateParts = [];
        switch ($validationSetting) {
            case 'd.m.Y':
                if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $dateParts) && checkdate(
                    (int)$dateParts[2],
                    (int)$dateParts[1],
                    (int)$dateParts[3]
                )) {
                    return true;
                }

                break;

            case 'm/d/Y':
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $dateParts) && checkdate(
                    (int)$dateParts[1],
                    (int)$dateParts[2],
                    (int)$dateParts[3]
                )) {
                    return true;
                }

                break;

            default:
        }

        return false;
    }

    protected function getValidationName(): string
    {
        if ($this->getControllerName() === 'invitation' && $this->referrerActionName === 'edit') {
            return 'validationEdit';
        }

        return 'validation';
    }

    protected function getControllerName(): string
    {
        if (!in_array($this->pluginNamespace, self::ALLOWED_PLUGIN_NAMESPACES)) {
            throw new LogicException('Plugin namespace is not allowed', 1683551467);
        }

        // Security check: see Commit ae6c8d0b390a96d11fe7e3a524b0ace2cb23b7da
        // only allow controller names 'new', 'edit' and 'invitation
        if ($this->pluginNamespace === 'tx_femanager_edit') {
            return 'edit';
        }

        if ($this->pluginNamespace === 'tx_femanager_invitation') {
            return 'invitation';
        }

        return 'new';
    }

    protected function getReferrerActionName(): string
    {
        return $this->referrerActionName;
    }

    public function setReferrerActionName(string $referrerActionName): static
    {
        $this->referrerActionName = $referrerActionName;

        return $this;
    }

    public function getPluginName(): string
    {
        return $this->pluginNamespace;
    }

    public function setPluginNamespace(string $pluginNamespace): static
    {
        $this->pluginNamespace = $pluginNamespace;

        return $this;
    }
}
