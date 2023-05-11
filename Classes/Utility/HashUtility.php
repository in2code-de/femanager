<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;

/**
 * Class HashUtility
 */
class HashUtility extends AbstractUtility
{
    /**
     * Check if given hash is correct
     */
    public static function validHash(string $hash, User $user): bool
    {
        return self::createHashForUser($user) === $hash;
    }

    /**
     * Create hash for a user
     */
    public static function createHashForUser(User $user): string
    {
        return self::hashString($user->getUsername());
    }

    /**
     * Create Hash from String and TYPO3 Encryption Key (if available)
     *
     * @param string $string Any String to hash
     * @param int $length Hash Length
     * @return string Hashed String
     */
    protected static function hashString(string $string, int $length = 16): string
    {
        return substr(md5($string . self::getEncryptionKey()), 0, $length);
    }
}
