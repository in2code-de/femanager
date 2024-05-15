<?php

declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Event\AfterUserUpdateEvent;
use In2code\Femanager\Event\BeforeUpdateUserEvent;
use In2code\Femanager\Event\DeleteUserEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

/**
 * Class EditController
 */
class EditController extends AbstractFrontendController
{
    public function editAction(): ResponseInterface
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
        return $this->htmlResponse();
    }

    public function initializeUpdateAction()
    {
        if ($this->keepPassword()) {
            unset($this->pluginVariables['user']['password']);
            unset($this->pluginVariables['password_repeat']);
        }
        $this->request->setArguments($this->pluginVariables);
    }

    /**
     * @param User $user
     * @Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * @Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     * @Validate("In2code\Femanager\Domain\Validator\CaptchaValidator", param="user")
     */
    public function updateAction(User $user)
    {
        $currentUser = UserUtility::getCurrentUser();
        $userValues = $this->request->hasArgument('user') ? $this->request->getArgument('user') : null;
        $token = $this->request->hasArgument('token') ? $this->request->getArgument('token') : null;

        if ($currentUser === null ||
            empty($userValues['__identity']) ||
            (int)$userValues['__identity'] === null ||
            $token === null ||
            $this->isSpoof($currentUser, (int)$userValues['__identity'], $token)
        ) {
            $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                AbstractMessage::ERROR
            );
            return new ForwardResponse('edit');
        }

        $response = $this->redirectIfNoChangesOnObject($user);
        if ($response !== null) {
            return $response;
        }
        $user = FrontendUtility::forceValues(
            $user,
            ConfigurationUtility::getValue('edit./forceValues./beforeAnyConfirmation.', $this->config)
        );

        $this->emailForUsername($user);
        UserUtility::convertPassword(
            $user,
            ConfigurationUtility::getValue('edit/misc/passwordSave', $this->settings)
        );

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
    public function confirmUpdateRequestAction(User $user, $hash, $status = 'confirm'): ResponseInterface
    {
        $this->view->assign('user', $user);
        if (!HashUtility::validHash($hash, $user) || !$user->getTxFemanagerChangerequest()) {
            $this->addFlashMessage(LocalizationUtility::translate('updateFailedProfile'), '', AbstractMessage::ERROR);
            return $this->htmlResponse(null);
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
        return $this->htmlResponse();
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
        $currentUser = UserUtility::getCurrentUser();
        $token = $this->request->hasArgument('token') ? $this->request->getArgument('token') : null;
        $uid = $this->request->hasArgument('user') ? $this->request->getArgument('user') : null;
        if ($currentUser === null ||
            $token === null ||
            $uid === null ||
            $this->isSpoof($currentUser, (int)$uid, $token)
        ) {
            $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                AbstractMessage::ERROR
            );
            return new ForwardResponse('edit');
        }

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
     */
    protected function redirectIfNoChangesOnObject(User $user)
    {
        if (!ObjectUtility::isDirtyObject($user)) {
            $this->addFlashMessage(LocalizationUtility::translate('noChanges'), '', AbstractMessage::NOTICE);
            return $this->redirect('edit');
        }
        return null;
    }

    /**
     * @param User $user
     */
    protected function emailForUsername(User $user)
    {
        $fillEmailWithUsername = ConfigurationUtility::getValue('edit/fillEmailWithUsername', $this->settings);
        if ($fillEmailWithUsername === '1') {
            $user->setEmail($user->getUsername());
        }
    }
}
