<?php
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Service\SendParametersService;
use In2code\Femanager\Domain\Service\StoreInDatabaseService;
use In2code\Femanager\Utility\FileUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
 * Abstract Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
 */
abstract class AbstractController extends ActionController
{

    /**
     * @var \In2code\Femanager\Domain\Repository\UserRepository
     * @inject
     */
    protected $userRepository;

    /**
     * @var \In2code\Femanager\Domain\Repository\UserGroupRepository
     * @inject
     */
    protected $userGroupRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection = null;

    /**
     * @var \In2code\Femanager\Domain\Service\SendMailService
     * @inject
     */
    protected $sendMail;

    /**
     * Content Object
     *
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * Former known as piVars
     *
     * @var array
     */
    public $pluginVariables;

    /**
     * TypoScript
     *
     * @var array
     */
    public $config;

    /**
     * Complete Configuration
     *
     * @var array
     */
    public $allConfig;

    /**
     * Current logged in user object
     *
     * @var User
     */
    public $user;

    /**
     * All available usergroups
     *
     * @var object
     */
    public $allUserGroups;

    /**
     * Prefix method to createAction()
     *        Create Confirmation from Admin is not necessary
     *
     * @param User $user
     * @return void
     */
    public function createAllConfirmed(User $user)
    {
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->addFlashMessage(LocalizationUtility::translate('create'));
        LogUtility::log(Log::STATUS_NEWREGISTRATION, $user);
        $this->finalCreate($user, 'new', 'createStatus');
    }

    /**
     * Prefix method to createAction(): Create must be confirmed by Admin
     *
     * @param User $user
     * @return void
     */
    public function createRequest(User $user)
    {
        $user->setDisable(true);
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        LogUtility::log(Log::STATUS_PROFILECREATIONREQUEST, $user);
        if (!empty($this->settings['new']['confirmByUser'])) {
            $this->createUserConfirmationRequest($user);
        }
        if (!empty($this->settings['new']['confirmByAdmin'])) {
            $this->createAdminConfirmationRequest($user);
        }
    }

    /**
     * Send email to user for confirmation
     *
     * @param User $user
     * @return void
     * @throws UnsupportedRequestTypeException
     */
    protected function createUserConfirmationRequest(User $user)
    {
        $this->sendMail->send(
            'createUserConfirmation',
            StringUtility::makeEmailArray(
                $user->getEmail(),
                $user->getUsername()
            ),
            array(
                $this->settings['new']['email']['createUserConfirmation']['sender']['email']['value'] =>
                    $this->settings['settings']['new']['email']['createUserConfirmation']['sender']['name']['value']
            ),
            'Confirm your profile creation request',
            array(
                'user' => $user,
                'hash' => HashUtility::createHashForUser($user)
            ),
            $this->config['new.']['email.']['createUserConfirmation.']
        );
        $this->redirectByAction('new', 'requestRedirect');
        $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForUserConfirm'));
        $this->redirect('new');
    }

    /**
     * Send email to admin for confirmation
     *
     * @param User $user
     * @throws UnsupportedRequestTypeException
     */
    protected function createAdminConfirmationRequest(User $user)
    {
        $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForAdminConfirm'));
        $this->sendMail->send(
            'createAdminConfirmation',
            StringUtility::makeEmailArray(
                $this->settings['new']['confirmByAdmin'],
                $this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
            ),
            StringUtility::makeEmailArray(
                $user->getEmail(),
                $user->getUsername()
            ),
            'New Registration request',
            array(
                'user' => $user,
                'hash' => HashUtility::createHashForUser($user)
            ),
            $this->config['new.']['email.']['createAdminConfirmation.']
        );
        $this->redirect('new');
    }

