<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Validator;

use In2code\Femanager\Utility\ObjectUtility;
use SJBR\SrFreecap\Domain\Repository\WordRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class CaptchaValidator
 * @package In2code\Femanager\Domain\Validator
 */
class CaptchaValidator extends AbstractValidator
{

    /**
     * Validation of given Params
     *
     * @param $user
     * @return bool
     */
    public function isValid($user)
    {
        $this->init();
        if (!$this->captchaEnabled() || $this->validCaptcha()) {
            return true;
        }
        $this->addError('validationErrorCaptcha', 'captcha');
        return false;
    }

    /**
     * Check if captcha is valid
     *
     * @return bool
     */
    protected function validCaptcha()
    {
        $isValid = false;
        $wordRepository = ObjectUtility::getObjectManager()->get(WordRepository::class);
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
        return (ExtensionManagementUtility::isLoaded('sr_freecap')
            && !empty($this->validationSettings['captcha']['captcha']));
    }
}
