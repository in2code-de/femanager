<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Unit\Fixture\Utility\LogUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class LogUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\LogUtility
 */
class LogUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     * @covers ::getDispatcher
     */
    public function testGetDispatcher()
    {
        $this->expectExceptionCode(1459422492);
        LogUtility::getDispatcherPublic();
    }

    /**
     * @return void
     * @covers ::getLog
     */
    public function testGetLog()
    {
        $this->expectExceptionCode(1459422492);
        LogUtility::getLogPublic();
    }

    /**
     * @return void
     * @covers ::getLogRepository
     */
    public function testGetLogRepository()
    {
        $this->expectExceptionCode(1459422492);
        LogUtility::getLogRepositoryPublic();
    }
}
