<?php

declare(strict_types=1);

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
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\UploadedFile;
use \TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractController
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractController extends ActionController
{
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
     *
     * @param UserRepository $userRepository
     * @param UserGroupRepository $userGroupRepository
     * @param PersistenceManager $persistenceManager
     * @param SendMailService $sendMailService
     * @param FinisherRunner $finisherRunner
     * @param LogUtility $logUtility
     */
    public function __construct(
        protected UserRepository $userRepository,
        protected UserGroupRepository $userGroupRepository,
        protected PersistenceManager $persistenceManager,
        protected SendMailService $sendMailService,
        protected FinisherRunner $finisherRunner,
        protected LogUtility $logUtility,
    ) {
    }

    /**
     * Prefix method to createAction()
     *        Create Confirmation from Admin is not necessary
     */
    public function createAllConfirmed(User $user): ResponseInterface|null
    {
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->processUploadedFiles($user);

        $this->logUtility->log(Log::STATUS_NEWREGISTRATION, $user);
        return $this->finalCreate($user, 'new', 'createStatus');
    }

    protected function processUploadedFiles($user)
    {
        $uploadedFiles = $this->request->getUploadedFiles(); // the image is now in $uploadedFiles['image'], not in $uploadedFiles['user']['image']
        $allowedFileExtensions = preg_split(
            '/\s*,\s*/',
            trim((string)ConfigurationUtility::getConfiguration('misc.uploadFileExtension'))
        );
        $allowedMimeTypes = preg_split(
            '/\s*,\s*/',
            trim((string)ConfigurationUtility::getConfiguration('misc.uploadMimeTypes'))
        );

        // we expect to see at least 'image'
        $uploadFields = ConfigurationUtility::getConfiguration('misc.uploadFields');
        foreach ($uploadFields as $field) {
            if (
                $uploadedFiles !== []
                && !empty($uploadedFiles[$field])
            ) {
                $images = [];
                // add image to an array (this way the original code in the foreach loop below
                // does not have to be changed at all)
                $images[] = $uploadedFiles[$field];

                foreach ($images as $uploadedFile) {
                    /**  @var $uploadedFile UploadedFile */
                    if (in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes)
                        && in_array(
                            pathinfo(
                                (string)$uploadedFile->getClientFilename()
                            )['extension'],
                            $allowedFileExtensions
                        )
                    ) {
                        /** @var StorageRepository $storageRepository */
                        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
                        $uploadString = ConfigurationUtility::getConfiguration('misc.uploadFolder');
                        $storage = $storageRepository->findByCombinedIdentifier($uploadString);
                        $parts = GeneralUtility::trimExplode(':', $uploadString);
                        if ($storage && !$storage?->hasFolder($parts[1])) {
                            $storage->createFolder($parts[1]);
                        }

                        $resourceStorage = $storageRepository->findByCombinedIdentifier($uploadString);
                        $uploadFolder = $resourceStorage?->getFolder($parts[1]);

                        $newFile = $storage->addUploadedFile(
                            $uploadedFile,
                            $uploadFolder,
                            null,
                            DuplicationBehavior::RENAME
                        );

                        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                            ->getConnectionForTable('sys_file_reference');
                        $connection->insert(
                            'sys_file_reference',
                            [
                                'uid_local' => $newFile->getUid(),
                                'uid_foreign' => $user->getUid(),
                                'tablenames' => 'fe_users',
                                'fieldname' => $field,
                                'sorting_foreign' => 1,
                                'crdate' => time(),
                                'tstamp' => time(),
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Prefix method to updateAction()
     *        Update Confirmation from Admin is not necessary
     */
    public function updateAllConfirmed(User $user)
    {
        // send notify email to admin
        $existingUser = clone $this->userRepository->findByUid($user->getUid());

        $this->processUploadedFiles($user);

        if (ConfigurationUtility::notifyAdminAboutEdits($this->settings)) {
            $this->sendMailService->send(
                'updateNotify',
                StringUtility::makeEmailArray(
                    ConfigurationUtility::getValue(
                        'edit./email./notifyAdmin./receiver./email./value',
                        $this->config
                    ) ?: ConfigurationUtility::getValue('edit./notifyAdmin', $this->config),
                    $this->settings['edit']['email']['notifyAdmin']['receiver']['name']['value'] ?? null
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile update',
                [
                    'user' => $user,
                    'changes' => UserUtility::getDirtyPropertiesFromUser($user),
                    'settings' => $this->settings,
                ],
                $this->config['edit.']['email.']['notifyAdmin.'] ?? [],
                $this->request
            );
        }

        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new FinalUpdateEvent($user));
        $this->logUtility->log(Log::STATUS_PROFILEUPDATED, $user, ['existingUser' => $existingUser]);
        $this->addFlashMessage(LocalizationUtility::translate('update'));
        return $this->redirectByAction('edit', 'redirect', 'edit');
    }

    /**
     * Prefix method to updateAction(): Update must be confirmed by Admin
     *
     * @param User $user
     */
    public function updateRequest($user): ResponseInterface|null
    {
        if ($this->settings['edit']['confirmByAdmin'] ?? null) {
            $dirtyProperties = UserUtility::getDirtyPropertiesFromUser($user);
            $user = UserUtility::rollbackUserWithChangeRequest($user, $dirtyProperties);
            $this->sendMailService->send(
                'updateRequest',
                StringUtility::makeEmailArray(
                    $this->settings['edit']['confirmByAdmin'] ?? '',
                    $this->settings['edit']['email']['updateRequest']['sender']['name']['value'] ?? null
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'New Profile change request',
                [
                    'user' => $user,
                    'changes' => $dirtyProperties,
                    'hash' => HashUtility::createHashForUser($user),
                ],
                $this->config['edit.']['email.']['updateRequest.'] ?? [],
                $this->request
            );
            $this->logUtility->log(
                Log::STATUS_PROFILEUPDATEREFUSEDADMIN,
                $user,
                ['dirtyProperties' => $dirtyProperties]
            );
            $this->addFlashMessage(LocalizationUtility::translate('updateRequest'));
            return $this->redirectByAction('edit', 'requestRedirect', 'edit');
        }

        $this->logUtility->log(
            Log::STATUS_PROFILEUPDATEREFUSEDADMIN,
            $user,
            ['message' => 'settings[edit][confirmByAdmin] is missing!']
        );
        return null;
    }

    /**
     * Some additional actions after profile creation
     *
     * @param string $redirectByActionName Action to redirect
     * @param bool $login Login after creation
     * @param bool $backend Don't redirect if called from backend action
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @TODO: Remove Suppress when login is reactivated
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function finalCreate(
        \In2code\Femanager\Domain\Model\User $user,
        string $action,
        string $redirectByActionName,
        bool $login = true,
        string $status = '',
        bool $backend = false
    ): ResponseInterface|null {
        $this->loginPreflight($user, $login);
        $variables = ['user' => $user, 'settings' => $this->settings, 'hash' => HashUtility::createHashForUser($user)];
        if (ConfigurationUtility::getValue(
                'new./email./createUserNotify./sender./email./value',
                $this->config
            ) && ConfigurationUtility::getValue('new./email./createUserNotify./sender./name./value', $this->config)) {
            $this->sendMailService->send(
                'createUserNotify',
                StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
                StringUtility::makeEmailArray(
                    ConfigurationUtility::getValue(
                        'new./email./createUserNotify./sender./email./value',
                        $this->config
                    ),
                    ConfigurationUtility::getValue('new./email./createUserNotify./sender./name./value', $this->config)
                ),
                $this->contentObject->cObjGetSingle(
                    (string)ConfigurationUtility::getValue('new./email./createUserNotify./subject', $this->config),
                    (array)ConfigurationUtility::getValue('new./email./createUserNotify./subject.', $this->config),
                ),
                $variables,
                ConfigurationUtility::getValue('new./email./createUserNotify.', $this->config),
                $this->request
            );
        }

        $createAdminNotify = ConfigurationUtility::getValue(
            'new./email./createAdminNotify./receiver./email./value',
            $this->config
        );
        if (!$createAdminNotify) {
            $createAdminNotify = $this->settings['new']['notifyAdmin'] ?? null;
        }

        // send notify email to admin
        if ($createAdminNotify) {
            $this->sendMailService->send(
                'createNotify',
                StringUtility::makeEmailArray(
                    $createAdminNotify,
                    ConfigurationUtility::getValue('new./email./createAdminNotify./receiver./name./value', $this->config)
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                $this->contentObject->cObjGetSingle(
                    (string)ConfigurationUtility::getValue('new./email./createAdminNotify./subject', $this->config),
                    (array)ConfigurationUtility::getValue('new./email./createAdminNotify./subject.', $this->config)
                ),
                $variables,
                ConfigurationUtility::getValue('new./email./createAdminNotify.', $this->config),
                $this->request
            );
        }

        $this->eventDispatcher->dispatch(new FinalCreateEvent($user, $action));
        $this->finisherRunner->callFinishers($user, $this->actionMethodName, $this->settings, $this->contentObject);

        if ($backend === false) {
            $redirectTarget = $this->redirectByAction(
                $action,
                ($status !== '' && $status !== '0' ? $status . 'Redirect' : 'redirect'),
                $redirectByActionName
            );
            if ($redirectTarget instanceof RedirectResponse) {
                $this->addFlashMessage(LocalizationUtility::translate('create'));
            }

            return $redirectTarget;
        }

        return null;
    }

    /**
     * Log user in
     *
     * @param $login
     * @throws IllegalObjectTypeException
     */
    protected function loginPreflight(User $user, $login)
    {
        if ($login) {
            // persist user (otherwise login may not be possible)
            $this->userRepository->update($user);
            $this->persistenceManager->persistAll();
            if (ConfigurationUtility::getValue('new./login', $this->config) === '1') {
                UserUtility::login($user);
                $this->addFlashMessage(
                    LocalizationUtility::translate('login'),
                    '',
                    ContextualFeedbackSeverity::NOTICE
                );
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
    protected function redirectByAction(
        string $action = 'new',
        string $category = 'redirect',
        ?string $defaultAction = 'new'
    ): ResponseInterface {
        $target = null;
        // redirect from TypoScript cObject
        if ($this->contentObject->cObjGetSingle(
            (string)ConfigurationUtility::getValue($action . './' . $category, $this->config),
            (array)ConfigurationUtility::getValue($action . './' . $category . '.', $this->config),
        )
        ) {
            $target = $this->contentObject->cObjGetSingle(
                (string)ConfigurationUtility::getValue($action . './' . $category, $this->config),
                array_merge_recursive(
                    (array)ConfigurationUtility::getValue($action . './' . $category . '.', $this->config),
                    [
                        'linkAccessRestrictedPages' => 1,
                    ]
                )
            );
        }

        // if redirect target
        if ($target) {
            return $this->redirectToUri(StringUtility::removeDoubleSlashesFromUri($target));
        }

        return $this->redirect($defaultAction);
    }

    /**
     * Check if user is authenticated and params are valid
     *
     * @param int $uid Given fe_users uid
     */
    protected function isSpoof(User $user, int $uid, string $receivedToken): bool
    {
        $errorOnProfileUpdate = false;
        $knownToken = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\HashService::class)->hmac((string)$user->getUid(), (string)($user->getCrdate() ?: new \DateTime('01.01.1970'))->getTimestamp());

        //check if the params are valid
        if (!hash_equals($knownToken, $receivedToken)) {
            $errorOnProfileUpdate = true;
        }

        //check if the logged user is allowed to edit / delete this record
        if ($user->getUid() !== $uid && $uid > 0) {
            return true;
        }

        return $errorOnProfileUpdate;
    }

    /**
     * Assigns all values, which should be available in all views
     */
    public function assignForAll(): void
    {
        $jsLabels = [
            'loading_states' => LocalizationUtility::translate('js.loading_states'),
            'please_choose' => LocalizationUtility::translate('pleaseChoose'),
        ];
        $this->view->assignMultiple(
            [
                'languageUid' => FrontendUtility::getFrontendLanguageUid(),
                'storagePid' => $this->allConfig['persistence']['storagePid'] ?? 0,
                'Pid' => FrontendUtility::getCurrentPid(),
                'data' => $this->contentObject->data,
                'useStaticInfoTables' => ExtensionManagementUtility::isLoaded('static_info_tables'),
                'jsLabels' => json_encode($jsLabels, JSON_THROW_ON_ERROR),
            ]
        );
    }

    /**
     * @throws \Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function initializeAction(): void
    {
        $this->user = UserUtility::getCurrentUser();
        $this->contentObject = $this->request->getAttribute('currentContentObject');
        $this->pluginVariables = $this->request->getArguments();
        $this->moduleConfig = [];
        $this->allConfig = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $this->config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $this->config =
            $this->config[BackendUtility::getPluginOrModuleString() . '.']['tx_femanager.']['settings.'] ?? [];

        if (ApplicationType::fromRequest($this->request)->isBackend()) {
            $pid = $this->allConfig['persistence']['storagePid'] ?? 0;
            $config = BackendUtility::loadTS((int)$pid);
            $this->config = $config['plugin.']['tx_femanager.']['settings.'] ?? [];
            $this->settings = $this->config;

            $this->moduleConfig = $config['module.']['tx_femanager.'] ?? [];

            // Retrieve page TSconfig of the current page
            $pageTsConfig = BackendUtilityCore::getPagesTSconfig(BackendUtility::getPageIdentifier());
            if (is_array($pageTsConfig['module.']['tx_femanager.'] ?? [])) {
                $this->moduleConfig = array_merge($this->moduleConfig, $pageTsConfig['module.']['tx_femanager.'] ?? []);
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

        if (ApplicationType::fromRequest($this->request)->isFrontend()) {
            $dataProcessorRunner = GeneralUtility::makeInstance(DataProcessorRunner::class);
            $dataProcessorRunner->callClasses(
                $this->settings,
                $this->contentObject,
                $this->arguments,
                $this->request
            );
        }
    }

    /**
     * Deactivate errormessages in flashmessages
     *
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    protected function checkStoragePid()
    {
        if ((int)($this->allConfig['persistence']['storagePid'] ?? 0) === 0
            && ($this->request->getParsedBody()['type'] ?? $this->request->getQueryParams()['type'] ?? null) !== '1548935210'
            && !ApplicationType::fromRequest($this->request)->isBackend()
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate('error_no_storagepid'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    protected function checkTypoScript()
    {
        if (ApplicationType::fromRequest($this->request ?? null)->isBackend()) {
            if (($this->config['_TypoScriptIncluded'] ?? '1') !== '1') {
                $this->addFlashMessage(
                    (string)LocalizationUtility::translate('error_no_typoscript_be'),
                    '',
                    ContextualFeedbackSeverity::ERROR
                );
            }
        } else {
            $typoscriptIncluded = ConfigurationUtility::getValue('_TypoScriptIncluded', $this->settings);
            if (
                $typoscriptIncluded !== '1' && !($this->request->getParsedBody()['eID'] ?? $this->request->getQueryParams()['eID'] ?? null)
                && !ApplicationType::fromRequest($this->request)->isBackend()
            ) {
                $this->addFlashMessage(
                    (string)LocalizationUtility::translate('error_no_typoscript'),
                    '',
                    ContextualFeedbackSeverity::ERROR
                );
            }
        }
    }

    protected function setAllUserGroups()
    {
        $controllerName = strtolower($this->request->getControllerName());
        $removeFromSelection = $this->settings[$controllerName]['misc']['removeFromUserGroupSelection'] ?? '';
        $this->allUserGroups = $this->userGroupRepository->findAllForFrontendSelection($removeFromSelection);
    }

    /**
     * Send email to user for confirmation
     */
    public function sendCreateUserConfirmationMail(User $user): void
    {
        $this->sendMailService->send(
            'createUserConfirmation',
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            [
                ConfigurationUtility::getValue(
                    'new./email./createUserConfirmation./sender./email./value',
                    $this->config
                ) => ConfigurationUtility::getValue(
                    'new./email./createUserConfirmation./sender./name./value',
                    $this->config
                ),
            ],
            $this->contentObject->cObjGetSingle(
                (string)ConfigurationUtility::getValue('new./email./createUserConfirmation./subject', $this->config),
                (array)ConfigurationUtility::getValue('new./email./createUserConfirmation./subject.', $this->config)
            ),
            [
                'user' => $user,
                'hash' => HashUtility::createHashForUser($user),
            ],
            ConfigurationUtility::getValue('new./email./createUserConfirmation.', $this->config),
            $this->request
        );
    }

    public function sendCreateUserConfirmationMailFromBackend(User $user): void
    {
        $receiver = StringUtility::makeEmailArray($user->getEmail(), $user->getUsername());
        $sender = StringUtility::makeEmailArray(
            ConfigurationUtility::getValue('new./email./createUserConfirmation./sender./email./value', $this->config),
            ConfigurationUtility::getValue('new./email./createUserConfirmation./sender./name./value', $this->config)
        );
        $subjectInConfig = ConfigurationUtility::getValue('new./email./createUserConfirmation./subject', $this->config);
        $subject = ($subjectInConfig == 'TEXT') ? 'Please confirm your registration' : $subjectInConfig;
        // simple mails without cObj information are sent from the backend
        $this->sendMailService->sendSimple(
            'createUserConfirmation',
            $receiver,
            $sender,
            $subject,
            [
                'user' => $user,
                'hash' => HashUtility::createHashForUser($user),
            ],
            ConfigurationUtility::getValue('new./email./createUserConfirmation.', $this->config),
            $this->request
        );
    }

    protected function validateMissingCaptcha(string $redirectAction): void
    {
        if ($this->isCaptchaEnabled() && $this->request->getAttribute('extbase')->getArgument('captcha') === '') {
            $this->addFlashMessage(
                LocalizationUtility::translate('validationErrorCaptcha'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            throw new PropagateResponseException($this->redirect($redirectAction), 12398019239);
        }
    }

    protected function isCaptchaEnabled(): bool {
        $extbaseAttribute = $this->request->getAttribute('extbase');
        $controllerName = strtolower($extbaseAttribute->getControllerName());

        return $extbaseAttribute->hasArgument('captcha') &&
            $this->config[$controllerName . '.']['validation.']['captcha.']['captcha'] == true &&
            ExtensionManagementUtility::isLoaded('sr_freecap');
    }
}
