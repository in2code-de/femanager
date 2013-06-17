<?php
namespace In2\Femanager\Domain\Repository;

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
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Overload Find by UID to also get hidden records
	 *
	 * @param \int $uid				fe_users UID
	 * @return object
	 */
	public function findByUid($uid) {
		if ($this->identityMap->hasIdentifier($uid, $this->objectType)) {
			$object = $this->identityMap->getObjectByIdentifier($uid, $this->objectType);
		} else {
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectEnableFields(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$and = array(
				$query->equals('uid', $uid),
				$query->equals('deleted', 0)
			);
			$object = $query->matching($query->logicalAnd($and))->execute()->getFirst();
		}
		return $object;
	}

	/**
	 * Find users by commaseparated usergroup list
	 *
	 * @param \string $userGroupList 		commaseparated list of usergroup uids
	 * @param \array $settings 				Flexform and TypoScript Settings
	 * @return query object
	 */
	public function findByUsergroups($userGroupList, $settings) {
		$query = $this->createQuery();

		// where
		if (!empty($userGroupList)) {
			$query->matching(
				$query->logicalOr(
					$query->in('usergroup', explode(',', $userGroupList))
				)
			);
		}

		// sorting
		$sorting = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;
		if ($settings['list']['sorting'] == 'desc') {
			$sorting = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
		}
		$field = preg_replace('/[^a-zA-Z0-9_-]/', '', $settings['list']['orderby']); // ensure that there is no bad behaviour
		$query->setOrderings(
			array(
				 $field => $sorting
			)
		);

		// set limit
		if (intval($settings['list']['limit']) > 0) {
			$query->setLimit(intval($settings['list']['limit']));
		}

		$users = $query->execute();
		return $users;
	}

	/**
	 * Check if there is already an entry in the table
	 *
	 * @param $field
	 * @param $value
	 * @return query object
	 */
	public function checkUniqueDb($field, $value) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectEnableFields(FALSE);

		$and = array(
			$query->equals($field, $value),
			$query->equals('deleted', 0)
		);

		$query->matching(
			$query->logicalAnd($and)
		);

		$users = $query->execute();
		return $users;
	}
}
?>