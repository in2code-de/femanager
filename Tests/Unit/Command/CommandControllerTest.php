<?php

namespace In2code\Femanager\Command;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CommandControllerTest
 * @coversDefaultClass \In2code\Femanager\Command\TaskCommandController
 */
class TaskCommandControllerTest extends UnitTestCase
{

    /**
     * @var \In2code\Femanager\Command\TaskCommandController
     */
    protected $commandControllerMock;

    /**
     * Make object available
     * @return void
     */
    public function setUp()
    {
        $this->commandControllerMock = $this->getAccessibleMock(TaskCommandController::class, ['dummy']);
    }

    /**
     * Dataprovider for testGetCompareTime()
     *
     * @return array
     */
    public function getCompareTimeDataProvider()
    {
        return [
            [
                0,
                1000000
            ],
            [
                1,
                913600
            ],
            [
                2,
                827200
            ],
        ];
    }

    /**
     * @test
     * @param int $value
     * @param int $expectedResult
     * @return void
     * @dataProvider getCompareTimeDataProvider
     * @covers ::getCompareTime
     */
    public function testGetCompareTime($value, $expectedResult)
    {
        $this->commandControllerMock->_set('currentTime', 1000000);
        $this->assertSame($expectedResult, $this->commandControllerMock->_call('getCompareTime', $value));
    }


    public function tearDown()
    {
        unset($this->commandControllerMock);
    }
}
