<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Service\UserGroupSanitizationService;
use In2code\Femanager\Utility\LogUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UserGroupSanitizationService::class)]
class UserGroupSanitizationServiceTest extends UnitTestCase
{
    protected UserGroupRepository&MockObject $userGroupRepository;
    protected LogUtility&MockObject $logUtility;
    protected UserGroupSanitizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userGroupRepository = $this->createMock(UserGroupRepository::class);
        $this->userGroupRepository->method('findByUid')->willReturnCallback(
            fn ($uid): UserGroup => $this->createUserGroup((int)$uid)
        );
        $this->logUtility = $this->createMock(LogUtility::class);
        $this->service = new UserGroupSanitizationService($this->userGroupRepository, $this->logUtility);
    }

    #[Test]
    public function unchangedSelectionIsKeptWithoutLog(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([1]);

        $result = $this->service->sanitize($user, [], [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    #[Test]
    public function changeIsRevertedWhenFieldIsNotEditable(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2]);
        $settings = ['fields' => 'username,email'];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    #[Test]
    public function valueInAllowListIsKeptWithoutLog(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([2]);
        $settings = ['validation' => ['usergroup' => ['inList' => '1,2,3']]];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([2], $this->usergroupUids($result));
    }

    #[Test]
    public function valuesNotInAllowListAreStrippedAndLogged(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2, 4]);
        $settings = ['validation' => ['usergroup' => ['inList' => '1,2,3']]];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([2], $this->usergroupUids($result));
    }

    #[Test]
    public function changeIsRevertedWhenNoAllowListAndNoOptInIsConfigured(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2]);

        $result = $this->service->sanitize($user, [], [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    #[Test]
    public function changeIsKeptWhenUnrestrictedSelectionIsOptedIn(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([2]);
        $settings = ['misc' => ['allowUnrestrictedUserGroupSelection' => '1']];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([2], $this->usergroupUids($result));
    }

    #[Test]
    public function selectionIsUntouchedWhenGroupsAreForced(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([2]);
        $settings = ['overrideUserGroup' => '5'];

        $result = $this->service->sanitize($user, $settings, []);

        self::assertSame([2], $this->usergroupUids($result));
    }

    #[Test]
    public function registrationStripsSelectionWhenNothingIsConfigured(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([3]);

        $result = $this->service->sanitize($user, [], []);

        self::assertSame([], $this->usergroupUids($result));
    }

    public static function fieldRenderModeDataProvider(): array
    {
        return [
            'forced groups are hidden' => [
                ['overrideUserGroup' => '5'],
                UserGroupSanitizationService::FIELD_MODE_HIDDEN,
            ],
            'allow list enables selection' => [
                ['validation' => ['usergroup' => ['inList' => '1,2']]],
                UserGroupSanitizationService::FIELD_MODE_SELECT,
            ],
            'opt in enables selection' => [
                ['misc' => ['allowUnrestrictedUserGroupSelection' => '1']],
                UserGroupSanitizationService::FIELD_MODE_SELECT,
            ],
            'nothing configured shows notice' => [
                [],
                UserGroupSanitizationService::FIELD_MODE_NOTICE,
            ],
        ];
    }

    #[Test]
    #[DataProvider('fieldRenderModeDataProvider')]
    public function fieldRenderModeReflectsConfiguration(array $formSettings, string $expectedMode): void
    {
        self::assertSame($expectedMode, $this->service->getFieldRenderMode($formSettings));
    }

    #[Test]
    public function noticeModeLogsConfigurationWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $this->service->setLogger($logger);

        self::assertSame(
            UserGroupSanitizationService::FIELD_MODE_NOTICE,
            $this->service->getFieldRenderMode([])
        );
    }

    #[Test]
    public function selectModeDoesNotLogWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');
        $this->service->setLogger($logger);

        $settings = ['validation' => ['usergroup' => ['inList' => '1,2']]];
        self::assertSame(
            UserGroupSanitizationService::FIELD_MODE_SELECT,
            $this->service->getFieldRenderMode($settings)
        );
    }

    #[Test]
    public function originalUsergroupUidsAreEmptyForNewUser(): void
    {
        self::assertSame([], $this->service->getOriginalUsergroupUids($this->createUser([2])));
    }

    #[Test]
    public function originalUsergroupUidsAreResolvedFromCleanState(): void
    {
        $user = $this->createUser([3, 1]);
        $user->_memorizeCleanState();
        $user->setUsergroup(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());

        self::assertSame([1, 3], $this->service->getOriginalUsergroupUids($user));
    }

    /**
     * @param int[] $usergroupUids
     */
    private function createUser(array $usergroupUids): User
    {
        $user = new User();
        foreach ($usergroupUids as $uid) {
            $user->addUsergroup($this->createUserGroup($uid));
        }

        return $user;
    }

    private function createUserGroup(int $uid): UserGroup
    {
        $userGroup = new UserGroup();
        $userGroup->_setProperty('uid', $uid);

        return $userGroup;
    }

    /**
     * @return int[]
     */
    private function usergroupUids(User $user): array
    {
        $uids = [];
        foreach ($user->getUsergroup() as $userGroup) {
            $uids[] = (int)$userGroup->getUid();
        }
        sort($uids);

        return $uids;
    }
}
