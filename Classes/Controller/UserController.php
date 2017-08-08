<?php
declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\FileUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;

/**
 * Class UserController
 */
class UserController extends AbstractController
{

    /**
     * @var \In2code\Femanager\Domain\Validator\ClientsideValidator
     * @inject
     */
    protected $clientsideValidator;

    /**
     * @param array $filter
     * @return void
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
     * @param User $user
     * @return void
     */
    public function showAction(User $user = null)
    {
        if (!is_object($user)) {
            if (is_numeric($this->settings['show']['user'])) {
                $user = $this->userRepository->findByUid($this->settings['show']['user']);
            } elseif ($this->settings['show']['user'] === '[this]') {
                $user = $this->user;
            }
        }
        $this->view->assign('user', $user);
        $this->assignForAll();
    }

    /**
     * @return string
     */
    public function fileUploadAction(): string
    {
        $fileName = FileUtility::uploadFile();
        header('Content-Type: text/plain');
        $result = [
            'success' => ($fileName ? true : false),
            'uploadName' => $fileName
        ];
        return json_encode($result);
    }

    /**
     * @return void
     */
    public function fileDeleteAction()
    {
    }

    /**
     * Call this Action from eID to validate field values
     *
     * @param string $validation Validation string like "required, email, min(10)"
     * @param string $value Given Field value
     * @param string $field Fieldname like "username" or "email"
     * @param User $user Existing User
     * @param string $additionalValue Additional Values
     * @return void
     */
    public function validateAction(
        $validation = null,
        $value = null,
        $field = null,
        User $user = null,
        $additionalValue = ''
    ) {
        $result = $this->clientsideValidator
            ->setValidationSettingsString($validation)
            ->setValue($value)
            ->setFieldName($field)
            ->setUser($user)
            ->setAdditionalValue($additionalValue)
            ->validateField();

        $this->view->assignMultiple(
            [
                'isValid' => $result,
                'messages' => $this->clientsideValidator->getMessages(),
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
     * @return void
     */
    public function loginAsAction(User $user)
    {
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, [$user, $this]);
        if (!BackendUserUtility::isAdminAuthentication()) {
            throw new UnauthorizedException(LocalizationUtility::translate('error_not_authorized'));
        }
        UserUtility::login($user);
        $this->redirectByAction('loginAs', 'redirect');
        $this->redirectToUri('/');
    }
}
