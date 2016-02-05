<?php
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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
 * Send Parameters Service Class
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SendParametersService
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * SendPost - Send values via curl to target
     *
     * @param User $user User properties
     * @return void
     */
    public function send(User $user)
    {
        $this->initialize($user);
        $this->contentObject->start($this->properties);
        if ($this->isTurnedOn()) {
            $curlObject = curl_init();
            curl_setopt($curlObject, CURLOPT_URL, $this->getUri());
            curl_setopt($curlObject, CURLOPT_POST, 1);
            curl_setopt($curlObject, CURLOPT_POSTFIELDS, $this->getData());
            curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curlObject);
            curl_close($curlObject);
            $this->log();
        }
    }

    /**
     * Get URI for curl request
     *
     * @return string
     */
    protected function getUri()
    {
        return $this->configuration['targetUrl'];
    }

    /**
     * Get data array
     *
     * @return string
     */
    protected function getData()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['data'], $this->configuration['data.']);
    }

    /**
     * Write to devlog
     *
     * @return bool
     */
    protected function log()
    {
        if (!empty($this->configuration['debug'])) {
            GeneralUtility::devLog(
                'femanager sendpost values',
                'femanager',
                0,
                [
                    'url' => $this->getUri(),
                    'data' => $this->getData(),
                    'properties' => $this->properties
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function isTurnedOn()
    {
        return $this->contentObject->cObjGetSingle($this->configuration['_enable'], $this->configuration['_enable.'])
        === '1';
    }

    /**
     * Initialize
     *
     * @param User $user
     * @return void
     */
    protected function initialize(User $user)
    {
        $this->properties = $user->_getCleanProperties();
        $this->contentObject = $this->configurationManager->getContentObject();
    }
}