    /**
     * Prefix method to updateAction()
     *        Update Confirmation from Admin is not necessary
     *
     * @param User $user
     * @return void
     */
    public function updateAllConfirmed(User $user)
    {

        // send notify email to admin
        if ($this->settings['edit']['notifyAdmin']) {
            $existingUser = $this->userRepository->findByUid($user->getUid());
            $dirtyProperties = UserUtility::getDirtyPropertiesFromUser($existingUser);
            $this->sendMail->send(
                'updateNotify',
                StringUtility::makeEmailArray(
                    $this->settings['edit']['notifyAdmin'],
                    $this->settings['edit']['email']['notifyAdmin']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray(
                    $user->getEmail(),
                    $user->getUsername()
                ),
                'Profile update',
                array(
                    'user' => $user,
                    'changes' => $dirtyProperties,
                    'settings' => $this->settings
                ),
                $this->config['edit.']['email.']['notifyAdmin.']
            );
        }

        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $this));
        LogUtility::log(Log::STATUS_PROFILEUPDATED, $user);
        $this->redirectByAction('edit');
        $this->addFlashMessage(LocalizationUtility::translate('update'));
    }

    /**
     * Prefix method to updateAction(): Update must be confirmed by Admin
     *
     * @param User $user
     * @return void
     */
    public function updateRequest($user)
    {
        $dirtyProperties = UserUtility::getDirtyPropertiesFromUser($user);
        $user = UserUtility::rollbackUserWithChangeRequest($user, $dirtyProperties);
        $this->sendMail->send(
            'updateRequest',
            array(
                $this->settings['edit']['confirmByAdmin'] =>
                    $this->settings['edit']['email']['updateRequest']['sender']['name']['value']
            ),
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            'New Profile change request',
            array(
                'user' => $user,
                'changes' => $dirtyProperties,
                'hash' => HashUtility::createHashForUser($user)
            ),
            $this->config['edit.']['email.']['updateRequest.']
        );
        LogUtility::log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
        $this->redirectByAction('edit', 'requestRedirect');
        $this->addFlashMessage(LocalizationUtility::translate('updateRequest'));
    }

    /**
     * Some additional actions after profile creation
     *
     * @param User $user
     * @param string $action
     * @param string $redirectByActionName Action to redirect
     * @param bool $login Login after creation
     * @param string $status
     * @return void
     */
    public function finalCreate($user, $action, $redirectByActionName, $login = true, $status = '')
    {
        // persist user (otherwise login is not possible if user is still disabled)
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        // login user
        if ($login) {
            $this->loginAfterCreate($user);
        }

        // send notify email to user
        $this->sendMail->send(
            'createUserNotify',
            StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
            array(
                $this->settings['new']['email']['createUserNotify']['sender']['email']['value'] =>
                    $this->settings['settings']['new']['email']['createUserNotify']['sender']['name']['value']
            ),
            'Profile creation',
            array(
                'user' => $user,
                'settings' => $this->settings
            ),
            $this->config['new.']['email.']['createUserNotify.']
        );

        // send notify email to admin
        if ($this->settings['new']['notifyAdmin']) {
            $this->sendMail->send(
                'createNotify',
                StringUtility::makeEmailArray(
                    $this->settings['new']['notifyAdmin'],
                    $this->settings['new']['email']['createAdminNotify']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation',
                array(
                    'user' => $user,
                    'settings' => $this->settings
                ),
                $this->config['new.']['email.']['createAdminNotify.']
            );
        }

        // sendpost: send values via POST to any target
        /** @var SendParametersService $sendParametersService */
        $sendParametersService = $this->objectManager->get(
            'In2code\\Femanager\\Domain\\Service\\SendParametersService',
            $this->config['new.']['sendPost.']
        );
        $sendParametersService->send($user);

        // store in database: store values in any wanted table
        $this->storeInDatabasePreflight($user);

        // add signal after user generation
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $action, $this));

        // frontend redirect (if activated via TypoScript)
        $this->redirectByAction($action, ($status ? $status . 'Redirect' : 'redirect'));

        // go to an action
        $this->redirect($redirectByActionName);
    }

    /**
     * Login FE-User after creation
     *
     * @param User $user
     * @return void
     */
    protected function loginAfterCreate($user)
    {
        if ($this->config['new.']['login'] != 1) {
            return;
        }

        $GLOBALS['TSFE']->fe_user->checkPid = false;
        $info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();

        $pids = $this->allConfig['persistence']['storagePid'];
        $extraWhere = ' AND pid IN (' . $this->databaseConnection->cleanIntList($pids) . ')';
        $user = $GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $user->getUsername(), $extraWhere);

        $GLOBALS['TSFE']->fe_user->createUserSession($user);
        $GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->fetchUserSession();

        // add login flashmessage
        $this->addFlashMessage(LocalizationUtility::translate('login'), '', FlashMessage::NOTICE);
    }

    /**
     * Redirect by TypoScript setting
     *        [userConfirmation|userConfirmationRefused|adminConfirmation|
     *        adminConfirmationRefused|adminConfirmationRefusedSilent]Redirect
     *
     * @param string $action "new", "edit"
     * @param string $category "redirect", "requestRedirect" value from TypoScript
     * @return void
     */
    protected function redirectByAction($action = 'new', $category = 'redirect')
    {
        $target = null;
        // redirect from TypoScript cObject
        if (
            $this->cObj->cObjGetSingle(
                $this->config[$action . '.'][$category],
                $this->config[$action . '.'][$category . '.']
            )
        ) {
            $target = $this->cObj->cObjGetSingle(
                $this->config[$action . '.'][$category],
                $this->config[$action . '.'][$category . '.']
            );
        }

        // if redirect target
        if ($target) {
            $this->uriBuilder->setTargetPageUid($target);
            $this->uriBuilder->setLinkAccessRestrictedPages(true);
            $link = $this->uriBuilder->build();
            $this->redirectToUri($link);
        }
    }

    /**
     * Init for User creation
     *
     * @return void
     */
    public function initializeCreateAction()
    {
        // workarround for empty usergroups
        if ((int) $this->pluginVariables['user']['usergroup'][0] === 0) {
            unset($this->pluginVariables['user']['usergroup']);
        }
        $this->request->setArguments($this->pluginVariables);
    }

    /**
     * Init for User delete action
     *
     * @return void
     */
    protected function initializeDeleteAction()
    {
        $user = UserUtility::getCurrentUser();
        $uid = $this->request->getArgument('user');
        $this->testSpoof($user, $uid);
    }

    /**
     * Check if user is authenticated
     *
     * @param \In2code\Femanager\Domain\Model\User $user
     * @param int $uid Given fe_users uid
     * @return void
     */
    protected function testSpoof($user, $uid)
    {
        if ($user->getUid() !== (int) $uid && $uid > 0) {
            LogUtility::log(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                FlashMessage::ERROR
            );
            $this->forward('edit');
        }
    }

    /**
     * Assigns all values, which should be available in all views
     *
     * @return void
     */
    public function assignForAll()
    {
        $this->view->assignMultiple(
            array(
                'languageUid' => FrontendUtility::getFrontendLanguageUid(),
                'storagePid' => $this->allConfig['persistence']['storagePid'],
                'Pid' => FrontendUtility::getCurrentPid(),
                'actionName' => $this->actionMethodName,
                'uploadFolder' => FileUtility::getUploadFolderFromTca()
            )
        );
    }

    /**
     * Init
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->controllerContext = $this->buildControllerContext();
        $this->user = UserUtility::getCurrentUser();
        $this->cObj = $this->configurationManager->getContentObject();
        $this->pluginVariables = $this->request->getArguments();
        $this->allConfig = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $this->config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $this->config = $this->config['plugin.']['tx_femanager.']['settings.'];
        $controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
        $removeFromUserGroupSelection = $this->settings[$controllerName]['misc']['removeFromUserGroupSelection'];
        $this->allUserGroups = $this->userGroupRepository->findAllForFrontendSelection($removeFromUserGroupSelection);

        if (isset($this->arguments['user'])) {
            $this->arguments['user']
                ->getPropertyMappingConfiguration()
                ->forProperty('dateOfBirth')
                ->setTypeConverterOption(
                    'TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter',
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                    LocalizationUtility::translate('tx_femanager_domain_model_user.dateFormat')
                );
        }
        // check if ts is included
        if ($this->settings['_TypoScriptIncluded'] !== '1' && !GeneralUtility::_GP('eID') && TYPO3_MODE !== 'BE') {
            $this->addFlashMessage(LocalizationUtility::translate('error_no_typoscript'), '', FlashMessage::ERROR);
        }

        // check if storage pid was set
        if (
            (int) $this->allConfig['persistence']['storagePid'] === 0
            && !GeneralUtility::_GP('eID')
            && TYPO3_MODE !== 'BE'
        ) {
            $this->addFlashMessage(LocalizationUtility::translate('error_no_storagepid'), '', FlashMessage::ERROR);
        }
    }

    /**
     * Store user values in any database table
     *
     * @param User $user User properties
     * @return void
     */
    protected function storeInDatabasePreflight($user)
    {
        $uid = 0;
        if (!empty($this->config['new.']['storeInDatabase.'])) {
            // one loop for every table to store
            foreach ((array) $this->config['new.']['storeInDatabase.'] as $table => $storeSettings) {
                $storeSettings = null;
                if (
                    $this->cObj->cObjGetSingle(
                        $this->config['new.']['storeInDatabase.'][$table]['_enable'],
                        $this->config['new.']['storeInDatabase.'][$table]['_enable.']
                    ) === '1'
                ) {
                    $this->cObj->start(
                        array_merge($user->_getProperties(), array('lastGeneratedUid' => $uid))
                    );

                    /** @var StoreInDatabaseService $storeInDatabase */
                    $storeInDatabase = $this->objectManager->get(
                        'In2code\\Femanager\\Domain\\Service\\StoreInDatabaseService'
                    );
                    $storeInDatabase->setTable($table);
                    foreach ($this->config['new.']['storeInDatabase.'][$table] as $field => $value) {
                        if ($field[0] === '_' || stristr($field, '.')) {
                            continue;
                        }
                        $value = $this->cObj->cObjGetSingle(
                            $this->config['new.']['storeInDatabase.'][$table][$field],
                            $this->config['new.']['storeInDatabase.'][$table][$field . '.']
                        );
                        $storeInDatabase->addProperty($field, $value);
                    }
                    $uid = $storeInDatabase->execute();
                }
            }
        }
    }

    /**
     * Deactivate errormessages in flashmessages
     *
     * @return bool
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

}
