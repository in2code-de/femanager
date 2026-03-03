<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Domain\Repository\UserRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class CaptchaValidator extends AbstractValidator
{
    public function __construct(
        UserRepository $userRepository,
        ConfigurationManagerInterface $configurationManager,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($userRepository, $configurationManager, $eventDispatcher);
    }

    /**
     * Validation of given Params
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isValid(mixed $value): void
    {
        $this->init();
        if ($this->captchaEnabled() && (!is_string($value) || !$this->validCaptcha($value))) {
            $this->addError('validationErrorCaptcha', 0, ['fieldName' => 'captcha']);
        }
    }

    /**
     * Check if captcha is valid
     */
    protected function validCaptcha(string $captcha): bool
    {
        $isValid = false;
        $wordRepository = GeneralUtility::makeInstance(\SJBR\SrFreecap\Domain\Repository\WordRepository::class);
        $wordRepository->setRequest($this->request);
        $wordObject = $wordRepository->getWord();
        $wordHash = $wordObject->getWordHash();
        if (!empty($wordHash) && ($captcha !== '' && $captcha !== '0') && $wordObject->getHashFunction() === 'md5') {
            $userHash = md5(strtolower(mb_convert_encoding($captcha, 'ISO-8859-1')));
            if (hash_equals($wordHash, $userHash)) {
                $wordRepository->cleanUpWord();
                $isValid = true;
            }
        }

        return $isValid;
    }

    /**
     * Check if captcha is enabled (TypoScript, and sr_freecap loaded)
     */
    protected function captchaEnabled(): bool
    {
        return ExtensionManagementUtility::isLoaded('sr_freecap')
            && !empty($this->validationSettings['captcha']['captcha']);
    }
}
