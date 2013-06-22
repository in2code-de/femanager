<?php
namespace In2\Femanager\Domain\Model;

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
 * User Model
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser {

	/**
	 * Username
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $username = '';

	/**
	 * Password
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $password = '';

	/**
	 * usergroups
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2\Femanager\Domain\Model\UserGroup>
	 */
	protected $usergroup;

	/**
	 * txFemanagerChangerequest
	 *
	 * @var \string
	 */
	protected $txFemanagerChangerequest;

	/**
	 * crdate
	 *
	 * @var \DateTime
	 */
	protected $crdate;

	/**
	 * tstamp
	 *
	 * @var \DateTime
	 */
	protected $tstamp;

	/**
	 * disable
	 *
	 * @var \bool
	 */
	protected $disable;

	/**
	 * txFemanagerConfirmedbyuser
	 *
	 * @var \bool
	 */
	protected $txFemanagerConfirmedbyuser;

	/**
	 * txFemanagerConfirmedbyadmin
	 *
	 * @var \bool
	 */
	protected $txFemanagerConfirmedbyadmin;

	/**
	 * Online Status
	 *
	 * @var \bool
	 */
	protected $isOnline = FALSE;

	/**
	 * ignoreDirty (TRUE disables update)
	 *
	 * @var \bool
	 */
	protected $ignoreDirty = FALSE;

	/**
	 * Get usergroup
	 *
	 * @return \In2\Femanager\Domain\Model\UserGroup
	 */
	public function getUsergroup() {
		return $this->usergroup;
	}

	/**
	 * Set usergroup
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup
	 */
	public function setUsergroup(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup) {
		$this->usergroup = $usergroup;
	}

	/**
	 * Add usergroup
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
	 */
	public function addUsergroup(\In2\Femanager\Domain\Model\UserGroup $usergroup) {
		$this->usergroup->attach($usergroup);
	}

	/**
	 * Remove usergroup
	 *
	 * @param UserGroup $usergroup
	 */
	public function removeUsergroup(\In2\Femanager\Domain\Model\UserGroup $usergroup) {
		$this->usergroup->detach($usergroup);
	}

	/**
	 * Remove all usergroups
	 */
	public function removeAllUsergroups() {
		$this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @param string $txFemanagerChangerequest
	 */
	public function setTxFemanagerChangerequest($txFemanagerChangerequest) {
		$this->txFemanagerChangerequest = $txFemanagerChangerequest;
	}

	/**
	 * @return string
	 */
	public function getTxFemanagerChangerequest() {
		return $this->txFemanagerChangerequest;
	}

	/**
	 * @param \DateTime $crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * @return \DateTime
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	/**
	 * @param \DateTime $tstamp
	 */
	public function setTstamp($tstamp) {
		$this->tstamp = $tstamp;
	}

	/**
	 * @return \DateTime
	 */
	public function getTstamp() {
		return $this->tstamp;
	}

	/**
	 * @param boolean $disable
	 */
	public function setDisable($disable) {
		$this->disable = $disable;
	}

	/**
	 * @return boolean
	 */
	public function getDisable() {
		return $this->disable;
	}

	/**
	 * @param \bool $txFemanagerConfirmedbyadmin
	 */
	public function setTxFemanagerConfirmedbyadmin($txFemanagerConfirmedbyadmin) {
		$this->txFemanagerConfirmedbyadmin = $txFemanagerConfirmedbyadmin;
	}

	/**
	 * @return \bool
	 */
	public function getTxFemanagerConfirmedbyadmin() {
		return $this->txFemanagerConfirmedbyadmin;
	}

	/**
	 * @param \bool $txFemanagerConfirmedbyuser
	 */
	public function setTxFemanagerConfirmedbyuser($txFemanagerConfirmedbyuser) {
		$this->txFemanagerConfirmedbyuser = $txFemanagerConfirmedbyuser;
	}

	/**
	 * @return \bool
	 */
	public function getTxFemanagerConfirmedbyuser() {
		return $this->txFemanagerConfirmedbyuser;
	}

	/**
	 * @param boolean $ignoreDirty
	 */
	public function setIgnoreDirty($ignoreDirty) {
		$this->ignoreDirty = $ignoreDirty;
	}

	/**
	 * @return boolean
	 */
	public function getIgnoreDirty() {
		return $this->ignoreDirty;
	}

	/**
	 * @return boolean
	 */
	public function getIsOnline() {
		// check if last login was within 2h
		if (
			method_exists($this->getLastlogin(), 'getTimestamp') &&
			$this->getLastlogin()->getTimestamp() > (time() - 2 * 60 * 60) &&
			\In2\Femanager\Utility\Div::checkFrontendSessionToUser($this)
		) {
			return TRUE;
		}
		return $this->isOnline;
	}

	/**
	 * Workarround to disable persistence in updateAction
	 *
	 * @param null $propertyName
	 * @return bool
	 */
	public function _isDirty($propertyName = NULL) {
		return $this->getIgnoreDirty() ? FALSE : parent::_isDirty($propertyName);
	}
}
?>