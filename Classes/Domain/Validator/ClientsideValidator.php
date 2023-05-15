<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\PluginRepository;
use In2code\Femanager\Domain\Service\ValidationSettingsService;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use SJBR\SrFreecap\Domain\Repository\WordRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
    protected $user;

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
    protected $pluginName = '';

    /**
     * @var string
     */
    protected $actionName = '';

    protected function init()
    {
        $this->initializeObject();
        $this->setPluginVariables();
        $this->setClientValidationSettings();
    }

    protected function setClientValidationSettings()
    {
        $pluginName = $this->getPluginName();
        if ($pluginName !== '') {
            $config = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Femanager',
                null
            );
            $controllerName = $this->getControllerNameByPlugin($pluginName);
            $validationName = $this->getValidationNameByPlugin($pluginName);
            $this->validationSettings = $config[$controllerName][$validationName];
        }
    }

    /**
     * Validate Field
     */
    public function validateField(string $pluginName = 'tx_femanager_new'): bool
    {
        if ($this->isValidationSettingsDifferentToGlobalSettings($pluginName)) {
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

                case stristr((string) $validationSetting, 'min('):
                    if ($this->getValue() &&
                        !$this->validateMin($this->getValue(), StringUtility::getValuesInBrackets($validationSetting))
                    ) {
                        $this->addMessage('validationErrorMin');
                        $this->isValid = false;
                    }
                    break;

                case stristr((string) $validationSetting, 'max('):
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

                case stristr((string) $validationSetting, 'mustInclude('):
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

                case stristr((string) $validationSetting, 'mustNotInclude('):
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

                case stristr((string) $validationSetting, 'inList('):
                    if (!$this->validateInList(
                        $this->getValue(),
                        StringUtility::getValuesInBrackets($validationSetting)
                    )) {
                        $this->addMessage('validationErrorInList');
                        $this->isValid = false;
                    }
                    break;

                case stristr((string) $validationSetting, 'sameAs('):
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

                case stristr((string) $validationSetting, 'captcha('):
                    if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
                        $wordRepository = GeneralUtility::makeInstance(
                            WordRepository::class
                        );
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
                    if (method_exists($this, 'validate' . ucfirst((string) $mainSetting))) {
                        if (!$this->{'validate' . ucfirst((string) $mainSetting)}(
                            $this->getValue(),
                            StringUtility::getValuesInBrackets($validationSetting)
                        )) {
                            $this->addMessage('validationError' . ucfirst((string) $mainSetting));
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
     */
    protected function isValidationSettingsDifferentToGlobalSettings(string $pluginName = 'tx_femanager_new'): bool
    {
        return $this->getValidationSettingsString() !== $this->getValidationSettingsFromTypoScript($pluginName);
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

    public function getValidationSettingsFromTypoScript(string $pluginName = 'tx_femanager_new'): string
    {
        $controllerName = $this->getControllerNameByPlugin($pluginName);
        $validationService = GeneralUtility::makeInstance(
            ValidationSettingsService::class,
            $controllerName,
            $this->getValidationNameByPlugin($pluginName)
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

        if (!is_string($this->validationSettingsString)) {
            return [];
        }
        $singleSettingsArray = GeneralUtility::trimExplode(',', $this->validationSettingsString, true);
        foreach ($singleSettingsArray as &$singleSetting) {
            if (str_contains((string) $singleSetting, '|')) {
                $singleSetting = str_replace('|', ',', (string) $singleSetting);
            }
        }
        return $singleSettingsArray;
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

    public function getPlugin(): int
    {
        return $this->plugin;
    }

    /**
     * @return ClientsideValidator
     */
    public function setPlugin(int $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * @return ClientsideValidator
     */
    public function setPluginName(string $pluginName)
    {
        $this->pluginName = $pluginName;

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
     * @return ClientsideValidator
     */
    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;

        return $this;
    }

    protected function getValidationNameByPlugin(string $plugin = 'tx_femanager_new'): string
    {
        $validationName = 'validation';
        if ($this->getControllerNameByPlugin($plugin) === 'invitation' && $this->getActionName() === 'edit') {
            $validationName = 'validationEdit';
        }

        return $validationName;
    }

    /**
     * @param mixed $value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isValid($value): void
    {
    }
}
