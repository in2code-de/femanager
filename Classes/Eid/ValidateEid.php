<?php
declare(strict_types=1);
namespace In2code\Femanager\Eid;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Class ValidateEid
 */
class ValidateEid
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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($typo3ConfVars)
    {
        $this->configuration = [
            'pluginName' => 'Pi1',
            'vendorName' => 'In2code',
            'extensionName' => 'Femanager',
            'controller' => 'User',
            'action' => 'validate',
            'mvc' => [
                'requestHandlers' => [
                    'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler' =>
                        'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler'
                ]
            ],
            'settings' => [],
            'persistence' => [
                'storagePid' => GeneralUtility::_GP('storagePid')
            ]
        ];
        $_POST['tx_femanager_pi1']['action'] = 'validate';
        $_POST['tx_femanager_pi1']['controller'] = 'User';

        $this->bootstrap = new Bootstrap();

        $userObj = EidUtility::initFeUser();
        $pid = (GeneralUtility::_GP('id') ? GeneralUtility::_GP('id') : 1);
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

$eid = GeneralUtility::makeInstance(ValidateEid::class, $GLOBALS['TYPO3_CONF_VARS']);
echo $eid->run();
