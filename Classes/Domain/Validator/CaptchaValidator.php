<?php

declare(strict_types=1);
namespace In2code\Femanager\Domain\Validator;

use SJBR\SrFreecap\Domain\Repository\WordRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CaptchaValidator
 */
class CaptchaValidator extends AbstractValidator
{
    /**
     * Validation of given Params
     *
     * @param $user
     */
    public function isValid($user): void
    {
        $this->init();
        if (!$this->captchaEnabled() || $this->validCaptcha()) {

        }
        $this->addError('validationErrorCaptcha', 0, ['fieldName' => 'captcha']);

    }

    /**
     * Check if captcha is valid
     *
     * @return void
     */
    protected function validCaptcha()
    {
        $isValid = false;
        $wordRepository = GeneralUtility::makeInstance(WordRepository::class);
        $wordObject = $wordRepository->getWord();
        $wordHash = $wordObject->getWordHash();
        if (!empty($wordHash) && !empty($this->pluginVariables['captcha'])) {
            if ($wordObject->getHashFunction() == 'md5') {
                if (md5(strtolower(utf8_decode($this->pluginVariables['captcha']))) == $wordHash) {
                    $wordRepository->cleanUpWord();
                    $isValid = true;
                }
            }
        }
        return $isValid;
    }

    /**
     * Check if captcha is enabled (TypoScript, and sr_freecap loaded)
     *
     * @return bool
     */
    protected function captchaEnabled()
    {
        return ExtensionManagementUtility::isLoaded('sr_freecap')
            && !empty($this->validationSettings['captcha']['captcha']);
    }
}
