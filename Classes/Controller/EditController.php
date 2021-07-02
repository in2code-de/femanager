<?php
declare(strict_types = 1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Event\AfterUserUpdateEvent;
use In2code\Femanager\Event\BeforeUpdateUserEvent;
use In2code\Femanager\Event\DeleteUserEvent;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

/**
 * Class EditController
 */
class EditController extends AbstractFrontendController
{
    public function editAction()
    {
        $token = '';
        if ($this->user) {
            $token = GeneralUtility::hmac($this->user->getUid(), (string)$this->user->getCrdate()->getTimestamp());
        }
        $this->view->assignMultiple([
            'user' => $this->user,
            'allUserGroups' => $this->allUserGroups,
            'token' => $token
        ]);
        $this->assignForAll();
    }

    public function initializeUpdateAction()
    {
        $user = UserUtility::getCurrentUser();
        $userValues = $this->request->getArgument('user');
        $token = $this->request->getArgument('token');

        $this->testSpoof($user, $userValues['__identity'], $token);
        if ($this->keepPassword()) {
            unset($this->pluginVariables['user']['password']);
            unset($this->pluginVariables['password_repeat']);
        }
        $this->request->setArguments($this->pluginVariables);
    }

    /**
     * @param User $user
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\CaptchaValidator", param="user")
     */
    public function updateAction(User $user)
    {
        $this->redirectIfDirtyObject($user);
        $user = FrontendUtility::forceValues($user, $this->config['edit.']['forceValues.']['beforeAnyConfirmation.']);
        $this->emailForUsername($user);
        UserUtility::convertPassword($user, $this->settings['edit']['misc']['passwordSave']);
        $this->eventDispatcher->dispatch(new BeforeUpdateUserEvent($user));
        if (!empty($this->settings['edit']['confirmByAdmin'])) {
            $this->updateRequest($user);
        } else {
            $this->updateAllConfirmed($user);
        }
        $this->redirect('edit');
    }

    /**
     * @param User $user User object
     * @param string $hash
     * @param string $status could be "confirm", "refuse", "silentRefuse"
     */
    public function confirmUpdateRequestAction(User $user, $hash, $status = 'confirm')
    {
        $this->view->assign('user', $user);
        if (!HashUtility::validHash($hash, $user) || !$user->getTxFemanagerChangerequest()) {
            $this->addFlashMessage(LocalizationUtility::translate('updateFailedProfile'), '', FlashMessage::ERROR);
            return;
        }
        switch ($status) {
            case 'confirm':
                $this->statusConfirm($user);
                break;
            case 'refuse':
                $this->statusRefuse($user);
                break;
            case 'silentRefuse':
                $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
                $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
                break;
            default:
        }
        $user->setTxFemanagerChangerequest('');
        $this->userRepository->update($user);

        $this->eventDispatcher->dispatch(new AfterUserUpdateEvent($user, $hash, $status));
    }

    /**
     * Status update confirmation
     *
     * @param User $user
     */
    protected function statusConfirm(User $user)
    {
        $values = GeneralUtility::xml2array($user->getTxFemanagerChangerequest());
        foreach ((array)$values as $field => $value) {
            if ($field !== 'usergroup' && method_exists($user, 'set' . ucfirst($field))) {
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
        $user = FrontendUtility::forceValues($user, $this->config['edit.']['forceValues.']['onAdminConfirmation.']);
        $this->logUtility->log(Log::STATUS_PROFILEUPDATECONFIRMEDADMIN, $user);
        $this->addFlashMessage(LocalizationUtility::translate('updateProfile'));
    }

    /**
     * Status update refused
     *
     * @param User $user
     */
    protected function statusRefuse(User $user)
    {
        $this->sendMailService->send(
            'updateRequestRefused',
            StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
            ['sender@femanager.org' => 'Sender Name'],
            'Your change request was refused',
            [
                'user' => $user,
                'settings' => $this->settings
            ],
            $this->config['edit.']['email.']['updateRequestRefused.']
        );
        $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
    }

    /**
     * action delete
     *
     * @param User $user
     */
    public function deleteAction(User $user)
    {
        $this->eventDispatcher->dispatch(new DeleteUserEvent($user));
        $this->logUtility->log(Log::STATUS_PROFILEDELETE, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEDELETE));
        $this->userRepository->remove($user);
        $this->redirectByAction('delete');
        $this->redirect('edit');
    }

    /**
     * Check if password should be kept
     *
     *      If password is empty
     *      If password repeat is also empty
     *      If keepPasswordIfEmpty configuration is turned on
     *
     * @return bool
     */
    protected function keepPassword()
    {
        return !empty($this->settings['edit']['misc']['keepPasswordIfEmpty']) &&
        empty($this->pluginVariables['user']['password']) &&
        empty($this->pluginVariables['password_repeat']);
    }

    /**
     * Check: If there are no changes, simple redirect back
     *
     * @param User $user
     * @throws UnsupportedRequestTypeException
     */
    protected function redirectIfDirtyObject(User $user)
    {
        if (!ObjectUtility::isDirtyObject($user)) {
            $this->addFlashMessage(LocalizationUtility::translate('noChanges'), '', FlashMessage::NOTICE);
            $this->redirect('edit');
        }
    }

    /**
     * @param User $user
     */
    protected function emailForUsername(User $user)
    {
        if ($this->settings['edit']['fillEmailWithUsername'] === '1') {
            $user->setEmail($user->getUsername());
        }
    }
}
