<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Domain\Model;

use In2code\Femanager\Domain\Model\User;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \In2code\Femanager\Domain\Model\User
 */
final class UserTest extends UnitTestCase
{
    private User $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new User();
    }

    /**
     * @test
     */
    public function getGenderInitiallyReturnsRatherNotSay(): void
    {
        self::assertSame(99, $this->subject->getGender());
    }

    /**
     * @test
     */
    public function setGenderSetsGender(): void
    {
        $value = 1;
        $this->subject->setGender($value);

        self::assertSame($value, $this->subject->getGender());
    }
}
