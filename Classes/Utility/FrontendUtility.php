<?php
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 in2code.de
 *  Alex Kellner <alexander.kellner@in2code.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class FrontendUtility
 *
 * @package In2code\Femanager\Utility
 */
class FrontendUtility extends AbstractUtility
{

    /**
     * Get current pid
     *
     * @return int
     */
    public static function getCurrentPid()
    {
        return (int) self::getTypoScriptFrontendController()->id;
    }

    /**
     * Get frontend language uid
     *
     * @return int
     */
    public static function getFrontendLanguageUid()
    {
        if (!empty(self::getTypoScriptFrontendController()->tmpl->setup['config.']['sys_language_uid'])) {
            return (int) self::getTypoScriptFrontendController()->tmpl->setup['config.']['sys_language_uid'];
        }
        return 0;
    }

    /**
     * Set object properties from forceValues in TypoScript
     *
     * @param User $user
     * @param array $settings
     * @return User $object
     */
    public static function forceValues(User $user, array $settings)
    {
        foreach ((array) $settings as $field => $config) {
            $config = null;
            if (stristr($field, '.')) {
                continue;
            }
            // value to set
            $value = self::getContentObject()->cObjGetSingle($settings[$field], $settings[$field . '.']);
            if ($field === 'usergroup') {
                // need objectstorage for usergroup field
                $user->removeAllUsergroups();
                $values = GeneralUtility::trimExplode(',', $value, true);
                $userGroupRepository = self::getUserGroupRepository();

                foreach ($values as $usergroupUid) {
                    /** @var UserGroup $usergroup */
                    $usergroup = $userGroupRepository->findByUid($usergroupUid);
                    $user->addUsergroup($usergroup);
                }
            } else {
                // set value
                if (method_exists($user, 'set' . ucfirst($field))) {
                    $user->{'set' . ucfirst($field)}($value);
                }
            }
        }
        return $user;
    }
}
