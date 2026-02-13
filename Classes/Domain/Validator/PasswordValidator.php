<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as AbstractValidatorExtbase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PasswordValidator extends AbstractValidatorExtbase
{
    protected ?ContentObjectRenderer $currentContentObject = null;
    public array $extbaseArguments = [];
    public array $typoScriptConfiguration = [];
    protected string $referrerActionName = '';

    public function __construct(public readonly ConfigurationManagerInterface $configurationManager)
    {
    }

    protected function init(): void
    {
        $this->typoScriptConfiguration = ConfigurationUtility::getConfiguration();
        $this->currentContentObject = $this->request->getAttribute('currentContentObject');
        $extbaseRequestParameter = $this->request->getAttribute('extbase');
        $this->extbaseArguments = ($extbaseRequestParameter?->getArguments() ?? false) ? $extbaseRequestParameter?->getArguments() : [];
        $this->referrerActionName = ($extbaseRequestParameter?->getInternalArgument('__referrer')['@action'] ?? false) ? $extbaseRequestParameter?->getInternalArgument('__referrer')['@action'] : '';
    }

    /**
     * @param User $value
     */
    protected function isValid(mixed $value): void
    {
        $user = $value;
        $this->init();

        // if password fields are not active or if keep function active
        if ($this->passwordFieldsAdded() && !$this->keepPasswordIfEmpty()) {
            $password = $user->getPassword();
            $passwordRepeat = $this->extbaseArguments['password_repeat'] ?? '';

            if ($password !== $passwordRepeat) {
                $this->addError('validationErrorPasswordRepeat', 0, ['field' => 'password']);
            }
        }
    }

    /**
     * Check if Passwords are empty and if keep configuration is active
     */
    protected function keepPasswordIfEmpty(): bool
    {
        return isset($this->typoScriptConfiguration['edit']['misc']['keepPasswordIfEmpty']) &&
            $this->typoScriptConfiguration['edit']['misc']['keepPasswordIfEmpty'] === '1' &&
            (!isset($this->extbaseArguments['user']['password']) || $this->extbaseArguments['user']['password'] === '') &&
            (!isset($this->extbaseArguments['password_repeat']) || $this->extbaseArguments['password_repeat'] === '');
    }

    /**
     * Check if password fields are added with flexform
     */
    protected function passwordFieldsAdded(): bool
    {
        $flexFormValues = GeneralUtility::xml2array($this->currentContentObject->data['pi_flexform']);
        if (is_array($flexFormValues)) {
            $fields =
                $flexFormValues['data'][$this->referrerActionName]['lDEF']['settings.' . $this->referrerActionName . '.fields']['vDEF']
                ?? [];
            if (empty($fields) || GeneralUtility::inList($fields, 'password')) {
                // password fields are added to form
                return true;
            }
        }

        // password fields are not added to form
        return false;
    }
}
