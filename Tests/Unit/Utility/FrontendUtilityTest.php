<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FrontendUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FrontendUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\FrontendUtility
 */
class FrontendUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     */
    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getCurrentPid
     */
    public function testGetCurrentPid()
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            123,
            1
        );
        $this->assertSame(123, FrontendUtility::getCurrentPid());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFrontendLanguageUid
     */
    public function testGetFrontendLanguageUid()
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            123,
            1
        );
        $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] = 2;
        $this->assertSame(2, FrontendUtility::getFrontendLanguageUid());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getCharset
     */
    public function testGetCharset()
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            123,
            1
        );
        $GLOBALS['TSFE']->metaCharset = 'abc';
        $this->assertSame('abc', FrontendUtility::getCharset());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getUriToCurrentPage
     * @covers ::getCurrentPid
     */
    public function testGetUriToCurrentPage()
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            123,
            1
        );
        $this->expectExceptionCode(1459422492);
        FrontendUtility::getUriToCurrentPage();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getControllerName
     */
    public function testGetControllerName()
    {
        $_POST['tx_femanager_pi1']['controller'] = 'foo';
        $this->assertSame('foo', FrontendUtility::getControllerName());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getActionName
     */
    public function testGetActionName()
    {
        $_POST['tx_femanager_pi1']['action'] = 'bar';
        $this->assertSame('bar', FrontendUtility::getActionName());
    }
}
