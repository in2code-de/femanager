<?php
namespace In2\Femanager\Controller;

use \In2\Femanager\Utility\Div;

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
 * User Backend Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class UserBackendController extends \In2\Femanager\Controller\AbstractController {

	/**
	 * action list
	 *
	 * @param \array $filter Filter Array
	 * @return void
	 */
	public function listAction($filter = array()) {
		$users = $this->userRepository->findAllInBackend($filter);
		$this->view->assign('users', $users);
		$this->view->assign('token', \TYPO3\CMS\Backend\Utility\BackendUtility::getUrlToken('tceAction'));
	}

	/**
	 * action user logout
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function userLogoutAction(\In2\Femanager\Domain\Model\User $user) {
		Div::removeFrontendSessionToUser($user);
		$this->flashMessageContainer->add('User successfully logged out');
		$this->redirect('list');
	}

}