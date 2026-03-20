<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service\Mail;

use In2code\Femanager\Event\AfterMailSendEvent;
use In2code\Femanager\Event\BeforeMailBodyRenderEvent;
use In2code\Femanager\Event\BeforeMailSendEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\TemplateUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * @deprecated will be removed with version 14.0
 */
class MailMessageService extends AbstractMailService
{
    public function send(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        ?RequestInterface $request = null
    ): bool {
        $email = GeneralUtility::makeInstance(MailMessage::class);

        $variables = $this->embedImages($variables, $typoScript, $email);

        $this->prepareMailObject($template, $receiver, $sender, $subject, $variables, $email, $request);
        $this->overwriteEmailReceiver($typoScript, $email);
        $this->overwriteEmailSender($typoScript, $email);
        $this->setSubject($typoScript, $email);
        $this->setCc($typoScript, $email);
        $this->setReplyTo($typoScript, $email);
        $this->setPriority($typoScript, $email);
        $this->setAttachments($typoScript, $email);

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this->sendMailService));
        $email->send();
        $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this->sendMailService));

        return $email->isSent();
    }

    public function sendSimple(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        ?RequestInterface $request = null
    ): bool {
        $email = GeneralUtility::makeInstance(MailMessage::class);
        $variables = $this->embedImages($variables, $typoScript, $email);
        $this->prepareMailObject($template, $receiver, $sender, $subject, $variables, $email, $request);
        $email->setTo($receiver);
        $email->setFrom($sender);
        $email->setSubject($subject);

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this->sendMailService));
        $email->send();
        $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this->sendMailService));

        return $email->isSent();
    }

    protected function overwriteEmailReceiver(array $typoScript, MailMessage $email): void
    {
        $emailAddress = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('receiver./email', $typoScript),
            (array)ConfigurationUtility::getValue('receiver./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('receiver./name', $typoScript),
            (array)ConfigurationUtility::getValue('receiver./name.', $typoScript)
        );
        if ($emailAddress && $name) {
            $email->setTo([$emailAddress => $name]);
        }
    }

    protected function overwriteEmailSender(array $typoScript, MailMessage $email): void
    {
        $emailAddress = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('sender./email', $typoScript),
            (array)ConfigurationUtility::getValue('sender./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('sender./name', $typoScript),
            (array)ConfigurationUtility::getValue('sender./name.', $typoScript)
        );

        if ($emailAddress && $name) {
            $email->setFrom([$emailAddress => $name]);
        }
    }

    protected function setSubject(array $typoScript, MailMessage $email): void
    {
        $subject = $this->contentObject->cObjGetSingle((string)$typoScript['subject'], (array)$typoScript['subject.']);
        if ($subject) {
            $email->setSubject($subject);
        }
    }

    protected function setCc(array $typoScript, MailMessage $email): void
    {
        $cc = $this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.']);
        if ($cc) {
            $email->setCc(GeneralUtility::trimExplode(',', $cc, true));
        }
    }

    protected function setReplyTo(array $typoScript, MailMessage $email): void
    {
        if (is_null($typoScript['replyTo'] ?? null)) {
            return;
        }
        $replyTo = $this->contentObject->cObjGetSingle($typoScript['replyTo'], $typoScript['replyTo.']);
        if ($replyTo) {
            $email->setReplyTo(GeneralUtility::trimExplode(',', $replyTo, true));
        }
    }

    protected function setPriority(array $typoScript, MailMessage $email): void
    {
        $priority = (int)$this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('priority', $typoScript),
            (array)ConfigurationUtility::getValue('priority.', $typoScript)
        );
        if ($priority !== 0) {
            $email->priority($priority);
        }
    }

    protected function setAttachments(array $typoScript, MailMessage $email): void
    {
        if ($this->contentObject->cObjGetSingle($typoScript['attachments'] ?? '', $typoScript['attachments.'] ?? [])) {
            $files = GeneralUtility::trimExplode(
                ',',
                $this->contentObject->cObjGetSingle(
                    $typoScript['attachments'] ?? '',
                    $typoScript['attachments.'] ?? []
                ),
                true
            );
            foreach ($files as $file) {
                $email->attachFromPath($file);
            }
        }
    }

    protected function prepareMailObject(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables,
        MailMessage $email,
        RequestInterface|null $request = null
    ): void {
        $html = $this->getMailBody($template, $variables, $request);
        $email->setTo($receiver)
            ->setFrom($sender)
            ->setSubject($subject)
            ->html($html);
    }

    /**
     * Generate Email Body
     *
     * @param string $template Template file in Templates/Email/
     * @param array $variables Variables for assignMultiple
     */
    protected function getMailBody(string $template, array $variables, RequestInterface|null $request = null): string
    {
        $standAloneView = TemplateUtility::getDefaultStandAloneView($request);
        $standAloneView->setTemplatePathAndFilename($this->getRelativeEmailPathAndFilename($template));
        $standAloneView->assignMultiple($variables);

        $this->dispatcher->dispatch(new BeforeMailBodyRenderEvent($standAloneView, $variables, $this->sendMailService));
        return $standAloneView->render();
    }

    /**
     * Get path and filename for mail template
     */
    protected function getRelativeEmailPathAndFilename(string $fileName): string
    {
        return TemplateUtility::getTemplatePath('Email/' . ucfirst($fileName) . '.html');
    }
}
