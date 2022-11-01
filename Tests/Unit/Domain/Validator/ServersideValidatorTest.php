<?php

namespace In2code\Femanager\Tests\Unit\Domain\Validator;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class AbstractValidatorTest
 * @coversDefaultClass \In2code\Femanager\Domain\Validator\ServersideValidator
 */
class ServersideValidatorTest extends UnitTestCase
{
    /**
     * @var \In2code\Femanager\Domain\Validator\ServersideValidator
     */
    protected $generalValidatorMock;

    /**
     * Make object available
     */
    public function setUp(): void
    {
        $this->generalValidatorMock = $this->getAccessibleMock(ServersideValidator::class, ['dummy']);
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

        $usergroup1 = $this->getUserGroupMock(1);
        $usergroup2 = $this->getUserGroupMock(2);

        $user->addUsergroup($usergroup1);
        $user->addUsergroup($usergroup2);

        $fieldName = 'usergroup';

        $result = $this->generalValidatorMock->_callRef('getValue', $user, $fieldName);

        self::assertSame('1,2', $result);
    }

    /**
     * @covers ::getValue
     */
    public function testGetValueForObject(): void
    {
        $user = new User('testuser');

        $fieldName = 'username';

        $result = $this->generalValidatorMock->_callRef('getValue', $user, $fieldName);

        self::assertSame('testuser', $result);
    }

    /**
     * @param int $uid
     * @return UserGroup|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getUserGroupMock(int $uid = 1)
    {
        $mockClass = $this->getMockBuilder(UserGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClass->method('getUid')->willReturn($uid);

        return $mockClass;
    }
}
