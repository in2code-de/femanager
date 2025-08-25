<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Repository;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\PageTreeService;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class UserRepository
 */
class UserRepository extends Repository
{
    /**
     * Overload Find by UID to also get hidden records
     *
     * @param int $uid fe_users UID
     * @return User
     */
    public function findByUid($uid)
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $and = $query->equals('uid', $uid);

        /** @var User $user */
        $user = $query->matching($query->logicalAnd($and))->execute()->getFirst();

        return $user;
    }

    /**
     * Find users by commaseparated usergroup list
     *
     * @param string $userGroupList commaseparated list of usergroup uids
     * @param array $settings Flexform and TypoScript Settings
     * @param array $filter Filter Array
     */
    public function findByUsergroups($userGroupList, array $settings, $filter): QueryResultInterface|array
    {
        $query = $this->createQuery();

        // where
        $and = [
            $query->greaterThan('uid', 0),
        ];
        if (!empty($userGroupList)) {
            $selectedUsergroups = GeneralUtility::trimExplode(',', $userGroupList, true);
            $logicalOr = [];
            foreach ($selectedUsergroups as $group) {
                $logicalOr[] = $query->contains('usergroup', $group);
            }

            $and[] = $query->logicalOr(...$logicalOr);
        }

        if (!empty($filter['searchword'])) {
            $searchwords = GeneralUtility::trimExplode(' ', $filter['searchword'], true);
            $fieldsToSearch = GeneralUtility::trimExplode(
                ',',
                $settings['list']['filter']['searchword']['fieldsToSearch'],
                true
            );
            foreach ($searchwords as $searchword) {
                $logicalOr = [];
                foreach ($fieldsToSearch as $searchfield) {
                    $logicalOr[] = $query->like($searchfield, '%' . $searchword . '%');
                }

                $and[] = $query->logicalOr(...$logicalOr);
            }
        }

        $query->matching($query->logicalAnd(...$and));

        // sorting
        $sorting = QueryInterface::ORDER_ASCENDING;
        if (($settings['list']['sorting'] ?? null) === 'desc') {
            $sorting = QueryInterface::ORDER_DESCENDING;
        }

        $orderby = $settings['list']['orderby'] ?? 'uid';
        $field = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$orderby);
        $query->setOrderings([$field => $sorting]);

        // set limit
        $limit = $settings['list']['limit'] ?? 0;
        if ($limit > 0) {
            $query->setLimit((int)$limit);
        }

        return $query->execute();
    }

    /**
     * Check if there is already an entry in the table
     *
     * @param $field
     * @param $value
     * @param ?User $user Existing User
     */
    public function checkUniqueDb($field, $value, ?User $user = null): ?User
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePageAndStarttime($query);

        $and = [$query->equals($field, $value)];
        if ($user instanceof \In2code\Femanager\Domain\Model\User && method_exists($user, 'getUid')) {
            $and[] = $query->logicalNot($query->equals('uid', $user->getUid()));
        }

        $constraint = $query->logicalAnd(...$and);

        $query->matching($constraint);

        /** @var User $user */
        $user = $query->execute()->getFirst();

        return $user;
    }

    /**
     * Check if there is already an entry in the table on current page
     *
     * @param $field
     * @param $value
     * @param User $user Existing User
     */
    public function checkUniquePage($field, $value, ?User $user = null): ?User
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $and = [
            $query->equals($field, $value),
            $query->equals('deleted', 0),
        ];
        if ($user instanceof \In2code\Femanager\Domain\Model\User && method_exists($user, 'getUid')) {
            $and[] = $query->logicalNot($query->equals('uid', (int)$user->getUid()));
        }

        $constraint = $query->logicalAnd(...$and);

        $query->matching($constraint);

        /** @var User $user */
        $user = $query->execute()->getFirst();

        return $user;
    }

    /**
     * Find All for Backend Actions
     *
     * @param array $filter Filter Array
     */
    public function findAllInBackend(array $filter): QueryResultInterface|array
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $and = [$query->greaterThan('uid', 0)];
        $and = $this->filterByPage($and, $query);
        $and = $this->filterBySearchword($filter, $query, $and);

        $query->matching($query->logicalAnd(...$and));
        $query->setOrderings(['username' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }

    /**
     * Find All for Backend Actions
     *
     * @param array $filter Filter Array
     * @param bool $userConfirmation Show only fe_users which are confirmed by the user?
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function findAllInBackendForConfirmation(
        array $filter,
        bool $userConfirmation = false
    ): QueryResultInterface|array {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $and = [$query->equals('disable', true)];
        $and = $this->filterByPage($and, $query);
        $and = $this->filterBySearchword($filter, $query, $and);
        $and = $this->filterByUserConfirmation($and, $query, $userConfirmation);

        $query->matching($query->logicalAnd(...$and));
        $query->setOrderings(['username' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }

    /**
     * Find all users from current page or from any subpage
     * If no page id given or if on rootpage (pid 0):
     *      - Don't show any users for editors
     *      - Show all users for admins
     */
    protected function filterByPage(array $and, QueryInterface $query): array
    {
        if (BackendUtility::getPageIdentifier() > 0) {
            $and[] = $query->in('pid', $this->getTreeList(BackendUtility::getPageIdentifier()));
        } elseif (!BackendUserUtility::isAdminAuthentication()) {
            $and[] = $query->equals('uid', 0);
        }

        return $and;
    }

    protected function filterBySearchword(array $filter, QueryInterface $query, array $and): array
    {
        if (!empty($filter['searchword'])) {
            $searchwords = GeneralUtility::trimExplode(' ', $filter['searchword'], true);
            foreach ($searchwords as $searchword) {
                $orConditions = [];
                $orConditions[] = $query->like('address', '%' . $searchword . '%');
                $orConditions[] = $query->like('city', '%' . $searchword . '%');
                $orConditions[] = $query->like('company', '%' . $searchword . '%');
                $orConditions[] = $query->like('country', '%' . $searchword . '%');
                $orConditions[] = $query->like('email', '%' . $searchword . '%');
                $orConditions[] = $query->like('fax', '%' . $searchword . '%');
                $orConditions[] = $query->like('first_name', '%' . $searchword . '%');
                $orConditions[] = $query->like('image', '%' . $searchword . '%');
                $orConditions[] = $query->like('last_name', '%' . $searchword . '%');
                $orConditions[] = $query->like('middle_name', '%' . $searchword . '%');
                $orConditions[] = $query->like('name', '%' . $searchword . '%');
                $orConditions[] = $query->like('telephone', '%' . $searchword . '%');
                $orConditions[] = $query->like('title', '%' . $searchword . '%');
                $orConditions[] = $query->like('usergroup.title', '%' . $searchword . '%');
                $orConditions[] = $query->like('username', '%' . $searchword . '%');
                $orConditions[] = $query->like('www', '%' . $searchword . '%');
                $orConditions[] = $query->like('zip', '%' . $searchword . '%');
                $and[] = $query->logicalOr(...$orConditions);
            }
        }

        return $and;
    }

    protected function filterByUserConfirmation(array $and, QueryInterface $query, bool $userConfirmation): array
    {
        $and[] = $query->equals('txFemanagerConfirmedbyuser', $userConfirmation);

        return $and;
    }

    /**
     * Get all children pids of a start pid
     *
     * @param int $pageIdentifier
     */
    protected function getTreeList($pageIdentifier): array
    {
        $pageTreeService = GeneralUtility::makeInstance(PageTreeService::class);
        $treeList = $pageTreeService->getTreeList($pageIdentifier, 99, 0, '1');

        return GeneralUtility::trimExplode(',', (string) $treeList, true);
    }

    protected function ignoreEnableFieldsAndStoragePage(QueryInterface $query)
    {
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);
    }

    protected function ignoreEnableFieldsAndStoragePageAndStarttime(QueryInterface $query)
    {
        $this->ignoreEnableFieldsAndStoragePage($query);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled', 'starttime']);
    }

    /**
     * Find All
     */
    public function findFirstByEmail(string $mail): User|null
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $and = [
            $query->equals('txFemanagerConfirmedbyuser', false),
            $query->equals('email', $mail),
        ];
        $query->matching($query->logicalAnd(...$and));

        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->getFirst();
    }
}
