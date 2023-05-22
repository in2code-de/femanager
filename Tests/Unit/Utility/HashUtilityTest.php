<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\HashUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class HashUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\HashUtility
 */
class HashUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->user->setUsername('foo');
    }

    /**
     * @covers \In2code\Femanager\Utility\AbstractUtility::getEncryptionKey
     */
    public function testEncryptionKey()
    {
        $this->expectExceptionCode(1516373945265);
        HashUtility::createHashForUser($this->user);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::validHash
     */
    public function testValidHash()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'abc';
        self::assertTrue(HashUtility::validHash('715e5634c193bbe4', $this->user));
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::createHashForUser
     * @covers ::hashString
     * @covers \In2code\Femanager\Utility\AbstractUtility::getEncryptionKey
     */
    public function testCreateHashForUser()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'abc';
        $hash = HashUtility::createHashForUser($this->user);
        self::assertSame('715e5634c193bbe4', $hash);
    }
}
