<?php
declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class UserBackendController
 */
class UserBackendController extends AbstractController
{

    /**
     * @param array $filter
     * @return void
     */
    public function listAction(array $filter = [])
    {
        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackend($filter),
                'moduleUri' => BackendUtility::getModuleUrl('tce_db')
            ]
        );
    }

    /**
     * @param User $user
     * @return void
     */
    public function userLogoutAction(User $user)
    {
        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        $this->redirect('list');
    }
}
