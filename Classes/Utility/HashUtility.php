<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class HashUtility
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
