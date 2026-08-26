<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Domain\Service;

use In2code\Femanager\Domain\Model\Log;
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

    #[Test]
    public function fieldNotEditableWinsOverAllowList(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2]);
        $settings = [
            'fields' => 'username,email',
            'validation' => ['usergroup' => ['inList' => '1,2,3']],
        ];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    #[Test]
    public function allowListWinsOverUnrestrictedOptIn(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([3]);
        $settings = [
            'validation' => ['usergroup' => ['inList' => '1,2']],
            'misc' => ['allowUnrestrictedUserGroupSelection' => '1'],
        ];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([], $this->usergroupUids($result));
    }

    #[Test]
    public function forcedGroupsWinOverAllowList(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([9]);
        $settings = [
            'overrideUserGroup' => '5',
            'validation' => ['usergroup' => ['inList' => '1,2']],
        ];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([9], $this->usergroupUids($result));
    }

    public static function emptyOverrideValueDataProvider(): array
    {
        return [
            'string zero' => ['0'],
            'empty string' => [''],
            'integer zero' => [0],
            'null' => [null],
        ];
    }

    /**
     * An empty "overrideUserGroup" must not be mistaken for a configured one, otherwise the whole
     * sanitization would be skipped and any submitted usergroup would be persisted.
     */
    #[Test]
    #[DataProvider('emptyOverrideValueDataProvider')]
    public function overrideUserGroupDoesNotDisableSanitizationWhenEmpty(mixed $override): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2]);
        $settings = ['overrideUserGroup' => $override];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    public static function optInValueDataProvider(): array
    {
        return [
            'string one enables' => ['1', true],
            'integer one enables' => [1, true],
            'boolean true enables' => [true, true],
            'string zero does not enable' => ['0', false],
            'empty string does not enable' => ['', false],
            'padded one does not enable' => [' 1', false],
            'leading zero does not enable' => ['01', false],
            'arbitrary string does not enable' => ['yes', false],
        ];
    }

    #[Test]
    #[DataProvider('optInValueDataProvider')]
    public function optInIsOnlyEffectiveForExactStringOne(mixed $optInValue, bool $expectKept): void
    {
        $this->logUtility->expects($expectKept ? self::never() : self::once())->method('log');
        $user = $this->createUser([2]);
        $settings = ['misc' => ['allowUnrestrictedUserGroupSelection' => $optInValue]];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame($expectKept ? [2] : [1], $this->usergroupUids($result));
    }

    public static function allowListDataProvider(): array
    {
        return [
            'whitespace is trimmed' => ['1, 2 ,3', [2], false],
            'empty segments are skipped' => ['1,,2', [2], false],
            'empty list is no allow list' => ['', [1], true],
            'zero is a literal allow list' => ['0', [], true],
            'non numeric values strip everything' => ['a,b', [], true],
        ];
    }

    /**
     * @param int[] $expectedUids
     */
    #[Test]
    #[DataProvider('allowListDataProvider')]
    public function allowListIsNormalizedFromTypoScriptString(
        string $inList,
        array $expectedUids,
        bool $expectLog
    ): void {
        $this->logUtility->expects($expectLog ? self::once() : self::never())->method('log');
        $user = $this->createUser([2]);
        $settings = ['validation' => ['usergroup' => ['inList' => $inList]]];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame($expectedUids, $this->usergroupUids($result));
    }

    public static function fieldsDataProvider(): array
    {
        return [
            'usergroup listed with whitespace' => ['username, usergroup , email', [2], false],
            'empty field list means editable' => ['', [2], false],
            'usergroup not listed' => ['username,email', [1], true],
        ];
    }

    /**
     * @param int[] $expectedUids
     */
    #[Test]
    #[DataProvider('fieldsDataProvider')]
    public function fieldEditabilityIsDerivedFromFieldsList(
        string $fields,
        array $expectedUids,
        bool $expectLog
    ): void {
        $this->logUtility->expects($expectLog ? self::once() : self::never())->method('log');
        $user = $this->createUser([2]);
        $settings = [
            'fields' => $fields,
            'validation' => ['usergroup' => ['inList' => '1,2,3']],
        ];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame($expectedUids, $this->usergroupUids($result));
    }

    #[Test]
    public function reorderedSelectionIsNotTreatedAsChange(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([3, 1]);

        $result = $this->service->sanitize($user, [], [1, 3]);

        self::assertSame([1, 3], $this->usergroupUids($result));
    }

    /**
     * Duplicates are irrelevant for the comparison, so the relation is left untouched - including
     * the duplicate entry itself.
     */
    #[Test]
    public function duplicateSubmittedGroupsAreNotTreatedAsChange(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([2, 2]);

        $result = $this->service->sanitize($user, [], [2]);

        self::assertSame([2, 2], $this->usergroupUids($result));
    }

    #[Test]
    public function clearingGroupsIsRevertedWhenNothingIsConfigured(): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([]);

        $result = $this->service->sanitize($user, [], [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    /**
     * With an allow list a user may drop groups - only escalating to a group outside the list is
     * rejected. This pins the current behaviour so it is not changed unnoticed.
     */
    #[Test]
    public function clearingGroupsIsAllowedWithAllowList(): void
    {
        $this->logUtility->expects(self::never())->method('log');
        $user = $this->createUser([]);
        $settings = ['validation' => ['usergroup' => ['inList' => '1,2,3']]];

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([], $this->usergroupUids($result));
    }

    #[Test]
    public function revertedGroupIsDroppedWhenItNoLongerExists(): void
    {
        $userGroupRepository = $this->createMock(UserGroupRepository::class);
        $userGroupRepository->method('findByUid')->willReturn(null);
        $logUtility = $this->createMock(LogUtility::class);
        $logUtility->expects(self::once())->method('log');
        $service = new UserGroupSanitizationService($userGroupRepository, $logUtility);
        $user = $this->createUser([2]);

        $result = $service->sanitize($user, [], [1]);

        self::assertSame([], $this->usergroupUids($result));
    }

    #[Test]
    public function logUsesProfileUpdateNotAuthorizedStatus(): void
    {
        $user = $this->createUser([2]);
        $this->logUtility
            ->expects(self::once())
            ->method('log')
            ->with(Log::STATUS_PROFILEUPDATENOTAUTHORIZED, $user);

        $this->service->sanitize($user, [], [1]);
    }

    public static function malformedSettingsDataProvider(): array
    {
        return [
            'misc is not an array' => [['misc' => 'x']],
            'validation is not an array' => [['validation' => 'x']],
            'usergroup node without inList' => [['validation' => ['usergroup' => []]]],
        ];
    }

    #[Test]
    #[DataProvider('malformedSettingsDataProvider')]
    public function malformedSettingsFallBackToFailClosed(array $settings): void
    {
        $this->logUtility->expects(self::once())->method('log');
        $user = $this->createUser([2]);

        $result = $this->service->sanitize($user, $settings, [1]);

        self::assertSame([1], $this->usergroupUids($result));
    }

    /**
     * Resources/Private/Partials/Fields/Usergroup.html switches on the plain strings, so renaming a
     * constant value would silently break the template.
     */
    #[Test]
    public function fieldModeConstantsMatchTemplateLiterals(): void
    {
        self::assertSame('select', UserGroupSanitizationService::FIELD_MODE_SELECT);
        self::assertSame('hidden', UserGroupSanitizationService::FIELD_MODE_HIDDEN);
        self::assertSame('notice', UserGroupSanitizationService::FIELD_MODE_NOTICE);
    }

    #[Test]
    public function fieldRenderModeIgnoresFieldsSetting(): void
    {
        self::assertSame(
            UserGroupSanitizationService::FIELD_MODE_NOTICE,
            $this->service->getFieldRenderMode(['fields' => 'username'])
        );
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
