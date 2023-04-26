<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Unit\Fixture\Utility\AbstractUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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
        self::assertSame($tca, AbstractUtility::getTcaFromTablePublic($table));
    }

    /**
     * @covers ::getFilesArray
     */
    public function testGetFilesArray()
    {
        $result = AbstractUtility::getFilesArrayPublic();
        self::assertTrue(is_array($result));
    }
}
