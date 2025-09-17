<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Event\AfterMailSendEvent;
use In2code\Femanager\Event\BeforeMailSendEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class SendMailService
 */
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

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this));
        try {
            GeneralUtility::makeInstance(MailerInterface::class)->send($email);
            $sent = true;
            $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this));
        } catch (TransportExceptionInterface) {
            $sent = false;
        }

        return $sent;
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

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this));
        try {
            GeneralUtility::makeInstance(MailerInterface::class)->send($email);
            $sent = true;
            $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this));
        } catch (TransportExceptionInterface) {
            $sent = false;
        }

        return $sent;
    }

    protected function embedImages(array $variables, array $typoScript, FluidEmail $email): array
    {
        $images = $this->contentObject->cObjGetSingle(
            $typoScript['embedImage'] ?? 'TEXT',
            $typoScript['embedImage.'] ?? []
        );

        if (!$images) {
            return $variables;
        }

        $images = GeneralUtility::trimExplode(',', $images, true);
        $imageVariables = [];

        foreach ($images as $path) {
            $name = basename((string)$path);
            $imagePart = DataPart::fromPath($path);
            $contentType = $imagePart->getMediaType() . '/' . $imagePart->getMediaSubtype();
            $email->embedFromPath($path, $name, $contentType);
            $imageVariables[] = 'cid:' . $name;
        }

        return array_merge($variables, ['embedImages' => $imageVariables]);
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

    protected function isMailEnabled(array $typoScript, array $receiver): bool
    {
        $cObjectName = (string)ConfigurationUtility::getValue('_enable', $typoScript);
        $cObjectConf = (array)ConfigurationUtility::getValue('_enable.', $typoScript);

        return $this->contentObject->cObjGetSingle($cObjectName, $cObjectConf) && $receiver !== [];
    }
}
