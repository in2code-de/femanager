<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use Exception;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUtility extends AbstractUtility
{
    /**
     * Set object properties from forceValues in TypoScript
     *
     * @throws Exception
     * @codeCoverageIgnore
     */
    public static function forceValues(User $user, array $settings): User
    {
        foreach ($settings as $field => $config) {
            $config = null;
            if (stristr($field, '.')) {
                continue;
            }

            // value to set
            $value = self::getContentObject()->cObjGetSingle($settings[$field], $settings[$field . '.']);
            self::forceValue($user, $field, $value);
        }

        return $user;
    }

    /**
     * Set single object property from forceValues in TypoScript
     */
    public static function forceValue(User $user, string $field, mixed $value): void
    {
        if ($field === 'usergroup') {
            // need objectstorage for usergroup field
            $user->removeAllUsergroups();
            $values = GeneralUtility::trimExplode(',', $value, true);
            $userGroupRepository = self::getUserGroupRepository();

            foreach ($values as $usergroupUid) {
                /** @var UserGroup $usergroup */
                $usergroup = $userGroupRepository->findByUid((int)$usergroupUid);
                $user->addUsergroup($usergroup);
            }
        } else {
            // set value
            $setterMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            if (method_exists($user, $setterMethod)) {
                $user->{$setterMethod}($value);
            }
        }
    }
}
