<?php
namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\HashUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class HashUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\HashUtility
 */
class HashUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @var User
     */
    protected $user = null;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->user = new User();
        $this->user->setUsername('foo');
    }

    /**
     * @return void
     * @covers \In2code\Femanager\Utility\AbstractUtility::getEncryptionKey
     */
    public function testEncryptionKey()
    {
        $this->expectExceptionCode(1516373945265);
        HashUtility::createHashForUser($this->user);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::validHash
     */
    public function testValidHash()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'abc';
        $this->assertTrue(HashUtility::validHash('715e5634c193bbe4', $this->user));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::createHashForUser
     * @covers ::hashString
     * @covers \In2code\Femanager\Utility\AbstractUtility::getEncryptionKey
     */
    public function testCreateHashForUser()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'abc';
        $hash = HashUtility::createHashForUser($this->user);
        $this->assertSame('715e5634c193bbe4', $hash);
    }
}
