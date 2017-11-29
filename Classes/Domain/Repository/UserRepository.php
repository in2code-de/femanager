<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Repository;

use In2code\Femanager\Domain\Model\User;
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
        $and = [$query->equals('uid', $uid)];

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
     * @return QueryResultInterface|array
     */
    public function findByUsergroups($userGroupList, $settings, $filter)
    {
        $query = $this->createQuery();

        // where
        $and = [
            $query->greaterThan('uid', 0)
        ];
        if (!empty($userGroupList)) {
            $selectedUsergroups = GeneralUtility::trimExplode(',', $userGroupList, true);
            $logicalOr = [];
            foreach ($selectedUsergroups as $group) {
                $logicalOr[] = $query->contains('usergroup', $group);
            }
            $and[] = $query->logicalOr($logicalOr);
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
                $and[] = $query->logicalOr($logicalOr);
            }
        }
        $query->matching($query->logicalAnd($and));

        // sorting
        $sorting = QueryInterface::ORDER_ASCENDING;
        if ($settings['list']['sorting'] === 'desc') {
            $sorting = QueryInterface::ORDER_DESCENDING;
        }
        $field = preg_replace('/[^a-zA-Z0-9_-]/', '', $settings['list']['orderby']);
        $query->setOrderings([$field => $sorting]);

        // set limit
        if ((int)$settings['list']['limit'] > 0) {
            $query->setLimit((int)$settings['list']['limit']);
        }

        $users = $query->execute();
        return $users;
    }

    /**
     * Check if there is already an entry in the table
     *
     * @param $field
     * @param $value
     * @param User $user Existing User
     * @return null|User
     */
    public function checkUniqueDb($field, $value, User $user = null)
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);

        $and = [$query->equals($field, $value)];
        if (method_exists($user, 'getUid')) {
            $and[] = $query->logicalNot($query->equals('uid', $user->getUid()));
        }
        $constraint = $query->logicalAnd($and);

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
     * @param \In2code\Femanager\Domain\Model\User $user Existing User
     * @return null|User
     */
    public function checkUniquePage($field, $value, User $user = null)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $and = [
            $query->equals($field, $value),
            $query->equals('deleted', 0)
        ];
        if (method_exists($user, 'getUid')) {
            $and[] = $query->logicalNot($query->equals('uid', (int)$user->getUid()));
        }
        $constraint = $query->logicalAnd($and);

        $query->matching($constraint);

        /** @var User $user */
        $user = $query->execute()->getFirst();
        return $user;
    }

    /**
     * Find All for Backend Actions
     *
     * @param array $filter Filter Array
     * @return QueryResultInterface|array
     */
    public function findAllInBackend(array $filter)
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $and = [$query->greaterThan('uid', 0)];
        $and = $this->filterByPage($and, $query);
        $and = $this->filterBySearchword($filter, $query, $and);
        $query->matching($query->logicalAnd($and));
        $query->setOrderings(['username' => QueryInterface::ORDER_ASCENDING]);
        $records = $query->execute();
        return $records;
    }

    /**
     * Find All for Backend Actions
     *
     * @param array $filter Filter Array
     * @param bool $userConfirmation Show only fe_users which are confirmed by the user?
     * @return QueryResultInterface|array
     */
    public function findAllInBackendForConfirmation(array $filter, bool $userConfirmation = false)
    {
        $query = $this->createQuery();
        $this->ignoreEnableFieldsAndStoragePage($query);
        $and = [$query->equals('disable', true)];
        $and = $this->filterByPage($and, $query);
        $and = $this->filterBySearchword($filter, $query, $and);
        $and = $this->filterByUserConfirmation($and, $query, $userConfirmation);
        $query->matching($query->logicalAnd($and));
        $query->setOrderings(['username' => QueryInterface::ORDER_ASCENDING]);
        $records = $query->execute();
        return $records;
    }

    /**
     * Find all users from current page or from any subpage
     * If no page id given or if on rootpage (pid 0):
     *      - Don't show any users for editors
     *      - Show all users for admins
     *
     * @param array $and
     * @param QueryInterface $query
     * @return array
     */
    protected function filterByPage(array $and, QueryInterface $query): array
    {
        if (BackendUtility::getPageIdentifier() > 0) {
            $and[] = $query->in('pid', $this->getTreeList(BackendUtility::getPageIdentifier()));
        } else {
            if (!BackendUserUtility::isAdminAuthentication()) {
                $and[] = $query->equals('uid', 0);
            }
        }
        return $and;
    }

    /**
     * @param array $filter
     * @param QueryInterface $query
     * @param array $and
     * @return array
     */
    protected function filterBySearchword(array $filter, QueryInterface $query, array $and): array
    {
        if (!empty($filter['searchword'])) {
            $searchwords = GeneralUtility::trimExplode(' ', $filter['searchword'], true);
            foreach ($searchwords as $searchword) {
                $or = [];
                $or[] = $query->like('address', '%' . $searchword . '%');
                $or[] = $query->like('city', '%' . $searchword . '%');
                $or[] = $query->like('company', '%' . $searchword . '%');
                $or[] = $query->like('country', '%' . $searchword . '%');
                $or[] = $query->like('email', '%' . $searchword . '%');
                $or[] = $query->like('fax', '%' . $searchword . '%');
                $or[] = $query->like('first_name', '%' . $searchword . '%');
                $or[] = $query->like('image', '%' . $searchword . '%');
                $or[] = $query->like('last_name', '%' . $searchword . '%');
                $or[] = $query->like('middle_name', '%' . $searchword . '%');
                $or[] = $query->like('name', '%' . $searchword . '%');
                $or[] = $query->like('telephone', '%' . $searchword . '%');
                $or[] = $query->like('title', '%' . $searchword . '%');
                $or[] = $query->like('usergroup.title', '%' . $searchword . '%');
                $or[] = $query->like('username', '%' . $searchword . '%');
                $or[] = $query->like('www', '%' . $searchword . '%');
                $or[] = $query->like('zip', '%' . $searchword . '%');
                $and[] = $query->logicalOr($or);
            }
        }
        return $and;
    }

    /**
     * @param array $and
     * @param QueryInterface $query
     * @param bool $userConfirmation
     * @return array
     */
    protected function filterByUserConfirmation(array $and, QueryInterface $query, bool $userConfirmation): array
    {
        if ($userConfirmation === true) {
            $and[] = $query->equals('txFemanagerConfirmedbyuser', true);
        }
        return $and;
    }

    /**
     * Get all children pids of a start pid
     *
     * @param int $pageIdentifier
     * @return array
     */
    protected function getTreeList($pageIdentifier)
    {
        $queryGenerator = $this->objectManager->get('TYPO3\\CMS\\Core\\Database\\QueryGenerator');
        $treeList = $queryGenerator->getTreeList($pageIdentifier, 99, 0, '1');
        return GeneralUtility::trimExplode(',', $treeList, true);
    }

    /**
     * @param QueryInterface $query
     * @return void
     */
    protected function ignoreEnableFieldsAndStoragePage(QueryInterface $query)
    {
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);
    }
}
