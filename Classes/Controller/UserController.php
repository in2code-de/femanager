<?php
declare(strict_types = 1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\ClientsideValidator;
use In2code\Femanager\Event\ImpersonateEvent;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class UserController
 */
class UserController extends AbstractFrontendController
{

    /**
     * @param array $filter
     */
    public function listAction(array $filter = [])
    {
        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findByUsergroups(
                    $this->settings['list']['usergroup'],
                    $this->settings,
                    $filter
                ),
                'filter' => $filter
            ]
        );
        $this->assignForAll();
    }

    /**
     * Enforce user setting from FlexForm and ignore &tx_femanager_pi1[user]=421
     */
    public function initializeShowAction()
    {
        $arguments = $this->request->getArguments();
        if (!empty($this->settings['show']['user'])) {
            unset($arguments['user']);
        }
        $this->request->setArguments($arguments);
    }

    /**
     * @param User $user
     */
    public function showAction(User $user = null)
    {
        $this->view->assign('user', $this->getUser($user));
        $this->assignForAll();
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    public function imageDeleteAction(User $user)
    {
        if (UserUtility::getCurrentUser() !== $user) {
            throw new UnauthorizedException('You are not allowed to delete this image', 1516373759972);
        }
        $user->setImage($this->objectManager->get(ObjectStorage::class));
        $this->userRepository->update($user);
        $this->logUtility->log(Log::STATUS_PROFILEUPDATEIMAGEDELETE, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEIMAGEDELETE));
        $this->redirectToUri(FrontendUtility::getUriToCurrentPage());
    }

    /**
     * Call this Action from eID to validate field values
     *
     * @param string $validation Validation string like "required, email, min(10)"
     * @param string $value Given Field value
     * @param string $field Fieldname like "username" or "email"
     * @param User $user Existing User
     * @param string $additionalValue Additional Values
     * @param int $plugin tt_content.uid of the femanager plugin
     * @param string $referrerAction current action name
     */
    public function validateAction(
        $validation = null,
        $value = null,
        $field = null,
        User $user = null,
        $additionalValue = '',
        int $plugin = 0,
        string $referrerAction = ''
    ) {
        $clientsideValidator = $this->objectManager->get(ClientsideValidator::class);
        $result = $clientsideValidator
            ->setValidationSettingsString($validation)
            ->setValue($value)
            ->setFieldName($field)
            ->setUser($user)
            ->setAdditionalValue($additionalValue)
            ->setPlugin($plugin)
            ->setActionName($referrerAction)
            ->validateField();

        $this->view->assignMultiple(
            [
                'isValid' => $result,
                'messages' => $clientsideValidator->getMessages(),
                'validation' => $validation,
                'value' => $value,
                'fieldname' => $field,
                'user' => $user
            ]
        );
    }

    /**
     * Simulate frontenduser login for backend adminstrators only
     *
     * @param User $user
     * @throws UnauthorizedException
     */
    public function loginAsAction(User $user)
    {
        $this->eventDispatcher->dispatch(new ImpersonateEvent($user));

        if (!BackendUserUtility::isAdminAuthentication()) {
            throw new UnauthorizedException(LocalizationUtility::translate('error_not_authorized'), 1516373787864);
        }
        UserUtility::login($user);
        $this->redirectByAction('loginAs', 'redirect');
        $this->redirectToUri('/');
    }

    /**
     * @param User $user
     * @return User
     */
    protected function getUser(User $user = null)
    {
        if ($user === null) {
            if (is_numeric($this->settings['show']['user'])) {
                $user = $this->userRepository->findByUid($this->settings['show']['user']);
            } elseif ($this->settings['show']['user'] === '[this]') {
                $user = $this->user;
            }
        }
        return $user;
    }
}
