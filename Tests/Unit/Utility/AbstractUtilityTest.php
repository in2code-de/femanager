<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Tests\Unit\Fixture\Utility\AbstractUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
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
}
