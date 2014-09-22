<?php
namespace In2\Femanager\Controller;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \In2\Femanager\Domain\Model\User;
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
 * New Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class NewController extends \In2\Femanager\Controller\AbstractController {

	/**
	 * action new
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @dontvalidate $user
	 * @return void
	 */
	public function newAction(User $user = NULL) {
		$this->view->assign('user', $user);
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
		$user = $this->div->overrideUserGroup($user, $this->settings);
		$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['beforeAnyConfirmation.'], $this->cObj);
		$user = $this->div->fallbackUsernameAndPassword($user);
		if ($this->settings['new']['fillEmailWithUsername'] == 1) {
			$user->setEmail($user->getUsername());
		}
		Div::hashPassword($user, $this->settings['new']['misc']['passwordSave']);
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $this));

		if (!empty($this->settings['new']['confirmByUser']) || !empty($this->settings['new']['confirmByAdmin'])) {
			$this->createRequest($user);
		} else {
			$this->createAllConfirmed($user);
		}
	}

	/**
	 * Update if hash is ok
	 *
	 * @param \int $user User UID
	 * @param \string $hash Given hash
	 * @param \string $status
	 * 			"userConfirmation", "userConfirmationRefused", "adminConfirmation",
	 * 			"adminConfirmationRefused", "adminConfirmationRefusedSilent"
	 * @return void
	 */
	public function confirmCreateRequestAction($user, $hash, $status = 'adminConfirmation') {
		$user = $this->userRepository->findByUid($user);

		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $hash, $status, $this));

		// if there is still no user in db
		if ($user === NULL) {
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('missingUserInDatabase', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
			$this->redirect('new');
		}

		switch ($status) {

			// registration confirmed by user
			case 'userConfirmation':
				if (Div::createHash($user->getUsername()) === $hash) {

					// if user is already confirmed by himself
					if ($user->getTxFemanagerConfirmedbyuser()) {
						$this->flashMessageContainer->add(
							LocalizationUtility::translate('userAlreadyConfirmed', 'femanager'),
							'',
							\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
						);
						$this->redirect('new');
					}

					$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['onUserConfirmation.'], $this->cObj);
					$user->setTxFemanagerConfirmedbyuser(TRUE);
					$this->userRepository->update($user);
					$this->persistenceManager->persistAll();

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.102', 'femanager'),
						102,
						$user
					);

					// must be still confirmed from admin
					if (!empty($this->settings['new']['confirmByAdmin']) && !$user->getTxFemanagerConfirmedbyadmin()) {
						// send email to admin to get this confirmation
						$this->sendMail->send(
							'createAdminConfirmation',
							Div::makeEmailArray(
								$this->settings['new']['confirmByAdmin'],
								$this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
							),
							Div::makeEmailArray(
								$user->getEmail(),
								$user->getUsername()
							),
							'New Registration request',
							array(
								'user' => $user,
								'hash' => Div::createHash($user->getUsername() . $user->getUid())
							),
							$this->config['new.']['email.']['createAdminConfirmation.']
						);

						$this->flashMessageContainer->add(
							LocalizationUtility::translate('createRequestWaitingForAdminConfirm', 'femanager')
						);

					} else {
						$user->setDisable(FALSE);

						$this->flashMessageContainer->add(
							LocalizationUtility::translate('create', 'femanager')
						);

						$this->div->log(
							LocalizationUtility::translate('tx_femanager_domain_model_log.state.101', 'femanager'),
							101,
							$user
						);

						$this->finalCreate($user, 'new', 'createStatus', TRUE, $status);
					}

				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'userConfirmationRefused':
				if (Div::createHash($user->getUsername()) === $hash) {

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.104', 'femanager'),
						104,
						$user
					);

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createProfileDeleted', 'femanager')
					);

					$this->userRepository->remove($user);

				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'adminConfirmation':
				// registration complete
				if (Div::createHash($user->getUsername() . $user->getUid())) {
					$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['onAdminConfirmation.'], $this->cObj);
					$user->setTxFemanagerConfirmedbyadmin(TRUE);
					if ($user->getTxFemanagerConfirmedbyuser() || empty($this->settings['new']['confirmByUser'])) {
						$user->setDisable(FALSE);
					}

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('create', 'femanager')
					);

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.103', 'femanager'),
						103,
						$user
					);

					$this->finalCreate($user, 'new', 'createStatus', FALSE);

				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'adminConfirmationRefused':
				// Admin refuses profile
			case 'adminConfirmationRefusedSilent':
				if (Div::createHash($user->getUsername() . $user->getUid())) {

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.105', 'femanager'),
						105,
						$user
					);

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createProfileDeleted', 'femanager')
					);

					if (!stristr($status, 'silent')) {
						// send email to user to inform him about his profile confirmation
						$this->sendMail->send(
							'CreateUserNotifyRefused',
							Div::makeEmailArray(
								$user->getEmail(),
								$user->getFirstName() . ' ' . $user->getLastName()
							),
							array('sender@femanager.org' => 'Sender Name'),
							'Your profile was refused',
							array(
								'user' => $user
							),
							$this->config['new.']['email.']['createUserNotifyRefused.']
						);
					}

					$this->userRepository->remove($user);

				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			default:

		}

		/**
		 * redirect by TypoScript setting
		 * 		[userConfirmation|userConfirmationRefused|adminConfirmation|
		 * 		adminConfirmationRefused|adminConfirmationRefusedSilent]Redirect
		 */
		$this->redirectByAction('new', $status . 'Redirect');

		$this->redirect('new');
	}

	/**
	 * Just for showing informations after user creation
	 *
	 * @return void
	 */
	public function createStatusAction() {
	}

}