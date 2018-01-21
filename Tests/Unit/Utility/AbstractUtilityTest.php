<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Tests\Unit\Fixture\Utility\AbstractUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AbstractUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\AbstractUtility
 */
class AbstractUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getTcaFromTable
     */
    public function testGetTcaFromTable()
    {
        $table = 'tx_test';
        $tca = [
            'test' => [
                'foo'
            ]
        ];
        $GLOBALS['TCA'][$table] = $tca;
        $this->assertSame($tca, AbstractUtility::getTcaFromTablePublic($table));
    }

    /**
     * @return void
     * @covers ::getFilesArray
     */
    public function testGetFilesArray()
    {
        $result = AbstractUtility::getFilesArrayPublic();
        $this->assertTrue(is_array($result));
    }

    /**
     * @return void
     * @covers ::getUserGroupRepository
     */
    public function testGetUserGroupRepository()
    {
        $this->expectExceptionCode(1459422492);
        AbstractUtility::getUserGroupRepositoryPublic();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getTypoScriptFrontendController
     */
    public function testGetTypoScriptFrontendController()
    {
        TestingHelper::setDefaultConstants();
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            123,
            1
        );
        $this->assertInstanceOf(
            TypoScriptFrontendController::class,
            AbstractUtility::getTypoScriptFrontendControllerPublic()
        );
    }

    /**
     * @return void
     * @covers ::getContentObject
     */
    public function testGetContentObject()
    {
        $this->expectExceptionCode(1459422492);
        AbstractUtility::getContentObjectPublic();
    }

    /**
     * @return void
     * @covers ::getConfigurationManager
     */
    public function testGetConfigurationManager()
    {
        $this->expectExceptionCode(1459422492);
        AbstractUtility::getConfigurationManagerPublic();
    }
}
