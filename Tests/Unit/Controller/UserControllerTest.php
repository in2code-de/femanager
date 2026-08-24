<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Controller;

use In2code\Femanager\Controller\UserController;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Finisher\FinisherRunner;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LogUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * @coversDefaultClass \In2code\Femanager\Controller\UserController
 */
class UserControllerTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private UserRepository $userRepository;
    private TestableUserController $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'unit-test-key';
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->subject = new TestableUserController(
            $this->userRepository,
            self::createStub(UserGroupRepository::class),
            self::createStub(PersistenceManager::class),
            self::createStub(SendMailService::class),
            self::createStub(FinisherRunner::class),
            self::createStub(LogUtility::class)
        );
    }

    /** @covers ::getUser */
    public function testConfiguredUserCannotBeOverriddenByRequestArgument(): void
    {
        $configuredUser = new User();
        $requestedUser = new User();
        $this->userRepository->expects(self::once())->method('findByUid')->with('12')->willReturn($configuredUser);
        $this->subject->setControllerSettings(['show' => ['user' => '12']]);

        self::assertSame($configuredUser, $this->subject->resolveUser($requestedUser));
    }

    /** @covers ::getUser */
    public function testCurrentUserCannotBeOverriddenByRequestArgument(): void
    {
        $currentUser = new User();
        $requestedUser = new User();
        $this->subject->setControllerSettings(['show' => ['user' => '[this]']]);
        $this->subject->setCurrentUser($currentUser);

        self::assertSame($currentUser, $this->subject->resolveUser($requestedUser));
    }

    /** @covers ::getUser */
    public function testListUserIsAcceptedWithValidHash(): void
    {
        $user = new User();
        $user->setUsername('allowed-user');
        $this->subject->setControllerSettings([]);
        $hash = HashUtility::createHashForUser($user, 'show');

        self::assertSame($user, $this->subject->resolveUser($user, $hash));
    }

    /** @covers ::getUser */
    public function testListUserIsRejectedWithoutValidHash(): void
    {
        $user = new User();
        $user->setUsername('requested-user');
        $this->subject->setControllerSettings([]);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionCode(1754916601);
        $this->subject->resolveUser($user);
    }
}

class TestableUserController extends UserController
{
    public function setControllerSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function setCurrentUser(User $user): void
    {
        $this->user = $user;
    }

    public function resolveUser(?User $user = null, string $hash = ''): ?User
    {
        return $this->getUser($user, $hash);
    }
}
