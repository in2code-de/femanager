<?php

declare(strict_types = 1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\DataProcessor\DataProcessorRunner;
use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Event\FinalCreateEvent;
use In2code\Femanager\Event\FinalUpdateEvent;
use In2code\Femanager\Finisher\FinisherRunner;
use In2code\Femanager\Utility\BackendUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ActionController
{
    /**
     * @var \In2code\Femanager\Domain\Repository\UserRepository
     */
    protected $userRepository;

    /**
     * @var \In2code\Femanager\Domain\Repository\UserGroupRepository
     */
    protected $userGroupRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var \In2code\Femanager\Domain\Service\SendMailService
     */
    protected $sendMailService;

    /**
     * @var \In2code\Femanager\Finisher\FinisherRunner
     */
    protected $finisherRunner;

    /**
     * @var \In2code\Femanager\Utility\LogUtility
     */
    protected $logUtility;

    /**
     * Content Object
     *
     * @var ContentObjectRenderer
     */
    public $contentObject;

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
     * Module Configuration for Backend
     * this is a merge configuration -> TypoScript -> PageTSConfig -> UserTSConfig
     *
     * @var array
     */
    public $moduleConfig;

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
     * AbstractController constructor.
     * @param \In2code\Femanager\Domain\Repository\UserRepository $userRepository
     * @param \In2code\Femanager\Domain\Repository\UserGroupRepository $userGroupRepository
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     * @param \In2code\Femanager\Domain\Service\SendMailService $sendMailService
     * @param \In2code\Femanager\Finisher\FinisherRunner $finisherRunner
     * @param \In2code\Femanager\Utility\LogUtility $logUtility
     */
    public function __construct(
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository,
        PersistenceManager $persistenceManager,
        SendMailService $sendMailService,
        FinisherRunner $finisherRunner,
        LogUtility $logUtility
    ) {
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->persistenceManager = $persistenceManager;
        $this->sendMailService = $sendMailService;
        $this->finisherRunner = $finisherRunner;
        $this->logUtility = $logUtility;
    }

    /**
     * Prefix method to createAction()
     *        Create Confirmation from Admin is not necessary
     *
     * @param User $user
     */
    public function createAllConfirmed(User $user)
    {
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->logUtility->log(Log::STATUS_NEWREGISTRATION, $user);
        $this->finalCreate($user, 'new', 'createStatus');
    }

    /**
     * Prefix method to updateAction()
     *        Update Confirmation from Admin is not necessary
     *
     * @param User $user
     */
    public function updateAllConfirmed(User $user)
    {
        // send notify email to admin
        $existingUser = clone $this->userRepository->findByUid($user->getUid());
        if ($this->settings['edit']['notifyAdmin']
            || $this->settings['edit']['email']['notifyAdmin']['receiver']['email']['value']) {
            $this->sendMailService->send(
                'updateNotify',
                StringUtility::makeEmailArray(
                    $this->settings['edit']['email']['notifyAdmin']['receiver']['email']['value']
                        ?: $this->settings['edit']['notifyAdmin'],
                    $this->settings['edit']['email']['notifyAdmin']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile update',
                [
                    'user' => $user,
                    'changes' => UserUtility::getDirtyPropertiesFromUser($user),
                    'settings' => $this->settings
                ],
                $this->config['edit.']['email.']['notifyAdmin.']
            );
        }

        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new FinalUpdateEvent($user));
        $this->logUtility->log(Log::STATUS_PROFILEUPDATED, $user, ['existingUser' => $existingUser]);
        $this->redirectByAction('edit');
        $this->addFlashMessage(LocalizationUtility::translate('update'));
    }

    /**
     * Prefix method to updateAction(): Update must be confirmed by Admin
     *
     * @param User $user
     */
    public function updateRequest($user)
    {
        $dirtyProperties = UserUtility::getDirtyPropertiesFromUser($user);
        $user = UserUtility::rollbackUserWithChangeRequest($user, $dirtyProperties);
        $this->sendMailService->send(
            'updateRequest',
            StringUtility::makeEmailArray(
                $this->settings['edit']['confirmByAdmin'],
                $this->settings['edit']['email']['updateRequest']['sender']['name']['value']
            ),
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            'New Profile change request',
            [
                'user' => $user,
                'changes' => $dirtyProperties,
                'hash' => HashUtility::createHashForUser($user)
            ],
            $this->config['edit.']['email.']['updateRequest.']
        );
        $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user, ['dirtyProperties' => $dirtyProperties]);
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
     * @param bool $backend Don't redirect if called from backend action
     */
    public function finalCreate(
        $user,
        string $action,
        string $redirectByActionName,
        bool $login = true,
        string $status = '',
        bool $backend = false
    ): void {
        $this->loginPreflight($user, $login);
        $variables = ['user' => $user, 'settings' => $this->settings, 'hash' => HashUtility::createHashForUser($user)];
        $this->sendMailService->send(
            'createUserNotify',
            StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
            StringUtility::makeEmailArray(
                $this->settings['new']['email']['createUserNotify']['sender']['email']['value'],
                $this->settings['new']['email']['createUserNotify']['sender']['name']['value']
            ),
            $this->contentObject->cObjGetSingle(
                $this->config['new.']['email.']['createUserNotify.']['subject'],
                $this->config['new.']['email.']['createUserNotify.']['subject.']
            ),
            $variables,
            $this->config['new.']['email.']['createUserNotify.']
        );

        // send notify email to admin
        if ($this->settings['new']['notifyAdmin'] ||
            $this->settings['new']['email']['createAdminNotify']['receiver']['email']['value']) {
            $this->sendMailService->send(
                'createNotify',
                StringUtility::makeEmailArray(
                    $this->settings['new']['email']['createAdminNotify']['receiver']['email']['value']
                        ?: $this->settings['new']['notifyAdmin'],
                    $this->settings['new']['email']['createAdminNotify']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                $this->contentObject->cObjGetSingle(
                    $this->config['new.']['email.']['createAdminNotify.']['subject'],
                    $this->config['new.']['email.']['createAdminNotify.']['subject.']
                ),
                $variables,
                $this->config['new.']['email.']['createAdminNotify.']
            );
        }

        $this->eventDispatcher->dispatch(new FinalCreateEvent($user, $action));
        $this->finisherRunner->callFinishers($user, $this->actionMethodName, $this->settings, $this->contentObject);

        if ($backend === false) {
            $this->redirectByAction($action, ($status ? $status . 'Redirect' : 'redirect'));
            $this->addFlashMessage(LocalizationUtility::translate('create'));
            $this->redirect($redirectByActionName);
        }
    }

    /**
     * Log user in
     *
     * @param User $user
     * @param $login
     * @throws IllegalObjectTypeException
     */
    protected function loginPreflight(User $user, $login)
    {
        if ($login) {
            // persist user (otherwise login may not be possible)
            $this->userRepository->update($user);
            $this->persistenceManager->persistAll();
            if ($this->config['new.']['login'] === '1') {
                UserUtility::login($user, $this->allConfig['persistence']['storagePid']);
                $this->addFlashMessage(LocalizationUtility::translate('login'), '', FlashMessage::NOTICE);
            }
        }
    }

    /**
     * Redirect by TypoScript setting
     *        [userConfirmation|userConfirmationRefused|adminConfirmation|
     *        adminConfirmationRefused|adminConfirmationRefusedSilent]Redirect
     *
     * @param string $action "new", "edit"
     * @param string $category "redirect", "requestRedirect" value from TypoScript
     */
    protected function redirectByAction($action = 'new', $category = 'redirect')
    {
        $target = null;
        // redirect from TypoScript cObject
        if ($this->contentObject->cObjGetSingle(
            $this->config[$action . '.'][$category],
            $this->config[$action . '.'][$category . '.']
        )
        ) {
            $target = $this->contentObject->cObjGetSingle(
                $this->config[$action . '.'][$category],
                array_merge_recursive(
                    $this->config[$action . '.'][$category . '.'],
                    [
                        'linkAccessRestrictedPages' => 1
                    ]
                )
            );
        }

        // if redirect target
        if ($target) {
            $this->redirectToUri(StringUtility::removeDoubleSlashesFromUri($target));
        }
    }

    /**
     * Init for User delete action
     */
    protected function initializeDeleteAction()
    {
        $user = UserUtility::getCurrentUser();
        $token = $this->request->getArgument('token');
        $uid = $this->request->getArgument('user');
        $this->testSpoof($user, $uid, $token);
    }

    /**
     * Check if user is authenticated and params are valid
     *
     * @param User $user
     * @param int $uid Given fe_users uid
     * @param string $receivedToken Token
     */
    protected function testSpoof($user, $uid, $receivedToken)
    {
        $errorOnProfileUpdate = false;
        $knownToken = GeneralUtility::hmac($user->getUid(), (string)$user->getCrdate()->getTimestamp());

        //check if the params are valid
        if (!is_string($receivedToken) || !hash_equals($knownToken, $receivedToken)) {
            $errorOnProfileUpdate = true;
        }

        //check if the logged user is allowed to edit / delete this record
        if ($user->getUid() !== (int)$uid && $uid > 0) {
            $errorOnProfileUpdate = true;
        }

        if ($errorOnProfileUpdate === true) {
            $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY, $user);
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
     */
    public function assignForAll()
    {
        $jsLabels = [
            'loading_states' => LocalizationUtility::translate('js.loading_states'),
            'please_choose' => LocalizationUtility::translate('pleaseChoose'),
        ];
        $this->view->assignMultiple(
            [
                'languageUid' => FrontendUtility::getFrontendLanguageUid(),
                'storagePid' => $this->allConfig['persistence']['storagePid'],
                'Pid' => FrontendUtility::getCurrentPid(),
                'data' => $this->contentObject->data,
                'useStaticInfoTables' => ExtensionManagementUtility::isLoaded('static_info_tables'),
                'jsLabels' => json_encode($jsLabels),
            ]
        );
    }

    /**
     */
    public function initializeAction()
    {
        $this->controllerContext = $this->buildControllerContext();
        $this->user = UserUtility::getCurrentUser();
        $this->contentObject = $this->configurationManager->getContentObject();
        $this->pluginVariables = $this->request->getArguments();
        $this->moduleConfig = [];
        $this->allConfig = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $this->config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $this->config = $this->config[BackendUtility::getPluginOrModuleString() . '.']['tx_femanager.']['settings.'];
        if (TYPO3_MODE == 'BE') {
            $config = BackendUtility::loadTS($this->allConfig['settings']['configPID']);
            if (is_array($config['plugin.']['tx_femanager.']['settings.'])) {
                $this->config = $config['plugin.']['tx_femanager.']['settings.'];
                $this->settings = $this->config;
            }

            $this->moduleConfig = $config['module.']['tx_femanager.'];

            // Retrieve page TSconfig of the current page
            $pageTsConfig = BackendUtilityCore::getPagesTSconfig(BackendUtility::getPageIdentifier());
            if (is_array($pageTsConfig['module.']['tx_femanager.'])) {
                $this->moduleConfig = array_merge($this->moduleConfig, $pageTsConfig['module.']['tx_femanager.']);
            }

            // Retrieve user TSconfig of currently logged in user
            $userTsConfig = $GLOBALS['BE_USER']->getTSConfig();
            if (is_array($userTsConfig['tx_femanager.']) && is_array($this->moduleConfig)) {
                $this->moduleConfig = array_merge_recursive($this->moduleConfig, $userTsConfig['tx_femanager.']);
            }
        }

        $this->setAllUserGroups();
        $this->checkTypoScript();
        $this->checkStoragePid();

        $dataProcessorRunner = $this->objectManager->get(DataProcessorRunner::class);
        $this->pluginVariables = $dataProcessorRunner->callClasses(
            $this->request->getArguments(),
            $this->settings,
            $this->contentObject,
            $this->arguments
        );
        $this->request->setArguments($this->pluginVariables);
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

    /**
     */
    protected function checkStoragePid()
    {
        if ((int)$this->allConfig['persistence']['storagePid'] === 0
            && GeneralUtility::_GP('type') !== '1548935210'
            && TYPO3_MODE !== 'BE'
        ) {
            $this->addFlashMessage(LocalizationUtility::translate('error_no_storagepid'), '', FlashMessage::ERROR);
        }
    }

    /**
     */
    protected function checkTypoScript()
    {
        if (TYPO3_MODE == 'BE') {
            if ($this->config['_TypoScriptIncluded'] !== '1') {
                $this->addFlashMessage(
                    (string)LocalizationUtility::translate('error_no_typoscript_be'),
                    '',
                    FlashMessage::ERROR
                );
            }
        } else {
            if ($this->settings['_TypoScriptIncluded'] !== '1' && !GeneralUtility::_GP('eID') && TYPO3_MODE !== 'BE') {
                $this->addFlashMessage(
                    (string)LocalizationUtility::translate('error_no_typoscript'),
                    '',
                    FlashMessage::ERROR
                );
            }
        }
    }

    /**
     */
    protected function setAllUserGroups()
    {
        $controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
        $removeFromUserGroupSelection = $this->settings[$controllerName]['misc']['removeFromUserGroupSelection'];
        $this->allUserGroups = $this->userGroupRepository->findAllForFrontendSelection($removeFromUserGroupSelection);
    }

    /**
     * Send email to user for confirmation
     *
     * @param User $user
     * @throws UnsupportedRequestTypeException
     */
    public function sendCreateUserConfirmationMail(User $user)
    {
        $this->sendMailService->send(
            'createUserConfirmation',
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            [
                $this->config['new.']['email.']['createUserConfirmation.']['sender.']['email.']['value'] =>
                    $this->config['new.']['email.']['createUserConfirmation.']['sender.']['name.']['value']
            ],
            $this->contentObject->cObjGetSingle(
                $this->config['new.']['email.']['createUserConfirmation.']['subject'],
                $this->config['new.']['email.']['createUserConfirmation.']['subject.']
            ),
            [
                'user' => $user,
                'hash' => HashUtility::createHashForUser($user)
            ],
            $this->config['new.']['email.']['createUserConfirmation.']
        );
    }
}
