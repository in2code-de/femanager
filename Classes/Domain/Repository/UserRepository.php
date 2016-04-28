<?php
namespace In2code\Femanager\Domain\Repository;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
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
 * User Repository
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
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
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $and = [
            $query->equals('uid', $uid),
            $query->equals('deleted', 0)
        ];
        $object = $query->matching($query->logicalAnd($and))->execute()->getFirst();
        return $object;
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
        if ((int) $settings['list']['limit'] > 0) {
            $query->setLimit((int) $settings['list']['limit']);
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
     * @return QueryResultInterface|array
     */
    public function checkUniqueDb($field, $value, User $user = null)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $and = [
            $query->equals($field, $value),
            $query->equals('deleted', 0)
        ];
        if (method_exists($user, 'getUid')) {
            $and[] = $query->logicalNot($query->equals('uid', $user->getUid()));
        }
        $constraint = $query->logicalAnd($and);

        $query->matching($constraint);

        $users = $query->execute()->getFirst();
        return $users;
    }

    /**
     * Check if there is already an entry in the table on current page
     *
     * @param $field
     * @param $value
     * @param \In2code\Femanager\Domain\Model\User $user Existing User
     * @return QueryResultInterface|array
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
            $and[] = $query->logicalNot($query->equals('uid', (int) $user->getUid()));
        }
        $constraint = $query->logicalAnd($and);

        $query->matching($constraint);

        $users = $query->execute()->getFirst();
        return $users;
    }

    /**
     * Find All for Backend Actions
     *
     * @param array $filter Filter Array
     * @return QueryResultInterface|array
     */
    public function findAllInBackend($filter)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        // Where
        $and = [
            $query->equals('deleted', 0)
        ];
        $this->filterByPage($and, $query);
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
        $query->matching($query->logicalAnd($and));

        // Order
        $query->setOrderings(
            [
                'username' => QueryInterface::ORDER_ASCENDING
            ]
        );
        return $query->execute();
    }

    /**
     * Find all users from current page or from any subpage
     * If no page id given or if on rootpage (pid 0):
     *      - Don't show any users for editors
     *      - Show all users for admins
     *
     * @param array $and
     * @param QueryInterface $query
     */
    protected function filterByPage(array &$and, QueryInterface $query)
    {
        if ($this->getPageIdentifier() > 0) {
            $and[] = $query->in('pid', $this->getTreeList($this->getPageIdentifier()));
        } else {
            if (!BackendUserUtility::isAdminAuthentication()) {
                $and[] = $query->equals('uid', 0);
            }
        }
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
     * @return int
     */
    protected function getPageIdentifier()
    {
        return (int)GeneralUtility::_GET('id');
    }
}
