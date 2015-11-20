<?php
namespace In2code\Femanager\Controller;

use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use In2code\Femanager\Domain\Model\User;

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
 *          GNU General Public License, version 3 or later
 */
class NewController extends AbstractController
{

    /**
     * action new
     *
     * @param User $user
     * @dontvalidate $user
     * @return void
     */
    public function newAction(User $user = null)
    {
        $this->view->assign('user', $user);
        $this->view->assign('allUserGroups', $this->allUserGroups);
        $this->assignForAll();
    }

    /**
     * action create
     *
     * @param User $user
     * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
     * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
     * @validate $user In2code\Femanager\Domain\Validator\CaptchaValidator
     * @return void
     */
    public function createAction(User $user)
    {
        $user = UserUtility::overrideUserGroup($user, $this->settings);
        $user = FrontendUtility::forceValues(
            $user,
            $this->config['new.']['forceValues.']['beforeAnyConfirmation.']
        );
        $user = UserUtility::fallbackUsernameAndPassword($user);
        if ($this->settings['new']['fillEmailWithUsername'] == 1) {
            $user->setEmail($user->getUsername());
        }
        UserUtility::hashPassword($user, $this->settings['new']['misc']['passwordSave']);
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
     * @param int $user User UID
     * @param string $hash Given hash
     * @param string $status
     *            "userConfirmation", "userConfirmationRefused", "adminConfirmation",
     *            "adminConfirmationRefused", "adminConfirmationRefusedSilent"
     * @return void
     */
    public function confirmCreateRequestAction($user, $hash, $status = 'adminConfirmation')
    {
        $user = $this->userRepository->findByUid($user);
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__ . 'BeforePersist',
            array($user, $hash, $status, $this)
        );
        if ($user === null) {
            $this->addFlashMessage(
                LocalizationUtility::translate('missingUserInDatabase', 'femanager'),
                '',
                FlashMessage::ERROR
            );
            $this->redirect('new');
        }

        switch ($status) {

            // registration confirmed by user
            case 'userConfirmation':
                if (StringUtility::createHash($user->getUsername()) === $hash) {

                    // if user is already confirmed by himself
                    if ($user->getTxFemanagerConfirmedbyuser()) {
                        $this->addFlashMessage(
                            LocalizationUtility::translate('userAlreadyConfirmed', 'femanager'),
                            '',
                            FlashMessage::ERROR
                        );
                        $this->redirect('new');
                    }
                    $user = FrontendUtility::forceValues(
                        $user,
                        $this->config['new.']['forceValues.']['onUserConfirmation.']
                    );
                    $user->setTxFemanagerConfirmedbyuser(true);
                    $this->userRepository->update($user);
                    $this->persistenceManager->persistAll();
                    LogUtility::log(
                        LocalizationUtility::translate('tx_femanager_domain_model_log.state.102', 'femanager'),
                        102,
                        $user
                    );

                    // must be still confirmed from admin
                    if (!empty($this->settings['new']['confirmByAdmin']) && !$user->getTxFemanagerConfirmedbyadmin()) {
                        // send email to admin to get this confirmation
                        $this->sendMail->send(
                            'createAdminConfirmation',
                            StringUtility::makeEmailArray(
                                $this->settings['new']['confirmByAdmin'],
                                $this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
                            ),
                            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                            'New Registration request',
                            array(
                                'user' => $user,
                                'hash' => StringUtility::createHash($user->getUsername() . $user->getUid())
                            ),
                            $this->config['new.']['email.']['createAdminConfirmation.']
                        );

                        $this->addFlashMessage(
                            LocalizationUtility::translate('createRequestWaitingForAdminConfirm', 'femanager')
                        );

                    } else {
                        $user->setDisable(false);
                        $this->addFlashMessage(LocalizationUtility::translate('create', 'femanager'));
                        LogUtility::log(
                            LocalizationUtility::translate('tx_femanager_domain_model_log.state.101', 'femanager'),
                            101,
                            $user
                        );
                        $this->finalCreate($user, 'new', 'createStatus', true, $status);
                    }

                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('createFailedProfile', 'femanager'),
                        '',
                        FlashMessage::ERROR
                    );
                    return;
                }
                break;

            case 'userConfirmationRefused':
                if (StringUtility::createHash($user->getUsername()) === $hash) {
                    LogUtility::log(
                        LocalizationUtility::translate('tx_femanager_domain_model_log.state.104', 'femanager'),
                        104,
                        $user
                    );
                    $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted', 'femanager'));
                    $this->userRepository->remove($user);
                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('createFailedProfile', 'femanager'),
                        '',
                        FlashMessage::ERROR
                    );
                    return;
                }
                break;

            case 'adminConfirmation':
                // registration complete
                if (StringUtility::createHash($user->getUsername() . $user->getUid())) {
                    $user = FrontendUtility::forceValues(
                        $user,
                        $this->config['new.']['forceValues.']['onAdminConfirmation.']
                    );
                    $user->setTxFemanagerConfirmedbyadmin(true);
                    if ($user->getTxFemanagerConfirmedbyuser() || empty($this->settings['new']['confirmByUser'])) {
                        $user->setDisable(false);
                    }
                    $this->addFlashMessage(LocalizationUtility::translate('create', 'femanager'));
                    LogUtility::log(
                        LocalizationUtility::translate('tx_femanager_domain_model_log.state.103', 'femanager'),
                        103,
                        $user
                    );
                    $this->finalCreate($user, 'new', 'createStatus', false, $status);

                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('createFailedProfile', 'femanager'),
                        '',
                        FlashMessage::ERROR
                    );
                    return;
                }
                break;

            case 'adminConfirmationRefused':
                // Admin refuses profile
            case 'adminConfirmationRefusedSilent':
                if (StringUtility::createHash($user->getUsername() . $user->getUid())) {
                    LogUtility::log(
                        LocalizationUtility::translate('tx_femanager_domain_model_log.state.105', 'femanager'),
                        105,
                        $user
                    );
                    $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted', 'femanager'));
                    if (!stristr($status, 'silent')) {
                        // send email to user to inform him about his profile confirmation
                        $this->sendMail->send(
                            'CreateUserNotifyRefused',
                            StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
                            array('sender@femanager.org' => 'Sender Name'),
                            'Your profile was refused',
                            array('user' => $user),
                            $this->config['new.']['email.']['createUserNotifyRefused.']
                        );
                    }
                    $this->userRepository->remove($user);

                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('createFailedProfile', 'femanager'),
                        '',
                        FlashMessage::ERROR
                    );
                    return;
                }
                break;

            default:

        }

        /**
         * redirect by TypoScript setting
         *        [userConfirmation|userConfirmationRefused|adminConfirmation|
         *        adminConfirmationRefused|adminConfirmationRefusedSilent]Redirect
         */
        $this->redirectByAction('new', $status . 'Redirect');
        $this->redirect('new');
    }

    /**
     * Just for showing informations after user creation
     *
     * @return void
     */
    public function createStatusAction()
    {
    }

}
