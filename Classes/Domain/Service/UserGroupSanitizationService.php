<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Utility\LogUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Enforces a server-side allowlist for the usergroup relation submitted through the
 * frontend forms (new, edit, invitation).
 *
 * The frontend templates and the dropdown are not a security boundary: a crafted request can
 * submit any usergroup uid regardless of what is rendered. This service is therefore the single
 * place that decides which usergroup(s) a frontend user is actually allowed to assign to itself.
 *
 * Behaviour (secure by default / fail closed):
 * - "overrideUserGroup" configured  => groups are fully admin-controlled, nothing to sanitize.
 * - usergroup field not editable     => any submitted change is reverted to the original groups.
 * - "validation.usergroup.inList" set => submitted uids are reduced to that allowlist.
 * - no allowlist + opt-in flag set    => unrestricted selection (legacy behaviour).
 * - no allowlist + no opt-in          => submitted change is reverted (fail closed).
 *
 * Every reverted or reduced submission is logged with {@see Log::STATUS_PROFILEUPDATENOTAUTHORIZED}.
 */
class UserGroupSanitizationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Render the usergroup selection (allowlist or opt-in is configured).
     */
    public const FIELD_MODE_SELECT = 'select';

    /**
     * Hide the usergroup field (groups are forced via "overrideUserGroup").
     */
    public const FIELD_MODE_HIDDEN = 'hidden';

    /**
     * Show a configuration notice instead of the field (no allowlist and no opt-in).
     */
    public const FIELD_MODE_NOTICE = 'notice';

    private const FIELD_NAME = 'usergroup';
    private const SETTINGS_FIELDS = 'fields';
    private const SETTINGS_OVERRIDE = 'overrideUserGroup';
    private const SETTINGS_VALIDATION_INLIST = ['validation', 'usergroup', 'inList'];
    private const SETTINGS_OPT_IN = ['misc', 'allowUnrestrictedUserGroupSelection'];

    public function __construct(
        private readonly UserGroupRepository $userGroupRepository,
        private readonly LogUtility $logUtility,
    ) {
    }

    /**
     * @param array $formSettings Settings of the current form (e.g. $settings['edit']).
     * @param int[] $originalUsergroupUids Usergroup uids the user is allowed to keep (persisted state).
     */
    public function sanitize(User $user, array $formSettings, array $originalUsergroupUids): User
    {
        if ($this->isUserGroupForced($formSettings)) {
            return $user;
        }

        $submittedUids = $this->extractUsergroupUids($user);
        $originalUids = $this->normalizeUids($originalUsergroupUids);
        if ($submittedUids === $originalUids) {
            return $user;
        }

        $allowedUids = $this->determineAllowedUids($submittedUids, $originalUids, $formSettings);
        if ($allowedUids === null || $allowedUids === $submittedUids) {
            return $user;
        }

        $this->applyUsergroupUids($user, $allowedUids);
        $this->logUtility->log(Log::STATUS_PROFILEUPDATENOTAUTHORIZED, $user);
        return $user;
    }

    /**
     * Decides how the usergroup field should be presented in the frontend form. The template is not
     * a security boundary (see {@see sanitize()}); this only avoids offering a control that the
     * server would ignore anyway.
     *
     * @param array $formSettings Settings of the current form (e.g. $settings['edit']).
     * @return self::FIELD_MODE_*
     */
    public function getFieldRenderMode(array $formSettings): string
    {
        if ($this->isUserGroupForced($formSettings)) {
            return self::FIELD_MODE_HIDDEN;
        }

        if ($this->getAllowList($formSettings) !== []
            || $this->isUnrestrictedSelectionAllowed($formSettings)
        ) {
            return self::FIELD_MODE_SELECT;
        }

        $this->logger?->warning(
            'femanager: a usergroup field is offered in a frontend form, but no allowed usergroups '
            . 'are configured. Set "validation.usergroup.inList" to an allowlist or enable '
            . '"misc.allowUnrestrictedUserGroupSelection" for that form. The selection is disabled.'
        );

        return self::FIELD_MODE_NOTICE;
    }

    /**
     * Resolves the persisted usergroup uids of a mapped user from its clean (pre-request) state.
     * For newly created users this is an empty list.
     *
     * @return int[]
     */
    public function getOriginalUsergroupUids(User $user): array
    {
        $cleanUsergroup = $user->_getCleanProperties()[self::FIELD_NAME] ?? null;
        if (!$cleanUsergroup instanceof ObjectStorage) {
            return [];
        }

        $uids = [];
        foreach ($cleanUsergroup as $usergroup) {
            $uids[] = (int)$usergroup->getUid();
        }

        return $this->normalizeUids($uids);
    }

    /**
     * Returns the usergroup uids the user is allowed to end up with, or null when the submitted
     * selection is fully allowed and must not be changed.
     *
     * @param int[] $submittedUids
     * @param int[] $originalUids
     * @return int[]|null
     */
    private function determineAllowedUids(
        array $submittedUids,
        array $originalUids,
        array $formSettings
    ): ?array {
        if ($this->isFieldEditable($formSettings) === false) {
            return $originalUids;
        }

        $allowList = $this->getAllowList($formSettings);
        if ($allowList !== []) {
            return array_values(array_intersect($submittedUids, $allowList));
        }

        if ($this->isUnrestrictedSelectionAllowed($formSettings)) {
            return null;
        }

        return $originalUids;
    }

    private function isUserGroupForced(array $formSettings): bool
    {
        return !empty($formSettings[self::SETTINGS_OVERRIDE]);
    }

    private function isFieldEditable(array $formSettings): bool
    {
        $fields = (string)($formSettings[self::SETTINGS_FIELDS] ?? '');
        if ($fields === '') {
            return true;
        }

        return in_array(self::FIELD_NAME, GeneralUtility::trimExplode(',', $fields, true), true);
    }

    /**
     * @return int[]
     */
    private function getAllowList(array $formSettings): array
    {
        $allowList = $this->getNestedSetting($formSettings, self::SETTINGS_VALIDATION_INLIST);
        return $this->normalizeUids(GeneralUtility::intExplode(',', (string)$allowList, true));
    }

    private function isUnrestrictedSelectionAllowed(array $formSettings): bool
    {
        return (string)$this->getNestedSetting($formSettings, self::SETTINGS_OPT_IN) === '1';
    }

    private function getNestedSetting(array $formSettings, array $path): mixed
    {
        $value = $formSettings;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @return int[]
     */
    private function extractUsergroupUids(User $user): array
    {
        $uids = [];
        foreach ($user->getUsergroup() as $usergroup) {
            $uids[] = (int)$usergroup->getUid();
        }

        return $this->normalizeUids($uids);
    }

    /**
     * @param int[] $uids
     */
    private function applyUsergroupUids(User $user, array $uids): void
    {
        $user->removeAllUsergroups();
        foreach ($uids as $uid) {
            /** @var UserGroup|null $usergroup */
            $usergroup = $this->userGroupRepository->findByUid($uid);
            if ($usergroup instanceof UserGroup) {
                $user->addUsergroup($usergroup);
            }
        }
    }

    /**
     * Sort and deduplicate uids so two selections can be compared as sets.
     *
     * @param array<int|string> $uids
     * @return int[]
     */
    private function normalizeUids(array $uids): array
    {
        $uids = array_values(array_unique(array_map('intval', $uids)));
        sort($uids);

        return $uids;
    }
}
