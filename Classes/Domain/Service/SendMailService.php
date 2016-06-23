<?php
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Utility\TemplateUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * SendMail Function
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
 */
class SendMailService
{

    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * Content Object
     *
     * @var object
     */
    public $cObj;

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
    public function send($template, $receiver, $sender, $subject, $variables = [], $typoScript = [])
    {
        // config
        $email = $this->objectManager->get(MailMessage::class);
        $this->cObj = $this->configurationManager->getContentObject();
        if (!empty($variables['user']) && method_exists($variables['user'], '_getProperties')) {
            $this->cObj->start($variables['user']->_getProperties());
        }
        if (!$this->cObj->cObjGetSingle($typoScript['_enable'], $typoScript['_enable.']) || count($receiver) === 0) {
            return false;
        }

        // add embed images to mail body
        if ($this->cObj->cObjGetSingle($typoScript['embedImage'], $typoScript['embedImage.'])) {
            $images = GeneralUtility::trimExplode(
                ',',
                $this->cObj->cObjGetSingle($typoScript['embedImage'], $typoScript['embedImage.']),
                true
            );
            $imageVariables = [];
            foreach ($images as $image) {
                $imageVariables[] = $email->embed(\Swift_Image::fromPath($image));
            }
            $variables = array_merge($variables, ['embedImages' => $imageVariables]);
        }

        /**
         * Generate and send Email
         */
        $email
            ->setTo($receiver)
            ->setFrom($sender)
            ->setSubject($subject)
            ->setCharset($GLOBALS['TSFE']->metaCharset)
            ->setBody($this->getMailBody($template, $variables), 'text/html');

        // overwrite email receiver
        if (
            $this->cObj->cObjGetSingle(
                $typoScript['receiver.']['email'],
                $typoScript['receiver.']['email.']
            )
            &&
            $this->cObj->cObjGetSingle(
                $typoScript['receiver.']['name'],
                $typoScript['receiver.']['name.']
            )
        ) {
            $email->setTo(
                [
                    $this->cObj->cObjGetSingle($typoScript['receiver.']['email'], $typoScript['receiver.']['email.']) =>
                        $this->cObj->cObjGetSingle($typoScript['receiver.']['name'], $typoScript['receiver.']['name.'])
                ]
            );
        }

        // overwrite email sender
        if ($this->cObj->cObjGetSingle($typoScript['sender.']['email'], $typoScript['sender.']['email.']) &&
            $this->cObj->cObjGetSingle($typoScript['sender.']['name'], $typoScript['sender.']['name.'])
        ) {
            $email->setFrom(
                [
                    $this->cObj->cObjGetSingle($typoScript['sender.']['email'], $typoScript['sender.']['email.']) =>
                        $this->cObj->cObjGetSingle($typoScript['sender.']['name'], $typoScript['sender.']['name.'])
                ]
            );
        }

        // overwrite email subject
        if ($this->cObj->cObjGetSingle($typoScript['subject'], $typoScript['subject.'])) {
            $email->setSubject($this->cObj->cObjGetSingle($typoScript['subject'], $typoScript['subject.']));
        }

        // overwrite email CC receivers
        if ($this->cObj->cObjGetSingle($typoScript['cc'], $typoScript['cc.'])) {
            $email->setCc($this->cObj->cObjGetSingle($typoScript['cc'], $typoScript['cc.']));
        }

        // overwrite email priority
        if ($this->cObj->cObjGetSingle($typoScript['priority'], $typoScript['priority.'])) {
            $email->setPriority($this->cObj->cObjGetSingle($typoScript['priority'], $typoScript['priority.']));
        }

        // add attachments from typoscript
        if ($this->cObj->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.'])) {
            $files = GeneralUtility::trimExplode(
                ',',
                $this->cObj->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.']),
                true
            );
            foreach ($files as $file) {
                $email->attach(\Swift_Attachment::fromPath($file));
            }
        }

        $email->send();

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
     * Get path and filename for mail template
     *
     * @param string $fileName
     * @return string
     */
    protected function getRelativeEmailPathAndFilename($fileName)
    {
        return TemplateUtility::getTemplatePath('Email/' . ucfirst($fileName) . '.html');
    }
}
