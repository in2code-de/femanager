<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\ObjectUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\ObjectUtility
 */
class ObjectUtilityTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @covers ::getQueryBuilder
     */
    public function testGetQueryBuilder()
    {
        $this->expectExceptionCode(1459422492);
        ObjectUtility::getQueryBuilder('tt_content');
    }

    /**
     * @covers ::implodeObjectStorageOnProperty
     */
    public function testImplodeObjectStorageOnProperty()
    {
        $objectStorage = new ObjectStorage();
        $user1 = new User();
        $user1->_setProperty('uid', 123);
        $objectStorage->attach($user1);
        $user2 = new User();
        $user2->_setProperty('uid', 852);
        $objectStorage->attach($user2);
        self::assertSame('123, 852', ObjectUtility::implodeObjectStorageOnProperty($objectStorage));
        self::assertSame('', ObjectUtility::implodeObjectStorageOnProperty($objectStorage, 'uidx'));
    }
}
