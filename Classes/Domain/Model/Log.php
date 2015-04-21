<?php
namespace In2\Femanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
 * Log Model
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class Log extends AbstractEntity {

	/**
	 * title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * state
	 *
	 * @var int
	 */
	protected $state;

	/**
	 * user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Set user
	 *
	 * @param User $user
	 * @return Log
	 */
	public function setUser(User $user) {
		$this->user = $user;
		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param int $state
	 * @return Log
	 */
	public function setState($state) {
		$this->state = $state;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @param string $title
	 * @return Log
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}