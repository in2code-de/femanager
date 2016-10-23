<?php
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\FileUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;

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
 * User Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
 */
class UserController extends AbstractController
{

    /**
     * ClientsideValidator
     *
     * @var \In2code\Femanager\Domain\Validator\ClientsideValidator
     * @inject
     */
    protected $clientsideValidator;

    /**
     * action list
     *
     * @param array $filter
     * @return void
     */
    public function listAction($filter = [])
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
     * action show
     *
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
     * File Uploader
     *
     * @return void
     */
    public function fileUploadAction()
    {
        $fileName = FileUtility::uploadFile();
        header('Content-Type: text/plain');
        $result = [
            'success' => ($fileName ? true : false),
            'uploadName' => $fileName
        ];
        echo json_encode($result);
    }

    /**
     * Showing information
     *
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
