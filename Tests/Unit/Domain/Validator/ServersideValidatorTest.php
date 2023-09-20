<?php

namespace In2code\Femanager\Tests\Unit\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AbstractValidatorTest
 * @coversDefaultClass \In2code\Femanager\Domain\Validator\ServersideValidator
 */
class ServersideValidatorTest extends UnitTestCase
{
    protected ServersideValidator|AccessibleObjectInterface|MockObject $generalValidatorMock;

    /**
     * Make object available
     */
    public function setUp(): void
    {
        $this->generalValidatorMock = $this->getAccessibleMock(
            ServersideValidator::class,
            null
        );
    }

    /**
     * Remove object
     */
    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    /**
     * @covers ::getValue
     */
    public function testGetValueForObjectStorage(): void
    {
        $user = new User();

        $usergroup1 = $this->getUserGroupMock();
        $usergroup2 = $this->getUserGroupMock(2);

        $user->addUsergroup($usergroup1);
        $user->addUsergroup($usergroup2);

        $fieldName = 'usergroup';

        $result = $this->generalValidatorMock->_call('getValue', $user, $fieldName);

        self::assertSame('1,2', $result);
    }

    /**
     * @covers ::getValue
     */
    public function testGetValueForObject(): void
    {
        $user = new User('testuser');

        $fieldName = 'username';

        $result = $this->generalValidatorMock->_call('getValue', $user, $fieldName);

        self::assertSame('testuser', $result);
    }

    protected function getUserGroupMock(int $uid = 1): UserGroup|MockObject
    {
        $mockClass = $this->getMockBuilder(UserGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClass->method('getUid')->willReturn($uid);

        return $mockClass;
    }
}
