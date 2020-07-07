<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FrontendUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
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
