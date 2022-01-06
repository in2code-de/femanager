<?php
declare(strict_types = 1);
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Event\AfterMailSendEvent;
use In2code\Femanager\Event\BeforeMailSendEvent;
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
     * @var object
     */
    public $contentObject = null;

    /**
     * SignalSlot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $signalSlotDispatcher;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * SendMailService constructor.
     */
    public function __construct(Mailer $mailer, EventDispatcherInterface $dispatcher)
    {
        $this->contentObject = ObjectUtility::getContentObject();
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param array $variables
     */
    protected function contentObjectStart(array $variables)
    {
        if (!empty($variables['user']) && method_exists($variables['user'], '_getProperties')) {
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
     * @return string
     */
    protected function getMailBody($template, $variables)
    {
        $standAloneView = TemplateUtility::getDefaultStandAloneView();
        $standAloneView->setTemplatePathAndFilename($this->getRelativeEmailPathAndFilename($template));
        $standAloneView->assignMultiple($variables);
        return $standAloneView->render();
    }

    /**
     * @param array $variables
     * @param array $typoScript
     * @param MailMessage $email
     * @return array
     */
    protected function embedImages(array $variables, array $typoScript, MailMessage $email): array
    {
        $images = $this->contentObject->cObjGetSingle($typoScript['embedImage'], $typoScript['embedImage.']);

        if (!$images) {
            return $variables;
        }

        $images = GeneralUtility::trimExplode(',', $images, true);
        $imageVariables = [];

        foreach ($images as $path) {
            $name = basename($path);
            $imagePart = DataPart::fromPath($path);
            $contentType = $imagePart->getMediaType() . '/' . $imagePart->getMediaSubtype();
            $email->embedFromPath($path, $name, $contentType);
            $imageVariables[] = 'cid:' . $name;
        }

        return array_merge($variables, ['embedImages' => $imageVariables]);
    }

    /**
     * @param string $template
     * @param array $receiver
     * @param array $sender
     * @param string $subject
     * @param array $variables
     * @param MailMessage $email
     */
    protected function prepareMailObject(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables,
        MailMessage $email
    ) {
        $html = $this->getMailBody($template, $variables);
        $email
            ->setTo($receiver)
            ->setFrom($sender)
            ->setSubject($subject)
            ->html($html);
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function overwriteEmailReceiver(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['receiver.']['email'], $typoScript['receiver.']['email.'])
            && $this->contentObject->cObjGetSingle($typoScript['receiver.']['name'], $typoScript['receiver.']['name.'])
        ) {
            $emailAddress = $this->contentObject->cObjGetSingle(
                $typoScript['receiver.']['email'],
                $typoScript['receiver.']['email.']
            );
            $name = $this->contentObject->cObjGetSingle(
                $typoScript['receiver.']['name'],
                $typoScript['receiver.']['name.']
            );
            $email->setTo([$emailAddress => $name]);
        }
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function overwriteEmailSender(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['sender.']['email'], $typoScript['sender.']['email.']) &&
            $this->contentObject->cObjGetSingle($typoScript['sender.']['name'], $typoScript['sender.']['name.'])
        ) {
            $emailAddress = $this->contentObject->cObjGetSingle(
                $typoScript['sender.']['email'],
                $typoScript['sender.']['email.']
            );
            $name = $this->contentObject->cObjGetSingle(
                $typoScript['sender.']['name'],
                $typoScript['sender.']['name.']
            );
            $email->setFrom([$emailAddress => $name]);
        }
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function setSubject(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['subject'], $typoScript['subject.'])) {
            $email->setSubject($this->contentObject->cObjGetSingle($typoScript['subject'], $typoScript['subject.']));
        }
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function setCc(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.'])) {
            $email->setCc($this->contentObject->cObjGetSingle($typoScript['cc'], $typoScript['cc.']));
        }
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function setPriority(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['priority'], $typoScript['priority.'])) {
            $email->priority((int)$this->contentObject->cObjGetSingle($typoScript['priority'], $typoScript['priority.']));
        }
    }

    /**
     * @param array $typoScript
     * @param MailMessage $email
     */
    protected function setAttachments(array $typoScript, MailMessage $email)
    {
        if ($this->contentObject->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.'])) {
            $files = GeneralUtility::trimExplode(
                ',',
                $this->contentObject->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.']),
                true
            );
            foreach ($files as $file) {
                $email->attachFromPath($file);
            }
        }
    }

    /**
     * Get path and filename for mail template
     *
     * @param string $fileName
     * @return string
     */
    protected function getRelativeEmailPathAndFilename($fileName)
    {
        return TemplateUtility::getTemplatePath('Email/' . ucfirst($fileName) . '.html');
    }

    /**
     * @param array $typoScript
     * @param array $receiver
     * @return bool
     */
    protected function isMailEnabled(array $typoScript, array $receiver): bool
    {
        return $this->contentObject->cObjGetSingle($typoScript['_enable'], $typoScript['_enable.'])
            && count($receiver) > 0;
    }
}
