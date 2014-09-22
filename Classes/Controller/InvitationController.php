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
		$this->allowedUserForInvitationNewAndCreate();
		$this->view->assign('allUserGroups', $this->allUserGroups);
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
		$this->allowedUserForInvitationNewAndCreate();
		$user->setDisable(TRUE);
		$user = $this->div->forceValues($user, $this->config['invitation.']['forceValues.']['beforeAnyConfirmation.'], $this->cObj);
		$user = $this->div->fallbackUsernameAndPassword($user);
		if ($this->settings['invitation']['fillEmailWithUsername'] == 1) {
			$user->setEmail($user->getUsername());
		}
		Div::hashPassword($user, $this->settings['invitation']['misc']['passwordSave']);
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $this));

		$this->createAllConfirmed($user);
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

		// send confirmation mail to user
		$this->sendMail->send(
			'invitation',
			Div::makeEmailArray(
				$user->getEmail(),
				$user->getUsername()
			),
			Div::makeEmailArray(
				$user->getEmail(),
				$user->getUsername()
			),
			'Profile creation with invitation',
			array(
				'user' => $user,
				'settings' => $this->settings,
				'hash' => Div::createHash($user->getUsername() . $user->getUid())
			),
			$this->config['invitation.']['email.']['invitation.']
		);

		// send notify email to admin
		if ($this->settings['invitation']['notifyAdminStep1']) {
			$this->sendMail->send(
				'invitationNotifyStep1',
				Div::makeEmailArray(
					$this->settings['invitation']['notifyAdminStep1'],
					$this->settings['invitation']['email']['invitationAdminNotifyStep1']['receiver']['name']['value']
				),
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				'Profile creation with invitation - Step 1',
				array(
					'user' => $user,
					'settings' => $this->settings
				),
				$this->config['invitation.']['email.']['invitationAdminNotifyStep1.']
			);
		}

		// add signal after user generation
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $this));

		$this->redirectByAction('redirectStep1');
		$this->redirect('new');
	}

	/**
	 * action edit
	 *
	 * @param \int $user User UID
	 * @param \string $hash
	 * @return void
	 */
	public function editAction($user, $hash = NULL) {
		$user = $this->userRepository->findByUid($user);
		$user->setDisable(FALSE);
		$this->userRepository->update($user);
		$this->persistenceManager->persistAll();

		// add signal on edit
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $hash, $this));

		$this->view->assign('user', $user);
		$this->view->assign('hash', $hash);

		if (Div::createHash($user->getUsername() . $user->getUid()) !== $hash) {
			if ($user !== NULL) {
				// delete user for security reasons
				$this->userRepository->remove($user);
			}
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('createFailedProfile', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
			$this->forward('status');
		}

		$this->assignForAll();
	}

	/**
	 * action update
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @validate $user In2\Femanager\Domain\Validator\ServersideValidator
	 * @validate $user In2\Femanager\Domain\Validator\PasswordValidator
	 * @return void
	 */
	public function updateAction($user) {
		$this->flashMessageContainer->add(
			LocalizationUtility::translate('createAndInvitedFinished', 'femanager')
		);

		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.405', 'femanager'),
			401,
			$user
		);

		// send notify email to admin
		if ($this->settings['invitation']['notifyAdmin']) {
			$this->sendMail->send(
				'invitationNotify',
				Div::makeEmailArray(
					$this->settings['invitation']['notifyAdmin'],
					$this->settings['invitation']['email']['invitationAdminNotify']['receiver']['name']['value']
				),
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				'Profile creation with invitation - Final',
				array(
					'user' => $user,
					'settings' => $this->settings
				),
				$this->config['invitation.']['email.']['invitationAdminNotify.']
			);
		}

		$user = $this->div->overrideUserGroup($user, $this->settings, 'invitation');
		Div::hashPassword($user, $this->settings['invitation']['misc']['passwordSave']);
		$this->userRepository->update($user);
		$this->persistenceManager->persistAll();

		// add signal after user generation
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $this));

		$this->redirectByAction('invitation', 'redirectPasswordChanged');
		$this->redirect('status');
	}

	/**
	 * Init for delete
	 *
	 * @return void
	 */
	protected function initializeDeleteAction() {
	}

	/**
	 * action delete
	 *
	 * @param \int $user User UID
	 * @param \string $hash
	 * @return void
	 */
	public function deleteAction($user, $hash = NULL) {
		$user = $this->userRepository->findByUid($user);

		if (Div::createHash($user->getUsername() . $user->getUid()) === $hash) {

			// write log
			$this->div->log(
				LocalizationUtility::translate('tx_femanager_domain_model_log.state.402', 'femanager'),
				300,
				$user
			);

			// add flashmessage
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('tx_femanager_domain_model_log.state.402', 'femanager')
			);

			// send notify email to admin
			if ($this->settings['invitation']['notifyAdminStep1']) {
				$this->sendMail->send(
					'invitationRefused',
					Div::makeEmailArray(
						$this->settings['invitation']['notifyAdminStep1'],
						$this->settings['invitation']['email']['invitationRefused']['receiver']['name']['value']
					),
					Div::makeEmailArray(
						$user->getEmail(),
						$user->getUsername()
					),
					'Profile deleted from User after invitation - Step 1',
					array(
						'user' => $user,
						'settings' => $this->settings
					),
					$this->config['invitation.']['email.']['invitationRefused.']
				);
			}

			// delete user
			$this->userRepository->remove($user);

			$this->redirectByAction('invitation', 'redirectDelete');
			$this->redirect('status');
		} else {
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('tx_femanager_domain_model_log.state.403', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
			$this->redirect('status');
		}
	}

	/**
	 * Restricted Action to show messages
	 *
	 * @return void
	 */
	public function statusAction() {
	}

	/**
	 * Check if user is allowed to see this action
	 *
	 * @return bool
	 */
	protected function allowedUserForInvitationNewAndCreate() {
		if (empty($this->settings['invitation']['allowedUserGroups'])) {
			return TRUE;
		}
		$allowedUsergroupUids = GeneralUtility::trimExplode(',', $this->settings['invitation']['allowedUserGroups'], TRUE);
		$currentUsergroupUids = $this->div->getCurrentUsergroupUids();

		// compare allowedUsergroups with currentUsergroups
		if (count(array_intersect($allowedUsergroupUids, $currentUsergroupUids))) {
			return TRUE;
		}

		// current user is not allowed
		$this->flashMessageContainer->add(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.404', 'femanager'),
			'',
			\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
		);
		$this->forward('status');
		return FALSE;
	}

}