<?php

declare(strict_types = 1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\PluginRepository;
use In2code\Femanager\Domain\Service\ValidationSettingsService;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ClientsideValidator
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
     * @var string
     */
    protected $validationSettingsString;

    /**
     * Field Value
     *
     * @var string
     */
    protected $value;

    /**
     * Field Name
     *
     * @var string
     */
    protected $fieldName;

    /**
     * User
     *
     * @var User
     */
    protected $user = null;

    /**
     * Error message container
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Additional Values (for comparing a value with another)
     *
     * @var string
     */
    protected $additionalValue;

    /**
     * @var int
     */
    protected $plugin = 0;

    /**
     * @var string
     */
    protected $actionName = '';

    /**
     * Validate Field
     *
     * @return bool
     */
    public function validateField()
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

                case 'email':
                    if ($this->getValue() && !$this->validateEmail($this->getValue())) {
                        $this->addMessage('validationErrorEmail');
                        $this->isValid = false;
                    }
                    break;

                case stristr($validationSetting, 'min('):
                    if ($this->getValue() &&
                        !$this->validateMin($this->getValue(), StringUtility::getValuesInBrackets($validationSetting))
                    ) {
                        $this->addMessage('validationErrorMin');
                        $this->isValid = false;
                    }
                    break;

                case stristr($validationSetting, 'max('):
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

                case stristr($validationSetting, 'mustInclude('):
                    if ($this->getValue() &&
                        !$this->validateMustInclude(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting)
                        )
                    ) {
                        $this->addMessage('validationErrorMustInclude');
                        $this->isValid = false;
                    }
                    break;

                case stristr($validationSetting, 'mustNotInclude('):
                    if ($this->getValue() &&
                        !$this->validateMustNotInclude(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting)
                        )
                    ) {
                        $this->addMessage('validationErrorMustNotInclude');
                        $this->isValid = false;
                    }
                    break;

                case stristr($validationSetting, 'inList('):
                    if (!$this->validateInList(
                        $this->getValue(),
                        StringUtility::getValuesInBrackets($validationSetting)
                    )) {
                        $this->addMessage('validationErrorInList');
                        $this->isValid = false;
                    }
                    break;

                case stristr($validationSetting, 'sameAs('):
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

                case stristr($validationSetting, 'captcha('):
                    if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
                        $wordRepository = ObjectUtility::getObjectManager()->get(
                            \SJBR\SrFreecap\Domain\Repository\WordRepository::class
                        );
                        $wordObject = $wordRepository->getWord();
                        $wordHash = $wordObject->getWordHash();
                        $userVal = md5(strtolower(utf8_decode($this->getValue())));
                        if ($wordHash !== $userVal) {
                            $this->addMessage('validationErrorCaptcha', 'captcha');
                            $this->isValid = false;
                        }
                    }

                    break;

                default:
                    // e.g. search for method validateCustom()
                    $mainSetting = StringUtility::getValuesBeforeBrackets($validationSetting);
                    if (method_exists($this, 'validate' . ucfirst($mainSetting))) {
                        if (!$this->{'validate' . ucfirst($mainSetting)}(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting)
                        )) {
                            $this->addMessage('validationError' . ucfirst($mainSetting));
                            $this->isValid = false;
                        }
                    }
            }
        }

        return $this->isValid;
    }

    /**
     * This function checks the given validation string from user input against settings in TypoScript. If both strings
     * do not match, it could be possible that there is a manipulation. In this case, we stop validation and return a
     * global error message
     *
     * @return bool
     */
    protected function isValidationSettingsDifferentToGlobalSettings(): bool
    {
        return $this->getValidationSettingsString() !== $this->getValidationSettingsFromTypoScript();
    }

    /**
     * Set validation
     *
     * @param string $validationSettingsString
     * @return ClientsideValidator
     */
    public function setValidationSettingsString($validationSettingsString)
    {
        $this->validationSettingsString = $validationSettingsString;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidationSettingsString()
    {
        return $this->validationSettingsString;
    }

    /**
     * @return string
     */
    public function getValidationSettingsFromTypoScript(): string
    {
        $controllerName = $this->getControllerName();
        $validationService = ObjectUtility::getObjectManager()->get(
            ValidationSettingsService::class,
            $controllerName,
            $this->getValidationName()
        );

        return $validationService->getValidationStringForField($this->fieldName);
    }

    /**
     * @return array
     */
    protected function getValidationSettings(): array
    {
        $validationSettings = GeneralUtility::trimExplode(',', $this->validationSettingsString, true);
        $validationSettings = str_replace('|', ',', $validationSettings);

        return $validationSettings;
    }

    /**
     * @param string $value
     * @return ClientsideValidator
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Add a message to the errormessage array
     *
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->messages = array_merge($this->messages, [$message]);
    }

    /**
     * @param array $messages
     * @return ClientsideValidator
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $fieldName
     * @return ClientsideValidator
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param User $user
     * @return ClientsideValidator
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $additionalValue
     * @return ClientsideValidator
     */
    public function setAdditionalValue($additionalValue)
    {
        $this->additionalValue = $additionalValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalValue()
    {
        return $this->additionalValue;
    }

    /**
     * @return int
     */
    public function getPlugin(): int
    {
        return $this->plugin;
    }

    /**
     * @param int $plugin
     * @return ClientsideValidator
     */
    public function setPlugin(int $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     * @return ClientsideValidator
     */
    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;

        return $this;
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
    protected function getControllerName(): string
    {
        $pluginRepository = ObjectUtility::getObjectManager()->get(PluginRepository::class);
        $controllerName = $pluginRepository->getControllerNameByPluginSettings($this->getPlugin());

        return $controllerName;
    }

    /**
     * @param mixed $value
     */
    protected function isValid($value)
    {
    }
}
