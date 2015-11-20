<?php
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use In2code\Femanager\Utility\FrontendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * Edit Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
 */
class EditController extends AbstractController
{

    /**
     * action edit
     *
     * @return void
     */
    public function editAction()
    {
        $this->view->assign('user', $this->user);
        $this->view->assign('allUserGroups', $this->allUserGroups);
        $this->assignForAll();
    }

    /**
     * Init for User creation
     *
     * @return void
     */
    public function initializeUpdateAction()
    {
        $user = UserUtility::getCurrentUser();
        $userValues = $this->request->getArgument('user');
        $this->testSpoof($user, $userValues['__identity']);

        // workarround for empty usergroups
        if ((int) $this->pluginVariables['user']['usergroup'][0]['__identity'] === 0) {
            unset($this->pluginVariables['user']['usergroup']);
        }
        // keep password if empty
        if (
            isset($this->settings['edit']['misc']['keepPasswordIfEmpty']) &&
            $this->settings['edit']['misc']['keepPasswordIfEmpty'] == '1' &&
            isset($this->pluginVariables['user']['password']) &&
            $this->pluginVariables['user']['password'] === '' &&
            isset($this->pluginVariables['password_repeat']) &&
            $this->pluginVariables['password_repeat'] === ''
        ) {
            unset($this->pluginVariables['user']['password']);
            unset($this->pluginVariables['password_repeat']);
        }
        $this->request->setArguments($this->pluginVariables);
    }

    /**
     * action update
     *
     * @param User $user
     * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
     * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
     * @validate $user In2code\Femanager\Domain\Validator\CaptchaValidator
     * @return void
     */
    public function updateAction(User $user)
    {
        // check if there are no changes
        if (!ObjectUtility::isDirtyObject($user)) {
            $this->addFlashMessage(LocalizationUtility::translate('noChanges'), '', FlashMessage::NOTICE);
            $this->redirect('edit');
        }

        /** @var User $user */
        $user = FrontendUtility::forceValues(
            $user,
            $this->config['edit.']['forceValues.']['beforeAnyConfirmation.']
        );
        if ($this->settings['edit']['fillEmailWithUsername'] === '1') {
            $user->setEmail($user->getUsername());
        }

        // convert password to md5 or sha1 hash
        if (array_key_exists('password', UserUtility::getDirtyPropertiesFromUser($user))) {
            UserUtility::hashPassword($user, $this->settings['edit']['misc']['passwordSave']);
        }

        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', array($user, $this));

        if (!empty($this->settings['edit']['confirmByAdmin'])) {
            $this->updateRequest($user);
        } else {
            $this->updateAllConfirmed($user);
        }

        $this->redirect('edit');
    }

    /**
     * Update if hash is ok
     *
     * @param User $user User object
     * @param string $hash
     * @param string $status could be "confirm", "refuse", "silentRefuse"
     * @return void
     */
    public function confirmUpdateRequestAction(User $user, $hash, $status = 'confirm')
    {
        $this->view->assign('user', $user);

        // if wrong hash or if no update xml
        if (
            StringUtility::createHash($user->getUsername() . $user->getUid()) !== $hash ||
            !$user->getTxFemanagerChangerequest()
        ) {
            $this->addFlashMessage(LocalizationUtility::translate('updateFailedProfile'), '', FlashMessage::ERROR);
            return;
        }

        switch ($status) {
            case 'confirm':
                // overwrite properties
                $values = GeneralUtility::xml2array($user->getTxFemanagerChangerequest(), '', 0, 'changes');
                foreach ((array) $values as $field => $value) {
                    if ($field != 'usergroup' && method_exists($user, 'set' . ucfirst($field))) {
                        $user->{'set' . ucfirst($field)}($value['new']);
                    } else {
                        $user->removeAllUsergroups();
                        $usergroupUids = GeneralUtility::trimExplode(',', $value['new'], true);
                        foreach ($usergroupUids as $usergroupUid) {
                            /** @var UserGroup $usergroup */
                            $usergroup = $this->userGroupRepository->findByUid($usergroupUid);
                            $user->addUsergroup($usergroup);
                        }
                    }
                }
                $user = FrontendUtility::forceValues(
                    $user,
                    $this->config['edit.']['forceValues.']['onAdminConfirmation.']
                );
                LogUtility::log(Log::STATUS_PROFILEUPDATECONFIRMEDADMIN, $user);
                $this->addFlashMessage(LocalizationUtility::translate('updateProfile'));
                break;

            case 'refuse':
                $this->sendMail->send(
                    'updateRequestRefused',
                    StringUtility::makeEmailArray(
                        $user->getEmail(),
                        $user->getFirstName() . ' ' . $user->getLastName()
                    ),
                    array('sender@femanager.org' => 'Sender Name'),
                    'Your change request was refused',
                    array(
                        'user' => $user,
                        'settings' => $this->settings
                    ),
                    $this->config['edit.']['email.']['updateRequestRefused.']
                );
                LogUtility::log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
                $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
                break;

            case 'silentRefuse':
                LogUtility::log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
                $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
                break;

            default:

        }

        $user->setTxFemanagerChangerequest('');
        $this->userRepository->update($user);
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__ . 'AfterPersist',
            array($user, $hash, $status, $this)
        );
    }

    /**
     * action delete
     *
     * @param User $user
     * @return void
     */
    public function deleteAction(User $user)
    {
        LogUtility::log(Log::STATUS_PROFILEDELETE, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEDELETE));
        $this->userRepository->remove($user);
        $this->redirectByAction('delete');
        $this->redirect('edit');
    }

}
