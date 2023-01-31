<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class PasswordValidator
 */
class PasswordValidator extends AbstractValidatorExtbase
{
    public ?ConfigurationManagerInterface $configurationManager = null;

    /**
     * Content Object
     *
     * @var object
     */
    protected $cObj;

    /**
     * Plugin Variables
     *
     * @var array
     */
    public $piVars = [];

    /**
     * TypoScript Configuration
     *
     * @var array
     */
    public $configuration = [];

    /**
     * Action Name
     *
     * @var string
     */
    protected $actionName;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManagerInterface(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function initializeObject(): void
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        }
    }

    /**
     * Validation of given Params
     *
     * @param $user
     * @return bool
     */
    public function isValid($user)
    {
        $this->initializeObject();
        $this->init();

        // if password fields are not active or if keep function active
        if (!$this->passwordFieldsAdded() || $this->keepPasswordIfEmpty()) {
            return true;
        }

        $password = $user->getPassword();
        $passwordRepeat = isset($this->piVars['password_repeat']) ? $this->piVars['password_repeat'] : '';

        if ($password !== $passwordRepeat) {
            $this->addError('validationErrorPasswordRepeat', 0, ['field' => 'password']);
            return false;
        }

        return true;
    }

    /**
     * Check if Passwords are empty and if keep configuration is active
     *
     * @return bool
     */
    protected function keepPasswordIfEmpty()
    {
        if (isset($this->configuration['edit']['misc']['keepPasswordIfEmpty']) &&
            $this->configuration['edit']['misc']['keepPasswordIfEmpty'] === '1' &&
            isset($this->piVars['user']['password']) && $this->piVars['user']['password'] === '' &&
            isset($this->piVars['password_repeat']) && $this->piVars['password_repeat'] === ''
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if password fields are added with flexform
     *
     * @return bool
     */
    protected function passwordFieldsAdded()
    {
        $flexFormValues = GeneralUtility::xml2array($this->cObj->data['pi_flexform']);
        if (is_array($flexFormValues)) {
            $fields =
                $flexFormValues['data'][$this->actionName]['lDEF']['settings.' . $this->actionName . '.fields']['vDEF'] ?? [];
            if (empty($fields) || GeneralUtility::inList($fields, 'password')) {
                // password fields are added to form
                return true;
            }
        }

        // password fields are not added to form
        return false;
    }

    /**
     * Initialize Validator Function
     */
    protected function init()
    {
        $this->configuration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            'Pi1'
        );
        $this->cObj = $this->configurationManager->getContentObject();
        $this->piVars = GeneralUtility::_GP('tx_femanager_pi1');
        $this->actionName = $this->piVars['__referrer']['@action'];
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
