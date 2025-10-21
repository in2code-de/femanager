<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service\Mail;

use In2code\Femanager\Event\AfterMailSendEvent;
use In2code\Femanager\Event\BeforeMailSendEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class FluidMailService extends AbstractMailService
{
    public function send(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        RequestInterface|null $request = null
    ): bool {
        $email = new FluidEmail();

        $variables = $this->embedImages($variables, $typoScript, $email);

        $typoScriptReceiver = $this->getTypoScriptReceiver($typoScript);
        $typoScriptSender = $this->getTypoScriptSender($typoScript);
        $attachments = $this->getAttachments($typoScript);

        $email
            ->setRequest($request)
            ->to(...($typoScriptReceiver ?: $receiver))
            ->from(...($typoScriptSender ?: $sender))
            ->subject($this->getTypoScripSubject($typoScript) ?: $subject)
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate($template)
            ->assignMultiple($variables)
            ->cc(...$this->getCc($typoScript))
            ->replyTo(...$this->getReplyTo($typoScript))
            ->priority($this->getPriority($typoScript));

        foreach ($attachments as $attachment) {
            $email->attachFromPath($attachment);
        }

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this->sendMailService));
        try {
            GeneralUtility::makeInstance(MailerInterface::class)->send($email);
            $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this->sendMailService));
            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
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
        $email = new FluidEmail();
        $variables = $this->embedImages($variables, $typoScript, $email);
        $email
            ->setRequest($request)
            ->to(...$receiver)
            ->from(...$sender)
            ->subject($subject)
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate($template)
            ->assignMultiple($variables);

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this->sendMailService));
        try {
            GeneralUtility::makeInstance(MailerInterface::class)->send($email);
            $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this->sendMailService));
            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }


    /**
     * @return array<Address>
     */
    protected function getTypoScriptReceiver(array $typoScript): array
    {
        $receiver = [];
        $emailAddress = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('receiver./email', $typoScript),
            (array)ConfigurationUtility::getValue('receiver./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('receiver./name', $typoScript),
            (array)ConfigurationUtility::getValue('receiver./name.', $typoScript)
        );

        if ($emailAddress && $name) {
            $receiver[] = new Address($emailAddress, $name);
        }

        return $receiver;
    }

    /**
     * @return array<Address>
     */
    protected function getTypoScriptSender(array $typoScript): array
    {
        $sender = [];
        $emailAddress = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('sender./email', $typoScript),
            (array)ConfigurationUtility::getValue('sender./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('sender./name', $typoScript),
            (array)ConfigurationUtility::getValue('sender./name.', $typoScript)
        );

        if ($emailAddress && $name) {
            $sender[] = new Address($emailAddress, $name);
        }

        return $sender;
    }

    protected function getTypoScripSubject(array $typoScript): string
    {
        return $this->contentObject->cObjGetSingle((string)$typoScript['subject'], (array)$typoScript['subject.']);
    }

    protected function getCc(array $typoScript): array
    {
        $addresses = [];
        foreach (GeneralUtility::trimExplode(
            ',',
            $this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.']),
            true
        ) as $mail) {
            $addresses[] = new Address($mail);
        }

        return $addresses;
    }

    protected function getReplyTo(array $typoScript): array
    {
        $addresses = [];
        foreach (GeneralUtility::trimExplode(
            ',',
            $this->contentObject->cObjGetSingle($typoScript['replyTo'], $typoScript['replyTo.']),
            true
        ) as $mail) {
            $addresses[] = new Address($mail);
        }

        return $addresses;
    }

    protected function getPriority(array $typoScript): int
    {
        $priority = (int)$this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue('priority', $typoScript),
            (array)ConfigurationUtility::getValue('priority.', $typoScript)
        );
        if ($priority !== 0) {
            return $priority;
        }

        // default priority
        return 1;
    }

    protected function getAttachments(array $typoScript): array
    {
        $attachments = [];
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
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

}
