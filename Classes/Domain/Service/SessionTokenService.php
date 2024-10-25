<?php
namespace In2code\Femanager\Domain\Service;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SessionTokenService
{
    /**
     * Default token lifetime in seconds (1 hour)
     */
    const TOKEN_LIFETIME = 3600;

    /**
     * Generate new session token for user
     */
    public function generateTokenForUser(User $user): string
    {
        $randomGenerator = GeneralUtility::makeInstance(Random::class);
        $token = $randomGenerator->generateRandomHexString(32);
        $expiry = time() + self::TOKEN_LIFETIME;

        $user->setTxFemanagerSessionToken($token);
        $user->setTxFemanagerSessionTokenExpiry($expiry);

        return $token;
    }

    /**
     * Validate user's session token
     */
    public function validateUserToken(User $user, string $token): bool
    {
        if (!$user->hasValidSessionToken()) {
            return false;
        }

        if (!hash_equals($user->getTxFemanagerSessionToken(), $token)) {
            return false;
        }

        return true;
    }

    /**
     * Clear user's session token
     */
    public function clearUserToken(User $user): void
    {
        $user->setTxFemanagerSessionToken('');
        $user->setTxFemanagerSessionTokenExpiry(0);
    }

    /**
     * Clean expired tokens from database
     */
    public function cleanExpiredTokens(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                      ->getQueryBuilderForTable('fe_users');

        $queryBuilder
            ->update('fe_users')
            ->set('tx_femanager_session_token', '')
            ->set('tx_femanager_session_token_expiry', 0)
            ->where(
                $queryBuilder->expr()->lt(
                    'tx_femanager_session_token_expiry',
                    $queryBuilder->createNamedParameter(time(), \PDO::PARAM_INT)
                )
            )
            ->executeStatement();
    }
}