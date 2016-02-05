<?php
namespace In2code\Femanager\Finisher;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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
 * SendParametersFinisher to send params via CURL
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SendParametersFinisher extends AbstractFinisher implements FinisherInterface
{

    /**
     * Inject a complete new content object
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @inject
     */
    protected $contentObject;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * TypoScript configuration part sendPost
     *
     * @var array
     */
    protected $configuration;

    /**
     * Initialize
     *
     * @return void
     */
    public function initializeFinisher()
    {
        $this->contentObject->start($this->user->_getProperties());
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $this->configuration = $typoScript['plugin.']['tx_femanager.']['settings.']['new.']['sendPost.'];
    }

    /**
     * Send values via curl to a third party software
     *
     * @return void
     */
    public function sendFinisher()
    {
        if ($this->isEnabled()) {
            $curlSettings = $this->getCurlSettings();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $curlSettings['url']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlSettings['params']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);
            $this->writeToDevelopmentLog();
        }
    }

    /**
     * Write devlog entry
     *
     * @return void
     */
    protected function writeToDevelopmentLog()
    {
        if (!empty($this->configuration['debug'])) {
            GeneralUtility::devLog('SendPost Values', 'femanager', 0, $this->getCurlSettings());
        }
    }

    /**
     * CURL settings
     *
     * @return array
     * @return void
     */
    protected function getCurlSettings()
    {
        return [
            'url' => $this->getTargetUrl(),
            'params' => $this->getData()
        ];
    }

    /**
     * Get parameters
     *
     * @return string
     */
    protected function getData()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['data'], $this->configuration['data.']);
    }

    protected function getTargetUrl()
    {
        $linkConfiguration = [
            'parameter' => $this->configuration['targetUrl'],
            'forceAbsoluteUrl' => '1',
            'returnLast' => 'url'
        ];
        return $this->contentObject->typoLink('dummy', $linkConfiguration);
    }

    /**
     * Check if sendPost is activated
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['_enable'], $this->configuration['_enable.'])
            === '1';
    }
}
