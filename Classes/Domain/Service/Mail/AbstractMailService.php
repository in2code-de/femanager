<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service\Mail;

use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMailService implements MailServiceInterface
{
    /**
     * Content Object
     */
    public ?object $contentObject = null;

    protected ?EventDispatcherInterface $dispatcher = null;

    protected SendMailService $sendMailService;

    public function __construct(
        SendMailService $sendMailService,
    ) {
        $this->contentObject = ObjectUtility::getContentObject();
        $this->dispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->sendMailService = $sendMailService;
    }

    protected function embedImages(array $variables, array $typoScript, FluidEmail|MailMessage $email): array
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
}
