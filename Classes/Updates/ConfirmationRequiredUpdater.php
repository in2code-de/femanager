<?php

declare(strict_types=1);

namespace In2code\Femanager\Updates;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Populates the new fe_users.tx_femanager_confirmation_required field for accounts that were still
 * pending (disabled) when the field was introduced. Without this requirement, the registration
 * workflow can no longer tell which confirmation a legacy pending user still needs and would fall
 * back to "admin required" for safety. The mapping below restores the precise requirement:
 *
 *   - confirmed by user, not by admin -> admin confirmation is still pending
 *   - confirmed by neither            -> ambiguous; require both (bypass-safe, admin can release)
 *
 * Already admin-confirmed accounts are left untouched (a disabled, admin-confirmed account was
 * disabled for other reasons and needs no femanager confirmation).
 */
#[UpgradeWizard('femanager_confirmationrequiredupdater')]
class ConfirmationRequiredUpdater implements UpgradeWizardInterface
{
    private const TABLE = 'fe_users';

    public function getTitle(): string
    {
        return 'EXT:femanager: Migrate required confirmation for pending users';
    }

    public function getDescription(): string
    {
        return 'Stores the required confirmation (user/admin) on every pending fe_users account so the '
            . 'registration workflow no longer depends on the plugin settings. Pending accounts to migrate: '
            . $this->countPendingUsersToMigrate();
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->countPendingUsersToMigrate() > 0;
    }

    public function executeUpdate(): bool
    {
        $this->updatePendingUsers(
            User::CONFIRMATION_REQUIRED_ADMIN,
            true
        );
        $this->updatePendingUsers(
            User::CONFIRMATION_REQUIRED_USER | User::CONFIRMATION_REQUIRED_ADMIN,
            false
        );

        return true;
    }

    private function countPendingUsersToMigrate(): int
    {
        $queryBuilder = $this->getQueryBuilder();

        return (int)$queryBuilder
            ->count('uid')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('disable', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq(
                    'tx_femanager_confirmedbyadmin',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tx_femanager_confirmation_required',
                    $queryBuilder->createNamedParameter(User::CONFIRMATION_REQUIRED_NONE, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchOne();
    }

    private function updatePendingUsers(int $confirmationRequired, bool $confirmedByUser): void
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->update(self::TABLE)
            ->set('tx_femanager_confirmation_required', $confirmationRequired)
            ->where(
                $queryBuilder->expr()->eq('disable', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq(
                    'tx_femanager_confirmedbyadmin',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tx_femanager_confirmedbyuser',
                    $queryBuilder->createNamedParameter($confirmedByUser ? 1 : 0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tx_femanager_confirmation_required',
                    $queryBuilder->createNamedParameter(User::CONFIRMATION_REQUIRED_NONE, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder;
    }
}
