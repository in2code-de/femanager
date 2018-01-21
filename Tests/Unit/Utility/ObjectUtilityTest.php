<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
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
     * @return void
     * @covers ::getQueryBuilder
     */
    public function testGetQueryBuilder()
    {
        $this->expectExceptionCode(1459422492);
        ObjectUtility::getQueryBuilder('tt_content');
    }

    /**
     * @return void
     * @covers ::getObjectManager
     * @covers \In2code\Femanager\Utility\AbstractUtility::getObjectManager
     */
    public function testGetObjectManager()
    {
        $this->assertInstanceOf(ObjectManagerInterface::class, ObjectUtility::getObjectManager());
    }

    /**
     * @return void
     * @covers ::getContentObject
     */
    public function testGetContentObject()
    {
        $this->expectExceptionCode(1459422492);
        ObjectUtility::getContentObject();
    }

    /**
     * @return void
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
        $this->assertSame('123, 852', ObjectUtility::implodeObjectStorageOnProperty($objectStorage));
        $this->assertSame('', ObjectUtility::implodeObjectStorageOnProperty($objectStorage, 'uidx'));
    }
}
