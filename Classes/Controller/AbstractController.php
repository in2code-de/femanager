<?php
namespace In2\Femanager\Controller;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \In2\Femanager\Domain\Model\User;
use \In2\Femanager\Utility\Div;

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
 * 			GNU General Public License, version 3 or later
 */
class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * userRepository
	 *
	 * @var \In2\Femanager\Domain\Repository\UserRepository
	 * @inject
	 */
	protected $userRepository;

	/**
	 * userGroupRepository
	 *
	 * @var \In2\Femanager\Domain\Repository\UserGroupRepository
	 * @inject
	 */
	protected $userGroupRepository;

	/**
	 * persistenceManager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * SignalSlot Dispatcher
	 *
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * Misc Functions
	 *
	 * @var \In2\Femanager\Utility\Div
	 * @inject
	 */
	protected $div;

	/**
	 * @var \In2\Femanager\Utility\SendMail
	 * @inject
	 */
	protected $sendMail;

	/**
	 * Content Object
	 *
	 * @var object
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
	 * @var object
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
	 * @var object
	 */
	public $user;

	/**
	 * All available usergroups
	 *
	 * @var object
	 */
	public $allUserGroups;

	/**
	 * controllerContext
	 *
	 * @var \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext
	 */
	public $controllerContext;

	/**
	 * Prefix method to createAction()
	 * 		Create Confirmation from Admin is not necessary
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function createAllConfirmed(User $user) {
		$this->userRepository->add($user);
		$this->persistenceManager->persistAll();

		$this->flashMessageContainer->add(
			LocalizationUtility::translate('create', 'femanager')
		);

		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.101', 'femanager'),
			101,
			$user
		);
		$this->finalCreate($user, 'new', 'createStatus');
	}

	/**
	 * Prefix method to createAction(): Create must be confirmed by Admin
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function createRequest(User $user) {
		// persist
		$user->setDisable(TRUE);
		$this->userRepository->add($user);
		$this->persistenceManager->persistAll();

		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.106', 'femanager'),
			106,
			$user
		);

		if (!empty($this->settings['new']['confirmByUser'])) {

			// send email to user for confirmation
			$this->sendMail->send(
				'createUserConfirmation',
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				array(
					$this->settings['new']['email']['createUserConfirmation']['sender']['email']['value']
						=> $this->settings['settings']['new']['email']['createUserConfirmation']['sender']['name']['value']
				),
				'Confirm your profile creation request',
				array(
					'user' => $user,
					'hash' => Div::createHash($user->getUsername())
				),
				$this->config['new.']['email.']['createUserConfirmation.']
			);

			// redirect by TypoScript
			$this->redirectByAction('new', 'requestRedirect');

			// add flashmessage
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('createRequestWaitingForUserConfirm', 'femanager')
			);

			// redirect
			$this->redirect('new');
		}
		if (!empty($this->settings['new']['confirmByAdmin'])) {

			$this->flashMessageContainer->add(
				LocalizationUtility::translate('createRequestWaitingForAdminConfirm', 'femanager')
			);

			// send email to admin
			$this->sendMail->send(
				'createAdminConfirmation',
				Div::makeEmailArray(
					$this->settings['new']['confirmByAdmin'],
					$this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
				),
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				'New Registration request',
				array(
					'user' => $user,
					'hash' => Div::createHash($user->getUsername() . $user->getUid())
				),
				$this->config['new.']['email.']['createAdminConfirmation.']
			);

			$this->redirect('new');
		}
	}

	/**
	 * Prefix method to updateAction()
	 * 		Update Confirmation from Admin is not necessary
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function updateAllConfirmed(User $user) {

		// send notify email to admin
		if ($this->settings['edit']['notifyAdmin']) {
			$existingUser = $this->userRepository->findByUid($user->getUid());
			$dirtyProperties = Div::getDirtyPropertiesFromObject($existingUser, $user);
			$this->sendMail->send(
				'updateNotify',
				Div::makeEmailArray(
					$this->settings['edit']['notifyAdmin'],
					$this->settings['edit']['email']['notifyAdmin']['receiver']['name']['value']
				),
				Div::makeEmailArray(
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

		// persist
		$this->userRepository->update($user);
		$this->persistenceManager->persistAll();
		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', array($user, $this));

		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.201', 'femanager'),
			201,
			$user
		);

		$this->redirectByAction('edit');

		$this->flashMessageContainer->add(
			LocalizationUtility::translate('update', 'femanager')
		);
	}

	/**
	 * Prefix method to updateAction(): Update must be confirmed by Admin
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function updateRequest($user) {
		$dirtyProperties = Div::getDirtyPropertiesFromObject($user);
		$user = Div::rollbackUserWithChangeRequest($user, $dirtyProperties);

		// send email to admin
		$this->sendMail->send(
			'updateRequest',
			array(
				$this->settings['edit']['confirmByAdmin'] =>
					$this->settings['edit']['email']['updateRequest']['sender']['name']['value']
			),
			Div::makeEmailArray(
				$user->getEmail(),
				$user->getUsername()
			),
			'New Profile change request',
			array(
				'user' => $user,
				'changes' => $dirtyProperties,
				'hash' => Div::createHash($user->getUsername() . $user->getUid())
			),
			$this->config['edit.']['email.']['updateRequest.']
		);

		// write log
		$this->div->log(
			LocalizationUtility::translate('tx_femanager_domain_model_log.state.204', 'femanager'),
			203,
			$user
		);

		// redirect if turned on in TypoScript
		$this->redirectByAction('edit', 'requestRedirect');

		// add flashmessage
		$this->flashMessageContainer->add(
			LocalizationUtility::translate('updateRequest', 'femanager')
		);
	}

	/**
	 * Some additional actions after profile creation
	 *
	 * @param $user
	 * @param $action
	 * @param string $redirectByActionName Action to redirect
	 * @param bool $login Login after creation
	 * @param string $status
	 * @return void
	 */
	public function finalCreate($user, $action, $redirectByActionName, $login = TRUE, $status = '') {
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
			Div::makeEmailArray(
				$user->getEmail(),
				$user->getFirstName() . ' ' . $user->getLastName()
			),
			array(
				$this->settings['new']['email']['createUserNotify']['sender']['email']['value']
					=> $this->settings['settings']['new']['email']['createUserNotify']['sender']['name']['value']
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
				Div::makeEmailArray(
					$this->settings['new']['notifyAdmin'],
					$this->settings['new']['email']['createAdminNotify']['receiver']['name']['value']
				),
				Div::makeEmailArray(
					$user->getEmail(),
					$user->getUsername()
				),
				'Profile creation',
				array(
					'user' => $user,
					'settings' => $this->settings
				),
				$this->config['new.']['email.']['createAdminNotify.']
			);
		}

		// sendpost: send values via POST to any target
		Div::sendPost($user, $this->config, $this->cObj);

		// store in database: store values in any wanted table
		Div::storeInDatabasePreflight($user, $this->config, $this->cObj, $this->objectManager);

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
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	protected function loginAfterCreate($user) {
		if ($this->config['new.']['login'] != 1) {
			return;
		}

		$GLOBALS['TSFE']->fe_user->checkPid = '';
		$info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
		$user = $GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $user->getUsername());
		$GLOBALS['TSFE']->fe_user->createUserSession($user);
		$GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->fetchUserSession();

		// add login flashmessage
		$this->flashMessageContainer->add(
			LocalizationUtility::translate('login', 'femanager'),
			'',
			\TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
		);
	}

	/**
	 * Redirect
	 *
	 * @param \string $action		"new", "edit"
	 * @param \string $category		"redirect", "requestRedirect" value from TypoScript
	 * @return void
	 */
	protected function redirectByAction($action = 'new', $category = 'redirect') {
		$target = NULL;
		// redirect from TypoScript cObject
		if ($this->cObj->cObjGetSingle($this->config[$action . '.'][$category], $this->config[$action . '.'][$category . '.'])) {
			$target = $this->cObj->cObjGetSingle($this->config[$action . '.'][$category], $this->config[$action . '.'][$category . '.']);
		}

		// if redirect target
		if ($target) {
			$this->uriBuilder->setTargetPageUid($target);
			$this->uriBuilder->setLinkAccessRestrictedPages(TRUE);
			$link = $this->uriBuilder->build();
			$this->redirectToUri($link);
		}
	}

	/**
	 * Init for User creation
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		// workarround for empty usergroups
		if (intval($this->pluginVariables['user']['usergroup'][0]) === 0) {
			unset($this->pluginVariables['user']['usergroup']);
		}
		$this->request->setArguments($this->pluginVariables);
	}

	/**
	 * Init for User delete action
	 *
	 * @return void
	 */
	protected function initializeDeleteAction() {
		$user = $this->div->getCurrentUser();
		$uid = $this->request->getArgument('user');
		$this->testSpoof($user, $uid);
	}

	/**
	 * Check if user is authenticated
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @param int $uid Given fe_users uid
	 * @return void
	 */
	protected function testSpoof($user, $uid) {
		if ($user->getUid() != $uid && $uid > 0) {

			// write log
			$this->div->log(
				LocalizationUtility::translate('tx_femanager_domain_model_log.state.205', 'femanager'),
				205,
				$user
			);

			// add flashmessage
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('tx_femanager_domain_model_log.state.205', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);

			$this->forward('edit');
		}
	}

	/**
	 * Assigns all values, which should be available in all views
	 *
	 * @return void
	 */
	public function assignForAll() {
		$this->view->assign(
			'languageUid',
			($GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ?
				$GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0)
		);
		$this->view->assign('storagePid', $this->allConfig['persistence']['storagePid']);
		$this->view->assign('Pid', $GLOBALS['TSFE']->id);
		$this->view->assign('actionName', $this->actionMethodName);
		$this->view->assign('uploadFolder', Div::getUploadFolderFromTca());
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->controllerContext = $this->buildControllerContext();
		$this->user = $this->div->getCurrentUser();
		$this->cObj = $this->configurationManager->getContentObject();
		$this->pluginVariables = $this->request->getArguments();
		$this->allConfig = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$this->config = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
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
					\TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
					LocalizationUtility::translate('tx_femanager_domain_model_user.dateFormat', 'femanager')
				);
		}
		// check if ts is included
		if ($this->settings['_TypoScriptIncluded'] != 1 && !GeneralUtility::_GP('eID') && TYPO3_MODE !== 'BE') {
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('error_no_typoscript', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
		}

		// check if storage pid was set
		if (intval($this->allConfig['persistence']['storagePid']) === 0 && !GeneralUtility::_GP('eID') && TYPO3_MODE !== 'BE') {
			$this->flashMessageContainer->add(
				LocalizationUtility::translate('error_no_storagepid', 'femanager'),
				'',
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
		}
	}

	/**
	 * Deactivate errormessages in flashmessages
	 *
	 * @return bool
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}

}