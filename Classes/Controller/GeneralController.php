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
 * User Controller
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeneralController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
	 * Misc Functions
	 *
	 * @var object
	 */
	public $div;

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
	 * Prefix method to createAction(): Create Confirmation from Admin is not necessary
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

			$this->flashMessageContainer->add(
				LocalizationUtility::translate('createRequestWaitingForUserConfirm', 'femanager')
			);

			// send email to user for confirmation
			$this->div->sendEmail(
				'createUserConfirmation',
				array($user->getEmail() => $user->getUsername()),
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

			$this->redirect('new');
		}
		if (!empty($this->settings['new']['confirmByAdmin'])) {

			$this->flashMessageContainer->add(
				LocalizationUtility::translate('createRequestWaitingForAdminConfirm', 'femanager')
			);

			// send email to admin
			$this->div->sendEmail(
				'createAdminConfirmation',
				Div::makeEmailArray(
					$this->settings['new']['confirmByAdmin'],
					$this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
				),
				array($user->getEmail() => $user->getUsername()),
				'New Registration request', // will be overwritten with TypoScript
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
	 * Prefix method to updateAction(): Update Confirmation from Admin is not necessary
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @return void
	 */
	public function updateAllConfirmed(User $user) {

		// send notify email to admin
		if ($this->settings['edit']['notifyAdmin']) {
			$existingUser = $this->userRepository->findByUid($user->getUid()); // read stored, existing values
			$dirtyProperties = Div::getDirtyPropertiesFromObject($existingUser, $user); // get changes
			$this->div->sendEmail(
				'updateNotify',
				Div::makeEmailArray(
					$this->settings['edit']['notifyAdmin'],
					$this->settings['edit']['email']['notifyAdmin']['receiver']['name']['value']
				),
				array($user->getEmail() => $user->getUsername()), // will be overwritten by TypoScript (if set)
				'Profile update', // will be overwritten by TypoScript (if set)
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
	 * @param \array $user
	 * @return void
	 */
	public function updateRequest($user) {
		$existingUser = $this->userRepository->findByUid($user->getUid()); // read stored, existing values
		$dirtyProperties = Div::getDirtyPropertiesFromObject($existingUser, $user); // get changes
		$user->setIgnoreDirty(TRUE); // don't auto persist properties
		$user->setUserGroup($existingUser->getUserGroup()); // workarround to disable auto persistance of usergroup
		$existingUser->setTxFemanagerChangerequest( // store change request values as xml to user
			GeneralUtility::array2xml($dirtyProperties, '', 0, 'changes')
		);

		// send email to admin
		$this->div->sendEmail(
			'updateRequest',
			array($this->settings['edit']['confirmByAdmin'] => $this->settings['edit']['email']['updateRequest']['sender']['name']['value']),
			array($user->getEmail() => $user->getUsername()),
			'New Profile change request', // will be overwritten with TypoScript
			array(
				 'user' => $existingUser,
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
	 * @param string $redirectByActionName		Action to redirect
	 * @param bool $login						Login after creation
	 * @return void
	 */
	public function finalCreate($user, $action, $redirectByActionName, $login = TRUE) {
		// login user
		if ($login) {
			$this->loginAfterCreate($user);
		}

		// send notify email to admin
		if ($this->settings['new']['notifyAdmin']) {
			$this->div->sendEmail(
				'createNotify',
				Div::makeEmailArray(
					$this->settings['new']['notifyAdmin'], // flexform value
					$this->settings['new']['email']['createAdminNotify']['receiver']['name']['value'] // value from TypoScript
				),
				array($user->getEmail() => $user->getUsername()), // will be overwritten by TypoScript (if set)
				'Profile creation', // will be overwritten by TypoScript (if set)
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
die('xyzzyx');
		// frontend redirect (if activated via TypoScript)
		$this->redirectByAction($action);

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
	 * @return void
	 */
	protected function redirectByAction($action = 'new') {
		$target = null;
		// redirect from TypoScript cObject
		if ($this->cObj->cObjGetSingle($this->config[$action . '.']['redirect'], $this->config[$action . '.']['redirect.'])) {
			$target = $this->cObj->cObjGetSingle($this->config[$action . '.']['redirect'], $this->config[$action . '.']['redirect.']);
		}

		// if redirect target
		if ($target) {
			$this->uriBuilder->setTargetPageUid($target);
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
	 * Init for User creation
	 *
	 * @return void
	 */
	public function initializeUpdateAction() {
		// workarround for empty usergroups
		if (intval($this->pluginVariables['user']['usergroup'][0]['__identity']) === 0) {
			unset($this->pluginVariables['user']['usergroup']);
		}
		$this->request->setArguments($this->pluginVariables);
	}

	/**
	 * Assigns all values, which should be available in all views
	 */
	public function assignForAll() {
		$this->view->assign('languageUid', ($GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0));
		$this->view->assign('storagePid', $this->allConfig['persistence']['storagePid']);
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->div = $this->objectManager->get('In2\Femanager\Utility\Div');
		$this->user = $this->div->getCurrentUser();
		$this->cObj = $this->configurationManager->getContentObject();
		$this->pluginVariables = $this->request->getArguments();
		$this->allConfig = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->config = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$this->config = $this->config['plugin.']['tx_femanager.']['settings.'];
		$this->allUserGroups = $this->userGroupRepository->findAll();

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
		return false;
	}

}
?>