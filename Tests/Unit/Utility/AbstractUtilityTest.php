<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Tests\Unit\Fixture\Utility\AbstractUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AbstractUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\AbstractUtility
 */
class AbstractUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getTcaFromTable
     */
    public function testGetTcaFromTable(): void
    {
        $table = 'tx_test';
        $tca = [
            'test' => [
                'foo',
            ],
        ];
        $GLOBALS['TCA'][$table] = $tca;
        self::assertSame($tca, AbstractUtility::getTcaFromTablePublic($table));
    }

    /**
     * @covers ::getFilesArray
     */
    public function testGetFilesArray(): void
    {
        $result = AbstractUtility::getFilesArrayPublic();
        self::assertTrue(is_array($result));
    }
}
