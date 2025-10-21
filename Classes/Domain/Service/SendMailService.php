<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Service\Mail\FluidMailService;
use In2code\Femanager\Domain\Service\Mail\MailMessageService;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class SendMailService
{
    /**
     * Content Object
     */
    public ?object $contentObject = null;

    /**
     * SendMailService constructor.
     */
    public function __construct(
        private readonly ?EventDispatcherInterface $dispatcher
    ) {
        $this->contentObject = ObjectUtility::getContentObject();
    }

    protected function contentObjectStart(array $variables): void
    {
        if (
            empty($variables['user'] ?? []) === false &&
            method_exists(($variables['user'] ?? null), '_getProperties')
        ) {
            $this->contentObject->start($variables['user']->_getProperties());
        }
    }

    /**
     * Generate and send Email
     *
     * @param string $template Template file in Templates/Email/
     * @param array $receiver Combination of Email => Name
     * @param array $sender Combination of Email => Name
     * @param string $subject Mail subject
     * @param array $variables Variables for assignMultiple
     * @param array $typoScript Add TypoScript to overwrite values
     * @return bool mail was sent?
     */
    public function send(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        RequestInterface|null $request = null
    ): bool {
        if ($this->isMailEnabled($typoScript, $receiver) === false) {
            return false;
        }

        $this->contentObjectStart($variables);

        if(ConfigurationUtility::useFluidMail()) {
            $mailService = GeneralUtility::makeInstance(FluidMailService::class, $this);
        } else {
            $mailService = GeneralUtility::makeInstance(MailMessageService::class, $this);
        }

        return $mailService->send($template, $receiver, $sender, $subject, $variables, $typoScript, $request);
    }

    public function sendSimple(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        RequestInterface|null $request = null
    ): bool {
        if ($this->isMailEnabled($typoScript, $receiver) === false) {
            return false;
        }

        if(ConfigurationUtility::useFluidMail()) {
            $mailService = GeneralUtility::makeInstance(FluidMailService::class, $this);
        } else {
            $mailService = GeneralUtility::makeInstance(MailMessageService::class, $this);
        }

        return $mailService->sendSimple($template, $receiver, $sender, $subject, $variables, $typoScript, $request);
    }

    protected function isMailEnabled(array $typoScript, array $receiver): bool
    {
        $cObjectName = (string)ConfigurationUtility::getValue('_enable', $typoScript);
        $cObjectConf = (array)ConfigurationUtility::getValue('_enable.', $typoScript);

        return $this->contentObject->cObjGetSingle($cObjectName, $cObjectConf) && $receiver !== [];
    }
}
