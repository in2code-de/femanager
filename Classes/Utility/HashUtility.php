<?php
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
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
 * Class HashUtility
 *
 * @package In2code\Femanager\Utility
 */
class HashUtility extends AbstractUtility
{

    /**
     * Check if given hash is correct
     *
     * @param string $hash
     * @param User $user
     * @return bool
     */
    public static function validHash($hash, User $user)
    {
        return self::createHashForUser($user) === $hash;
    }

    /**
     * Create hash for a user
     *
     * @param User $user
     * @return string
     */
    public static function createHashForUser(User $user)
    {
        return self::hashString($user->getUsername());
    }

    /**
     * Create Hash from String and TYPO3 Encryption Key (if available)
     *
     * @param string $string Any String to hash
     * @param int $length Hash Length
     * @return string $hash Hashed String
     */
    protected static function hashString($string, $length = 16)
    {
        return GeneralUtility::shortMD5($string . self::getEncryptionKey(), $length);
    }
}
