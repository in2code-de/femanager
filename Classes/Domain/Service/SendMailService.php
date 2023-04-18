<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Event\AfterMailSendEvent;
use In2code\Femanager\Event\BeforeMailSendEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\TemplateUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SendMailService
 */
class SendMailService
{
    /**
     * Content Object
     *
     * @var object|null
     */
    public ?object $contentObject = null;

    /**
     * @var Mailer|null
     */
    private ?Mailer $mailer = null;
    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $dispatcher = null;

    /**
     * SendMailService constructor.
     */
    public function __construct(Mailer $mailer, EventDispatcherInterface $dispatcher)
    {
        $this->contentObject = ObjectUtility::getContentObject();
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
    }

    protected function contentObjectStart(array $variables): void
    {
        if (empty($variables['user'] ?? []) === false &&
            method_exists(($variables['user'] ?? null), '_getProperties') === true
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
        array $typoScript = []
    ): bool {
        if (false === $this->isMailEnabled($typoScript, $receiver)) {
            return false;
        }

        $this->contentObjectStart($variables);
        $email = GeneralUtility::makeInstance(MailMessage::class);
        $variables = $this->embedImages($variables, $typoScript, $email);
        $this->prepareMailObject($template, $receiver, $sender, $subject, $variables, $email);
        $this->overwriteEmailReceiver($typoScript, $email);
        $this->overwriteEmailSender($typoScript, $email);
        $this->setSubject($typoScript, $email);
        $this->setCc($typoScript, $email);
        $this->setPriority($typoScript, $email);
        $this->setAttachments($typoScript, $email);

        $this->dispatcher->dispatch(new BeforeMailSendEvent($email, $variables, $this));
        $email->send();
        $this->dispatcher->dispatch(new AfterMailSendEvent($email, $variables, $this));

        return $email->isSent();
    }

    /**
     * Generate Email Body
     *
     * @param string $template Template file in Templates/Email/
     * @param array $variables Variables for assignMultiple
     */
    protected function getMailBody(string $template, array $variables): string
    {
        $standAloneView = TemplateUtility::getDefaultStandAloneView();
        $standAloneView->setTemplatePathAndFilename($this->getRelativeEmailPathAndFilename($template));
        $standAloneView->assignMultiple($variables);
        return $standAloneView->render();
    }

    protected function embedImages(array $variables, array $typoScript, MailMessage $email): array
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
            $name = basename((string) $path);
            $imagePart = DataPart::fromPath($path);
            $contentType = $imagePart->getMediaType() . '/' . $imagePart->getMediaSubtype();
            $email->embedFromPath($path, $name, $contentType);
            $imageVariables[] = 'cid:' . $name;
        }

        return array_merge($variables, ['embedImages' => $imageVariables]);
    }

    protected function prepareMailObject(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables,
        MailMessage $email
    ): void {
        $html = $this->getMailBody($template, $variables);
        $email->setTo($receiver)
            ->setFrom($sender)
            ->setSubject($subject)
            ->html($html);
    }

    protected function overwriteEmailReceiver(array $typoScript, MailMessage $email): void
    {
        $emailAddress = $this->contentObject->cObjGetSingle(
            ConfigurationUtility::getValue('receiver./email', $typoScript),
            ConfigurationUtility::getValue('receiver./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            ConfigurationUtility::getValue('receiver./name', $typoScript),
            ConfigurationUtility::getValue('receiver./name.', $typoScript)
        );
        if ($emailAddress && $name) {
            $email->setTo([$emailAddress => $name]);
        }
    }

    protected function overwriteEmailSender(array $typoScript, MailMessage $email): void
    {
        $emailAddress = $this->contentObject->cObjGetSingle(
            ConfigurationUtility::getValue('sender./email', $typoScript),
            ConfigurationUtility::getValue('sender./email.', $typoScript)
        );
        $name = $this->contentObject->cObjGetSingle(
            ConfigurationUtility::getValue('sender./name', $typoScript),
            ConfigurationUtility::getValue('sender./name.', $typoScript)
        );

        if ($emailAddress && $name) {
            $email->setFrom([$emailAddress => $name]);
        }
    }

    protected function setSubject(array $typoScript, MailMessage $email): void
    {
        if ($this->contentObject->cObjGetSingle($typoScript['subject'], $typoScript['subject.'])) {
            $email->setSubject($this->contentObject->cObjGetSingle($typoScript['subject'], $typoScript['subject.']));
        }
    }

    protected function setCc(array $typoScript, MailMessage $email): void
    {
        if ($this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.'])) {
            $email->setCc($this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.']));
        }
    }

    protected function setPriority(array $typoScript, MailMessage $email): void
    {
        $priority = (int)$this->contentObject->cObjGetSingle(
            ConfigurationUtility::getValue('priority', $typoScript),
            ConfigurationUtility::getValue('priority.', $typoScript)
        );
        if ($priority) {
            $email->priority($priority);
        }
    }

    protected function setAttachments(array $typoScript, MailMessage $email): void
    {
        if ($this->contentObject->cObjGetSingle($typoScript['attachments'] ?? '', $typoScript['attachments.'] ?? '')) {
            $files = GeneralUtility::trimExplode(
                ',',
                $this->contentObject->cObjGetSingle(
                    $typoScript['attachments'] ?? '',
                    $typoScript['attachments.'] ?? ''
                ),
                true
            );
            foreach ($files as $file) {
                $email->attachFromPath($file);
            }
        }
    }

    /**
     * Get path and filename for mail template
     */
    protected function getRelativeEmailPathAndFilename(string $fileName): string
    {
        return TemplateUtility::getTemplatePath('Email/' . ucfirst($fileName) . '.html');
    }

    protected function isMailEnabled(array $typoScript, array $receiver): bool
    {
        $cObjectName = ConfigurationUtility::getValue('_enable', $typoScript);
        $cObjectConf = ConfigurationUtility::getValue('_enable.', $typoScript);

        return $this->contentObject->cObjGetSingle($cObjectName, $cObjectConf) && count($receiver) > 0;
    }
}
