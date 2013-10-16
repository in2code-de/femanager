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
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewController extends \In2\Femanager\Controller\GeneralController {

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
	 * @return void
	 */
	public function createAction(User $user) {
		$user = $this->div->overrideUserGroup($user, $this->settings); // overwrite usergroup from flexform settings
		$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['beforeAnyConfirmation.'], $this->cObj); // overwrite values from TypoScript
		if ($this->settings['new']['fillEmailWithUsername'] == 1) { // fill email with value from username
			$user->setEmail($user->getUsername());
		}
		Div::hashPassword($user, $this->settings['new']['passwordSave']); // convert password to md5 or sha1 hash
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $this)); // add signal

		if (!empty($this->settings['new']['confirmByUser']) || !empty($this->settings['new']['confirmByAdmin'])) {
			$this->createRequest($user); // request user creation
		} else {
			$this->createAllConfirmed($user); // create user
		}
	}

	/**
	 * Update if hash is ok
	 *
	 * @param \int $user			User UID
	 * @param \string $hash			Given hash
	 * @param \string $status		"userConfirmation", "userConfirmationRefused", "adminConfirmation", "adminConfirmationRefused", "adminConfirmationRefusedSilent"
	 * @return void
	 */
	public function confirmCreateRequestAction($user, $hash, $status = 'adminConfirmation') {
		$user = $this->userRepository->findByUid($user); // workarround to also get hidden users

		switch ($status) {
			case 'userConfirmation': // registration confirmed by user
				if (Div::createHash($user->getUsername()) === $hash) { // hash is correct
					$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['onUserConfirmation.'], $this->cObj); // overwrite values from TypoScript
					$user->setTxFemanagerConfirmedbyuser(TRUE);

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.102', 'femanager'),
						102,
						$user
					);

					// must be still confirmed from admin
					if (!empty($this->settings['new']['confirmByAdmin']) && !$user->getTxFemanagerConfirmedbyadmin()) {
						// send email to admin to get this confirmation
						$this->div->sendEmail(
							'createAdminConfirmation',
							Div::makeEmailArray(
								$this->settings['new']['confirmByAdmin'],
								$this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
							),
							Div::makeEmailArray(
								$user->getEmail(),
								$user->getUsername()
							),
							'New Registration request', // will be overwritten with TypoScript
							array(
								 'user' => $user,
								 'hash' => Div::createHash($user->getUsername() . $user->getUid())
							),
							$this->config['new.']['email.']['createAdminConfirmation.']
						);

						$this->flashMessageContainer->add(
							LocalizationUtility::translate('createRequestWaitingForAdminConfirm', 'femanager')
						);

					} else { // registration completed
						$user->setDisable(FALSE);

						$this->flashMessageContainer->add(
							LocalizationUtility::translate('create', 'femanager')
						);

						$this->div->log(
							LocalizationUtility::translate('tx_femanager_domain_model_log.state.101', 'femanager'),
							101,
							$user
						);

						$this->finalCreate($user, 'new', 'createStatus');
					}

				} else { // hash is not correct
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'userConfirmationRefused': // registration refused by user
				if (Div::createHash($user->getUsername()) === $hash) { // hash is correct

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.104', 'femanager'),
						104,
						$user
					);

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createProfileDeleted', 'femanager')
					);

					$this->userRepository->remove($user);

				} else { // has is not correct
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'adminConfirmation': // registration confirmed by admin
				// registration complete
				if (Div::createHash($user->getUsername() . $user->getUid())) { // hash is correct
					$user = $this->div->forceValues($user, $this->config['new.']['forceValues.']['onAdminConfirmation.'], $this->cObj); // overwrite values from TypoScript
					$user->setTxFemanagerConfirmedbyadmin(TRUE); // set to confirmed by admin
					if ($user->getTxFemanagerConfirmedbyuser() || empty($this->settings['new']['confirmByUser'])) { // if already confirmed by user OR if user confirmation turned off
						$user->setDisable(FALSE); // enable
					}

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('create', 'femanager')
					);

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.103', 'femanager'),
						103,
						$user
					);

					// send email to user to inform him about his profile confirmation
					$this->div->sendEmail(
						'createUserNotify',
						Div::makeEmailArray(
							$user->getEmail(),
							$user->getFirstName() . ' ' . $user->getLastName()
						),
						array('sender@femanager.org' => 'Sender Name'), // will be overwritten by TypoScript (if set)
						'Your profile was confirmed', // will be overwritten with TypoScript
						array(
							 'user' => $user
						),
						$this->config['new.']['email.']['createUserNotify.']
					);

					$this->finalCreate($user, 'new', 'createStatus', FALSE);

				} else { // hash is not correct
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

			case 'adminConfirmationRefused': // registration refused by admin
			case 'adminConfirmationRefusedSilent': // registration refused by admin (silent)
				if (Div::createHash($user->getUsername() . $user->getUid())) { // hash is correct

					$this->div->log(
						LocalizationUtility::translate('tx_femanager_domain_model_log.state.105', 'femanager'),
						105,
						$user
					);

					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createProfileDeleted', 'femanager')
					);

					if (!stristr($status, 'silent')) { // send mail to user
						// send email to user to inform him about his profile confirmation
						$this->div->sendEmail(
							'CreateUserNotifyRefused',
							Div::makeEmailArray(
								$user->getEmail(),
								$user->getFirstName() . ' ' . $user->getLastName()
							),
							array('sender@femanager.org' => 'Sender Name'), // will be overwritten by TypoScript (if set)
							'Your profile was refused', // will be overwritten with TypoScript
							array(
								'user' => $user
							),
							$this->config['new.']['email.']['createUserNotifyRefused.']
						);
					}

					$this->userRepository->remove($user);

				} else { // has is not correct
					$this->flashMessageContainer->add(
						LocalizationUtility::translate('createFailedProfile', 'femanager'),
						'',
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);

					return;
				}
				break;

		}
                # "userConfirmation", "userConfirmationRefused", "adminConfirmation", "adminConfirmationRefused", "adminConfirmationRefusedSilent"
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
?>