<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;

/**
 * Class PasswordValidator
 */
class PasswordValidator extends AbstractValidatorExtbase
{

    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    public $configurationManager;

    /**
     * Content Object
     *
     * @var object
     */
    public $cObj;

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
     * Validation of given Params
     *
     * @param $user
     * @return bool
     */
    public function isValid($user)
    {
        $this->init();

        // if password fields are not active or if keep function active
        if (!$this->passwordFieldsAdded() || $this->keepPasswordIfEmpty()) {
            return true;
        }

        $password = $user->getPassword();
        $passwordRepeat = isset($this->piVars['password_repeat']) ? $this->piVars['password_repeat'] : '';

        if ($password !== $passwordRepeat) {
            $this->addError('validationErrorPasswordRepeat', 'password');
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
                $flexFormValues['data'][$this->actionName]['lDEF']['settings.' . $this->actionName . '.fields']['vDEF'];
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
     *
     * @return void
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
}
