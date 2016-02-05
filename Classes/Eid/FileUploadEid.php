<?php
namespace In2code\Femanager\Eid;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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
 * This class could called with AJAX via eID
 *
 * @author Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 * @package TYPO3
 * @subpackage EidFileUpload
 */
class FileUploadEid
{

    /**
     * configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * bootstrap
     *
     * @var array
     */
    protected $bootstrap;

    /**
     * Generates the output
     *
     * @return string from action
     */
    public function run()
    {
        return $this->bootstrap->run('', $this->configuration);
    }

    /**
     * Initialize Extbase
     *
     * @param array $typo3ConfVars
     */
    public function __construct($typo3ConfVars)
    {
        $this->configuration = [
            'pluginName' => 'Pi1',
            'vendorName' => 'In2code',
            'extensionName' => 'Femanager',
            'controller' => 'User',
            'action' => 'fileUpload',
            'mvc' => [
                'requestHandlers' => [
                    'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler' =>
                        'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler'
                ]
            ],
            'settings' => []
        ];
        $_POST['tx_femanager_pi1']['action'] = 'fileUpload';
        $_POST['tx_femanager_pi1']['controller'] = 'User';

        $this->bootstrap = new Bootstrap();

        $userObj = EidUtility::initFeUser();
        $pid = (GeneralUtility::_GET('id') ? GeneralUtility::_GET('id') : 1);
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $typo3ConfVars,
            $pid,
            0,
            true
        );
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->fe_user = $userObj;
        $GLOBALS['TSFE']->id = $pid;
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
    }
}

$eid = GeneralUtility::makeInstance(FileUploadEid::class, $GLOBALS['TYPO3_CONF_VARS']);
echo $eid->run();
