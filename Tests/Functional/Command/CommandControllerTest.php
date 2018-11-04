<?php

use In2code\Femanager\Command\TaskCommandController;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;


class CommandControllerTest extends \Nimut\TestingFramework\TestCase\FunctionalTestCase
{

    /**
     * @var \In2code\Femanager\Command\TaskCommandController
     */
    protected $commandControllerMock;

    protected $testExtensionsToLoad = ['typo3conf/ext/femanager'];


    /**
     * Make object available
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->commandControllerMock = $this->getAccessibleMock(TaskCommandController::class, ['dummy']);
        $now = 1540800000; //Monday, October 29th 2018, 9 am CEST
        $this->commandControllerMock->_set('currentTime', $now);


        $this->importDataSet(__DIR__ . '/../Fixture/fe_users.xml');
    }

    /**
     * Dataprovider for taskDeletesUnconfirmedUsers()
     *
     * @return array
     */
    public function deleteUnconfirmedUsersDataProvider()
    {
        return [
            [
                0,
                [1, 2, 3]
            ],
            [
                2,
                [1, 2, 3, 4]
            ],
            [
                180,
                [1, 2, 3, 4, 5]
            ],
            [
                1800,
                [1, 2, 3, 4, 5, 6]
            ],
        ];
    }


    /**
     *
     * @test
     * @param int $period
     * @param array $expectedResult
     * @return void
     * @dataProvider deleteUnconfirmedUsersDataProvider
     *
     */

    public function taskDeletesUnconfirmedUsers($period, $expectedResult)
    {
        $this->commandControllerMock->cleanUsersThatDidNotConfirmCommand($period);
        $actualResult = $this->findUserUidsforComparison();
        $this->assertSame($expectedResult, $actualResult);
    }


    /**
     *
     *
     * @return array
     */

    public function findUserUidsforComparison()
    {
        $queryBuilder = ObjectUtility::getQueryBuilder('fe_users');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $users = $queryBuilder
            ->select('uid')
            ->from('fe_users')
            ->execute()
            ->fetchAll();
        $finalUsers = [];
        foreach ($users as $user) {
            $finalUsers[] = $user['uid'];
        }
        return $finalUsers;
    }

    public function tearDown()
    {
        unset($this->commandControllerMock);
    }
}