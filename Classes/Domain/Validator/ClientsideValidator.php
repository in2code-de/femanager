<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\ValidationSettingsService;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use SJBR\SrFreecap\Domain\Repository\WordRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ClientsideValidator extends AbstractValidator
{
    /**
     * Validation settings string
     *        possible validations for each field are:
     *            required, email, min(12), max(13), intOnly, lettersOnly,unicodeLettersOnly
     *            uniqueInPage, uniqueInDb, date, mustInclude(number,letter,special),
     *            inList(1,2,3)
     *
     */
    protected string $validationSettingsString = '';
    protected string $value = '';
    protected string $fieldName = '';
    protected ?User $user = null;

    /**
     * Error message container
     */
    protected array $messages = [];

    /**
     * Additional Values (for comparing a value with another)
     */
    protected string $additionalValue = '';
    protected int $pluginUid = 0;
    protected string $actionName = '';

    /**
     * Validate Field
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateField(): bool
    {
        if ($this->isValidationSettingsDifferentToGlobalSettings()) {
            $this->addMessage('validationErrorGeneral');

            return false;
        }

        foreach ($this->getValidationSettings() as $validationSetting) {
            switch ($validationSetting) {
                case 'required':
                    if (!$this->validateRequired($this->getValue())) {
                        $this->addMessage('validationErrorRequired');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'fileRequired('):
                    if (!$this->validateFileRequired($this->getValue(), $this->getFieldName())) {
                        $this->addMessage('validationErrorRequired');
                        $this->isValid = false;
                    }

                    break;

                case 'email':
                    if ($this->getValue() && !$this->validateEmail($this->getValue())) {
                        $this->addMessage('validationErrorEmail');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'min('):
                    if ($this->getValue() &&
                        !$this->validateMin($this->getValue(), StringUtility::getValuesInBrackets($validationSetting))
                    ) {
                        $this->addMessage('validationErrorMin');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'max('):
                    if ($this->getValue() &&
                        !$this->validateMax($this->getValue(), StringUtility::getValuesInBrackets($validationSetting))
                    ) {
                        $this->addMessage('validationErrorMax');
                        $this->isValid = false;
                    }

                    break;

                case 'intOnly':
                    if ($this->getValue() && !$this->validateInt($this->getValue())) {
                        $this->addMessage('validationErrorInt');
                        $this->isValid = false;
                    }

                    break;

                case 'lettersOnly':
                    if ($this->getValue() && !$this->validateLetters($this->getValue())) {
                        $this->addMessage('validationErrorLetters');
                        $this->isValid = false;
                    }

                    break;

                case 'unicodeLettersOnly':
                    if ($this->getValue() && !$this->validateUnicodeLetters($this->getValue())) {
                        $this->addMessage('validationErrorLetters');
                        $this->isValid = false;
                    }

                    break;

                case 'uniqueInPage':
                    if ($this->getValue() &&
                        !$this->validateUniquePage($this->getValue(), $this->getFieldName(), $this->getUser())
                    ) {
                        $this->addMessage('validationErrorUniquePage');
                        $this->isValid = false;
                    }

                    break;

                case 'uniqueInDb':
                    if ($this->getValue() &&
                        !$this->validateUniqueDb($this->getValue(), $this->getFieldName(), $this->getUser())
                    ) {
                        $this->addMessage('validationErrorUniqueDb');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'mustInclude('):
                    if ($this->getValue() &&
                        !$this->validateString(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting),
                            true
                        )
                    ) {
                        $this->addMessage('validationErrorMustInclude');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'mustNotInclude('):
                    if ($this->getValue() &&
                        !$this->validateString(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting),
                            false
                        )
                    ) {
                        $this->addMessage('validationErrorMustNotInclude');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'inList('):
                    if (!$this->validateInList(
                        $this->getValue(),
                        StringUtility::getValuesInBrackets($validationSetting)
                    )) {
                        $this->addMessage('validationErrorInList');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'sameAs('):
                    if (!$this->validateSameAs($this->getValue(), $this->getAdditionalValue())) {
                        $this->addMessage('validationErrorSameAs');
                        $this->isValid = false;
                    }

                    break;

                case 'date':
                    if ($this->getValue() &&
                        !$this->validateDate(
                            $this->getValue(),
                            LocalizationUtility::translate('tx_femanager_domain_model_user.dateFormat')
                        )
                    ) {
                        $this->addMessage('validationErrorDate');
                        $this->isValid = false;
                    }

                    break;

                case stristr((string)$validationSetting, 'captcha('):
                    if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
                        $wordRepository = GeneralUtility::makeInstance(
                            WordRepository::class
                        );
                        $wordRepository->setRequest($this->request ?? $GLOBALS['TYPO3_REQUEST']);
                        $wordObject = $wordRepository->getWord();
                        $wordHash = $wordObject->getWordHash();
                        $userVal = md5(strtolower(mb_convert_encoding($this->getValue(), 'ISO-8859-1')));
                        if ($wordHash !== $userVal) {
                            $this->addMessage('validationErrorCaptcha');
                            $this->isValid = false;
                        }
                    }

                    break;

                default:
                    // e.g. search for method validateCustom()
                    $mainSetting = StringUtility::getValuesBeforeBrackets($validationSetting);
                    if (method_exists($this, 'validate' . ucfirst((string)$mainSetting)) && !$this->{'validate' . ucfirst((string)$mainSetting)}(
                        $this->getValue(),
                        StringUtility::getValuesInBrackets($validationSetting)
                    )) {
                        $this->addMessage('validationError' . ucfirst((string)$mainSetting));
                        $this->isValid = false;
                    }
            }
        }

        return $this->isValid;
    }

    /**
     * This function checks the given validation string from user input against settings in TypoScript. If both strings
     * do not match, it could be possible that there is a manipulation. In this case, we stop validation and return a
     * global error message
     */
    protected function isValidationSettingsDifferentToGlobalSettings(): bool
    {
        return $this->validationSettingsString !== $this->getValidationSettingsFromTypoScript();
    }

    public function setValidationSettingsString(string $validationSettingsString): static
    {
        $this->validationSettingsString = $validationSettingsString;

        return $this;
    }

    public function getValidationSettingsFromTypoScript(): string
    {
        $validationService = GeneralUtility::makeInstance(
            ValidationSettingsService::class,
            $this->getControllerName(),
            $this->getValidationName()
        );

        return $validationService->getValidationStringForField($this->fieldName);
    }

    /**
     * $validationSettingsString contains comma-separated singleSettings
     * e.g. "required, email, inList(1|2|3)
     * the pipes in the singleSettings must be replaced AFTER exploding the $validationSettingsString
     */
    protected function getValidationSettings(): array
    {
        $singleSettingsArray = GeneralUtility::trimExplode(',', $this->validationSettingsString, true);
        foreach ($singleSettingsArray as &$singleSetting) {
            if (str_contains($singleSetting, '|')) {
                $singleSetting = str_replace('|', ',', $singleSetting);
            }
        }

        return $singleSettingsArray;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Add a message to the errormessage array
     */
    public function addMessage(string $message): void
    {
        $this->messages = array_merge($this->messages, [$message]);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setFieldName(string $fieldName): static
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setUser(?User $user = null): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setAdditionalValue(string $additionalValue): static
    {
        $this->additionalValue = $additionalValue;

        return $this;
    }

    public function getAdditionalValue(): string
    {
        return $this->additionalValue;
    }

    public function setPluginUid(int $pluginUid): static
    {
        $this->pluginUid = $pluginUid;

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isValid(mixed $value): void
    {
    }
}
