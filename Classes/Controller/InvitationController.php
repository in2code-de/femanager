<?php
namespace In2\Femanager\Controller;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility,
	\TYPO3\CMS\Core\Utility\GeneralUtility,
	\In2\Femanager\Domain\Model\User,
	\In2\Femanager\Utility\Div;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Alex Kellner <alexander.kellner@in2code.de>, in2code
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
 * Invitation Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class InvitationController extends \In2\Femanager\Controller\AbstractController {

	/**
	 * action new
	 *
	 * @return void
	 */
	public function newAction() {
		$this->assignForAll();
	}

	/**
	 * action create
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @validate $user In2\Femanager\Domain\Validator\ServersideValidator
	 * @validate $user In2\Femanager\Domain\Validator\PasswordValidator
	 * @validate $user In2\Femanager\Domain\Validator\CaptchaValidator
	 * @return void
	 */
	public function createAction(User $user) {
		$user->setDisable(TRUE);
		$user = $this->div->forceValues($user, $this->config['invitation.']['forceValues.']['beforeAnyConfirmation.'], $this->cObj);
		$user = $this->div->fallbackUsernameAndPassword($user);
		if ($this->settings['invitation']['fillEmailWithUsername'] == 1) {
			$user->setEmail($user->getUsername());
		}
		Div::hashPassword($user, $this->settings['invitation']['passwordSave']);
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $this));

		if (!empty($this->settings['invitation']['confirmByAdmin'])) {
			// todo
			$this->createRequest($user);
		} else {
			$this->createAllConfirmed($user);
		}
	}

	/**
	 * Prefix method to createAction()
	 * 		Create Confirmation from Admin is not necessary
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function createAllConfirmed(User $user) {
		$this->userRepository->add($user);
		$this->persistenceManager->persistAll();

		$this->flashMessageContainer->add(
			LocalizationUtility::translate('createAndInvited', 'femanager')
		);

		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.401', 'femanager'),
			401,
			$user
		);

		// send notify email to admin
		if ($this->settings['invitation']['notifyAdmin']) {
			$this->div->sendEmail(
				'invitationNotify',
				Div::makeEmailArray(
					$this->settings['new']['notifyAdmin'],
					$this->settings['new']['email']['createAdminNotify']['receiver']['name']['value']
				),
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				'Profile creation',
				array(
					'user' => $user,
					'settings' => $this->settings
				),
				$this->config['new.']['email.']['createAdminNotify.']
			);
		}

		// add signal after user generation
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $this));

		// frontend redirect (if activated via TypoScript)
		$this->redirectByAction('invitation');

		// go to an action
		$this->redirect('new');
	}

	/**
	 * action edit
	 *
	 * @return void
	 */
	public function editAction() {
		$this->assignForAll();
	}

	/**
	 * action update
	 *
	 * @return void
	 */
	public function updateAction() {
		$this->assignForAll();
	}

}