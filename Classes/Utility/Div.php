<?php
namespace In2\Femanager\Utility;

use \TYPO3\CMS\Core\Utility\GeneralUtility,
	\TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
 * Misc Functions
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 * 			GNU General Public License, version 3 or later
 */
class Div {

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
	 * logRepository
	 *
	 * @var \In2\Femanager\Domain\Repository\LogRepository
	 * @inject
	 */
	protected $logRepository;

	/**
	 * configurationManager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * objectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Content Object
	 *
	 * @var object
	 */
	public $cObj;

	/**
	 * Return current logged in fe_user
	 *
	 * @return object
	 */
	public function getCurrentUser() {
		if (!is_array($GLOBALS['TSFE']->fe_user->user)) {
			return NULL;
		}
		return $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
	}

	/**
	 * Get Usergroups from current logged in user
	 *
	 * @return \array
	 */
	public function getCurrentUsergroupUids() {
		$currentLoggedInUser = $this->getCurrentUser();
		$usergroupUids = array();
		if ($currentLoggedInUser !== NULL) {
			foreach ($currentLoggedInUser->getUsergroup() as $usergroup) {
				$usergroupUids[] = $usergroup->getUid();
			}
		}
		return $usergroupUids;
	}

	/**
	 * Set object properties from forceValues in TypoScript
	 *
	 * @param $object
	 * @param $settings
	 * @param $cObj
	 * @return $object
	 */
	public function forceValues($object, $settings, $cObj) {
		foreach ((array) $settings as $field => $config) {
			$config = NULL;
			if (stristr($field, '.')) {
				continue;
			}

			// value to set
			$value = $cObj->cObjGetSingle($settings[$field], $settings[$field . '.']);

			if ($field == 'usergroup') {
				// need objectstorage for usergroup field
				$object->removeAllUsergroups();
				$values = GeneralUtility::trimExplode(',', $value, 1);
				foreach ($values as $usergroupUid) {
					$usergroup = $this->userGroupRepository->findByUid($usergroupUid);
					$object->addUsergroup($usergroup);
				}
			} else {
				// set value
				if (method_exists($object, 'set' . ucfirst($field))) {
					$object->{'set' . ucfirst($field)}($value);
				}
			}
		}
		return $object;
	}

	/**
	 * Autogenerate username and password if it's empty
	 *
	 * @param $user
	 */
	public function fallbackUsernameAndPassword($user) {
		if (!$user->getUsername()) {
			$user->setUsername(
				self::getRandomString()
			);
			if ($user->getEmail()) {
				$user->setUsername(
					$user->getEmail()
				);
			}
		}
		if (!$user->getPassword()) {
			$user->setPassword(
				self::getRandomString()
			);
		}
		return $user;
	}

	/**
	 * Overwrite usergroups from user by flexform settings
	 *
	 * @param \In2\Femanager\Domain\Model\User $object
	 * @param \array $settings
	 * @param \string $controllerName
	 * @return \In2\Femanager\Domain\Model\User $object
	 */
	public function overrideUserGroup($object, $settings, $controllerName = 'new') {
		if (empty($settings[$controllerName]['overrideUserGroup'])) {
			return $object;
		}

		// for each selected usergroup in the flexform
		$object->removeAllUsergroups();
		foreach (GeneralUtility::trimExplode(',', $settings[$controllerName]['overrideUserGroup'], 1) as $usergroupUid) {
			$usergroup = $this->userGroupRepository->findByUid($usergroupUid);
			$object->addUsergroup($usergroup);
		}

		return $object;
	}

	/**
	 * Upload file from $_FILES['qqfile']
	 *
	 * @return mixed	false or file.png
	 */
	public function uploadFile() {

		if (!is_array($_FILES['qqfile'])) {
			return FALSE;
		}

		// Check extension
		if (empty($_FILES['qqfile']['name']) || !self::checkExtension($_FILES['qqfile']['name'])) {
			return FALSE;
		}

		// create new filename and upload it
		$basicFileFunctions = $this->objectManager->get('TYPO3\CMS\Core\Utility\File\BasicFileUtility');
		$filename = $this->cleanFileName($_FILES['qqfile']['name']);
		$newFile = $basicFileFunctions->getUniqueName(
			$filename,
			GeneralUtility::getFileAbsFileName(
				self::getUploadFolderFromTca()
			)
		);
		if (GeneralUtility::upload_copy_move($_FILES['qqfile']['tmp_name'], $newFile)) {
			$fileInfo = pathinfo($newFile);
			return $fileInfo['basename'];
		}

		return FALSE;
	}

	/**
	 * Only allowed a-z, A-Z, 0-9, -, .
	 * Others will be replaced
	 *
	 * @param string $filename
	 * @param string $replace
	 * @return string
	 */
	public function cleanFileName($filename, $replace = '_') {
		return preg_replace('/[^a-zA-Z0-9-\.]/', $replace, trim($filename));
	}

	/**
	 * Check extension of given filename
	 *
	 * @param \string $filename Filename like (upload.png)
	 * @return \bool If Extension is allowed
	 */
	public static function checkExtension($filename) {
		$extensionList = 'jpg,jpeg,png,gif,bmp';
		if (!empty($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_femanager.']['settings.']['misc.']['uploadFileExtension'])) {
			$extensionList = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_femanager.']['settings.']['misc.']['uploadFileExtension'];
			$extensionList = str_replace(' ', '', $extensionList);
		}
		$fileInfo = pathinfo($filename);

		if (
			!empty($fileInfo['extension']) &&
			GeneralUtility::inList($extensionList, strtolower($fileInfo['extension'])) &&
			GeneralUtility::verifyFilenameAgainstDenyPattern($filename)
		) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Hash a password from $user->getPassword()
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
	 * @param \string $method		"md5" or "sha1"
	 * @return void
	 */
	public static function hashPassword(&$user, $method) {
		switch ($method) {
			case 'md5':
				$user->setPassword(md5($user->getPassword()));
				break;

			case 'sha1':
				$user->setPassword(sha1($user->getPassword()));
				break;

			default:
				if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
					if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')) {
						$objInstanceSaltedPw = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
						$user->setPassword($objInstanceSaltedPw->getHashedPassword($user->getPassword()));
					}
				}
		}
	}

	/**
	 * Checks if object was changed or not
	 *
	 * @param $object
	 * @return \bool
	 */
	public static function isDirtyObject($object) {
		foreach (array_keys($object->_getProperties()) as $propertyName) {
			$property = ObjectAccess::getProperty($object, $propertyName);
			if ($property === NULL) {
				// if property can not be accessed
				continue;
			}

			/**
			 * std::Property (string, int, etc..),
			 * PHP-Objects (DateTime, RecursiveIterator, etc...),
			 * TYPO3-Objects (user, page, etc...)
			 */
			if (!$property instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
				if ($object->_isDirty($propertyName)) {
					return TRUE;
				}
			} else {
				/**
				 * ObjectStorage
				 */
				if ($property->_isDirty()) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Get changed properties (compare two objects with same getter methods)
	 *
	 * @param \In2\Femanager\Domain\Model\User $changedObject			Changed object
	 * @return \array
	 * 			[firstName][old] = Alex
	 * 			[firstName][new] = Alexander
	 */
	public static function getDirtyPropertiesFromObject($changedObject) {
		$dirtyProperties = array();
		$ignoreProperties = array(
			'txFemanagerChangerequest',
			'ignoreDirty',
			'isOnline',
			'lastlogin'
		);

		foreach ($changedObject->_getCleanProperties() as $propertyName => $propertyValue) {
			if (!method_exists($changedObject, 'get' . ucfirst($propertyName)) || in_array($propertyName, $ignoreProperties)) {
				continue;
			}
			if (!is_object($propertyValue)) {
				if ($propertyValue != $changedObject->{'get' . ucfirst($propertyName)}()) {
					$dirtyProperties[$propertyName]['old'] = $propertyValue;
					$dirtyProperties[$propertyName]['new'] = $changedObject->{'get' . ucfirst($propertyName)}();
				}
			} else {
				if (get_class($propertyValue) === DateTime) {
					if ($propertyValue->getTimestamp() != $changedObject->{'get' . ucfirst($propertyName)}()->getTimestamp()) {
						$dirtyProperties[$propertyName]['old'] = $propertyValue->getTimestamp();
						$dirtyProperties[$propertyName]['new'] = $changedObject->{'get' . ucfirst($propertyName)}()->getTimestamp();
					}
				} else {
					$titlesOld = self::implodeObjectStorageOnProperty($propertyValue);
					$titlesNew = self::implodeObjectStorageOnProperty($changedObject->{'get' . ucfirst($propertyName)}());
					if ($titlesOld != $titlesNew) {
						$dirtyProperties[$propertyName]['old'] = $titlesOld;
						$dirtyProperties[$propertyName]['new'] = $titlesNew;
					}
				}
			}
		}
		return $dirtyProperties;
	}

	/**
	 * overwrite user with old values and xml with new values
	 *
	 * @param \In2\Femanager\Domain\Model\User $user
	 * @param \array $dirtyProperties
	 * @return \In2\Femanager\Domain\Model\User $user
	 */
	public static function rollbackUserWithChangeRequest($user, $dirtyProperties) {
		$existingUserProperties = $user->_getCleanProperties();

		// reset old values
		$user->setUserGroup($existingUserProperties['usergroup']);
		foreach ($dirtyProperties as $propertyName => $propertyValue) {
			$propertyValue = NULL;
			$user->{'set' . ucfirst($propertyName)}($existingUserProperties[$propertyName]);
		}

		// store changes as xml in field fe_users.tx_femanager_changerequest
		$user->setTxFemanagerChangerequest(
			GeneralUtility::array2xml($dirtyProperties, '', 0, 'changes')
		);

		return $user;
	}

	/**
	 * Implode subjobjects on a property (example for usergroups: "ug1, ug2, ug3")
	 *
	 * @param \object $objectStorage
	 * @param \string $property
	 * @param \string $glue
	 * @return \string
	 */
	public static function implodeObjectStorageOnProperty($objectStorage, $property = 'uid', $glue = ', ') {
		$value = '';
		foreach ($objectStorage as $object) {
			if (method_exists($object, 'get' . ucfirst($property))) {
				$value .= $object->{'get' . ucfirst($property)}();
				$value .= $glue;
			}
		}
		return substr($value, 0, (strlen($glue) * -1));
	}

	/**
	 * Determine if supplied string is a valid MD5 Hash
	 *
	 * @param string $md5 String to validate
	 * @return boolean
	 */
	public static function isMd5($md5) {
		return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);
	}

	/**
	 * @param \string $title												Title to log
	 * @param \int $state													State to log
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user			Related User
	 * @return void
	 */
	public function log($title, $state, \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user) {
		// Disable Log
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['femanager']);
		if (!empty($confArr['disableLog'])) {
			return;
		}

		/* @var $log \In2\Femanager\Domain\Model\Log */
		$log = $this->objectManager->get('In2\Femanager\Domain\Model\Log');
		$log->setTitle($title);
		$log->setState($state);
		$log->setUser($user);
		$this->logRepository->add($log);
	}

	/**
	 * Create Hash from String and TYPO3 Encryption Key (if available)
	 *
	 * @param \string $string			Any String to hash
	 * @param \int $length				Hash Length
	 * @return \string $hash			Hashed String
	 */
	public static function createHash($string, $length = 10) {
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
			$hash = GeneralUtility::shortMD5($string . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], $length);
		} else {
			$hash = GeneralUtility::shortMD5($string, $length);
		}
		return $hash;
	}

	/**
	 * Create array for swiftmailer
	 * 		sender and receiver mail/name combination with fallback
	 *
	 * @param \string $emailString String with separated emails (splitted by \n)
	 * @param \string $name Name for every email name combination
	 * @return \array $mailArray
	 */
	public static function makeEmailArray($emailString, $name = 'femanager') {
		$emails = GeneralUtility::trimExplode("\n", $emailString, 1);
		$mailArray = array();
		foreach ($emails as $email) {
			if (!GeneralUtility::validEmail($email)) {
				continue;
			}
			$mailArray[$email] = $name;
		}
		return $mailArray;
	}

	/**
	 * Read values between brackets
	 *
	 * @param \string $value
	 * @return \string
	 */
	public static function getValuesInBrackets($value = 'test(1,2,3)') {
		preg_match_all( '/\(.*?\)/i', $value, $result);
		return str_replace(array('(', ')'), '', $result[0][0]);
	}

	/**
	 * Read values before brackets
	 *
	 * @param \string $value
	 * @return \string
	 */
	public static function getValuesBeforeBrackets($value = 'test(1,2,3)') {
		$valueParts = GeneralUtility::trimExplode('(', $value, 1);
		return $valueParts[0];
	}

	/**
	 * SendPost - Send values via curl to target
	 *
	 * @param \In2\Femanager\Domain\Model\User $user User properties
	 * @param array $config TypoScript Settings
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
	 * @return void
	 */
	public static function sendPost($user, $config, $contentObject) {
		// stop if turned off
		if (!$contentObject->cObjGetSingle($config['new.']['sendPost.']['_enable'], $config['new.']['sendPost.']['_enable.'])) {
			return;
		}

		$properties = $user->_getCleanProperties();
		$contentObject->start($properties);
		$curl = array(
			'url' => $config['new.']['sendPost.']['targetUrl'],
			'data' => $contentObject->cObjGetSingle($config['new.']['sendPost.']['data'], $config['new.']['sendPost.']['data.']),
			'properties' => $properties
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl['url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curl['data']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_exec($ch);
		curl_close($ch);

		// Debug Output
		if ($config['new.']['sendPost.']['debug']) {
			GeneralUtility::devLog('femanager sendpost values', 'femanager', 0, $curl);
		}
	}

	/**
	 * Store user values in any database table
	 *
	 * @param \In2\Femanager\Domain\Model\User $user User properties
	 * @param array $config TypoScript Settings
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public static function storeInDatabasePreflight($user, $config, $contentObject, $objectManager) {
		$uid = 0;
		if (empty($config['new.']['storeInDatabase.'])) {
			return;
		}

		// one loop for every table to store
		foreach ((array) $config['new.']['storeInDatabase.'] as $table => $storeSettings) {
			$storeSettings = NULL;
			// if turned off
			if (
				!$contentObject->cObjGetSingle(
					$config['new.']['storeInDatabase.'][$table]['_enable'],
					$config['new.']['storeInDatabase.'][$table]['_enable.']
				)
			) {
				continue;
			}
			// push user values to TypoScript to use with ".field=username"
			$contentObject->start(array_merge($user->_getProperties(), array('lastGeneratedUid' => $uid)));

			/**
			 * @var $storeInDatabase \In2\Femanager\Utility\StoreInDatabase
			 */
			$storeInDatabase = $objectManager->get('In2\Femanager\Utility\StoreInDatabase');
			$storeInDatabase->setTable($table);
			foreach ($config['new.']['storeInDatabase.'][$table] as $field => $value) {
				if ($field[0] === '_' || stristr($field, '.')) {
					continue;
				}
				$value = $contentObject->cObjGetSingle(
					$config['new.']['storeInDatabase.'][$table][$field],
					$config['new.']['storeInDatabase.'][$table][$field . '.']
				);
				$storeInDatabase->addProperty($field, $value);
			}
			$uid = $storeInDatabase->execute();
		}
	}

	/**
	 * Remove FE Session to a given user
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
	 * @return void
	 */
	public static function removeFrontendSessionToUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user) {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('fe_sessions', 'ses_userid = ' . intval($user->getUid()));
	}

	/**
	 * Check if FE Session exists
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
	 * @return bool
	 */
	public static function checkFrontendSessionToUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user) {
		$select = 'ses_id';
		$from = 'fe_sessions';
		$where = 'ses_userid = ' . intval($user->getUid());
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if (!empty($row['ses_id'])) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Generate random string
	 *
	 * @return string
	 */
	public static function getRandomString() {
		$randomNumber = mt_rand(0, 9999999999999999);
		return GeneralUtility::shortMD5($randomNumber);
	}

	/**
	 * Read fe_users image uploadfolder from TCA
	 *
	 * @return \string path - standard "uploads/pics"
	 */
	public static function getUploadFolderFromTca() {
		$path = $GLOBALS['TCA']['fe_users']['columns']['image']['config']['uploadfolder'];
		if (empty($path)) {
			$path = 'uploads/pics';
		}
		return $path;
	}

	/**
	 * Generate and send Email
	 *
	 * @param \string $template					Template file in Templates/Email/
	 * @param \array $receiver					Combination of Email => Name
	 * @param \array $sender					Combination of Email => Name
	 * @param \string $subject					Mail subject
	 * @param \array $variables					Variables for assignMultiple
	 * @param \array $typoScript				Add TypoScript to overwrite values
	 * @return \bool							Mail was sent?
	 */
	public function sendEmail($template, $receiver, $sender, $subject, $variables = array(), $typoScript = array()) {
		// config
		$email = $this->objectManager->get('\TYPO3\CMS\Core\Mail\MailMessage');
		$this->cObj = $this->configurationManager->getContentObject();
		if (!empty($variables['user']) && method_exists($variables['user'], '_getProperties')) {
			$this->cObj->start($variables['user']->_getProperties());
		}
		if (
			!$this->cObj->cObjGetSingle($typoScript['_enable'], $typoScript['_enable.']) ||
			count($receiver) === 0
		) {
			return FALSE;
		}

		// add embed images to mail body
		if ($this->cObj->cObjGetSingle($typoScript['embedImage'], $typoScript['embedImage.'])) {
			$images = GeneralUtility::trimExplode(
				',',
				$this->cObj->cObjGetSingle($typoScript['embedImage'], $typoScript['embedImage.']),
				1
			);
			$imageVariables = array();
			foreach ($images as $image) {
				$imageVariables[] = $email->embed(\Swift_Image::fromPath($image));
			}
			$variables = array_merge($variables, array('embedImages' => $imageVariables));
		}

		/**
		 * Generate Email Body
		 */
		$emailBodyObject = $this->objectManager->get('\TYPO3\CMS\Fluid\View\StandaloneView');
		$emailBodyObject->getRequest()->setControllerExtensionName('Femanager');
		$emailBodyObject->getRequest()->setPluginName('Pi1');
		$emailBodyObject->getRequest()->setControllerName('New');
		$emailBodyObject->setTemplatePathAndFilename(
			$this->getTemplatePath('Email/' . ucfirst($template) . '.html')
		);
		$emailBodyObject->setLayoutRootPath($this->getTemplateFolder('layout'));
		$emailBodyObject->setPartialRootPath($this->getTemplateFolder('partial'));
		$emailBodyObject->assignMultiple($variables);

		/**
		 * Generate and send Email
		 */
		$email
			->setTo($receiver)
			->setFrom($sender)
			->setSubject($subject)
			->setCharset($GLOBALS['TSFE']->metaCharset)
			->setBody($emailBodyObject->render(), 'text/html');

		// overwrite email receiver
		if (
			$this->cObj->cObjGetSingle($typoScript['receiver.']['email'], $typoScript['receiver.']['email.']) &&
			$this->cObj->cObjGetSingle($typoScript['receiver.']['name'], $typoScript['receiver.']['name.'])
		) {
			$email->setTo(
				array(
					$this->cObj->cObjGetSingle($typoScript['receiver.']['email'], $typoScript['receiver.']['email.']) =>
					$this->cObj->cObjGetSingle($typoScript['receiver.']['name'], $typoScript['receiver.']['name.'])
				)
			);
		}

		// overwrite email sender
		if (
			$this->cObj->cObjGetSingle($typoScript['sender.']['email'], $typoScript['sender.']['email.']) &&
			$this->cObj->cObjGetSingle($typoScript['sender.']['name'], $typoScript['sender.']['name.'])
		) {
			$email->setFrom(
				array(
					$this->cObj->cObjGetSingle($typoScript['sender.']['email'], $typoScript['sender.']['email.']) =>
					$this->cObj->cObjGetSingle($typoScript['sender.']['name'], $typoScript['sender.']['name.'])
				)
			);
		}

		// overwrite email subject
		if ($this->cObj->cObjGetSingle($typoScript['subject'], $typoScript['subject.'])) {
			$email->setSubject($this->cObj->cObjGetSingle($typoScript['subject'], $typoScript['subject.']));
		}

		// overwrite email CC receivers
		if ($this->cObj->cObjGetSingle($typoScript['cc'], $typoScript['cc.'])) {
			$email->setCc($this->cObj->cObjGetSingle($typoScript['cc'], $typoScript['cc.']));
		}

		// overwrite email priority
		if ($this->cObj->cObjGetSingle($typoScript['priority'], $typoScript['priority.'])) {
			$email->setPriority($this->cObj->cObjGetSingle($typoScript['priority'], $typoScript['priority.']));
		}

		// add attachments from typoscript
		if ($this->cObj->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.'])) {
			$files = GeneralUtility::trimExplode(
				',',
				$this->cObj->cObjGetSingle($typoScript['attachments'], $typoScript['attachments.']),
				1
			);
			foreach ($files as $file) {
				$email->attach(\Swift_Attachment::fromPath($file));
			}
		}

		$email->send();

		return $email->isSent();
	}

	/**
	 * Get absolute paths for templates with fallback
	 *
	 * @param string $part "template", "partial", "layout"
	 * @return string
	 */
	public function getTemplateFolder($part = 'template') {
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$templatePath = $extbaseFrameworkConfiguration['view'][$part . 'RootPath'];
		if (empty($templatePath)) {
			$templatePath = 'EXT:femanager/Resources/Private/' . ucfirst($part) . 's/';
		}
		$absoluteTemplatePath = GeneralUtility::getFileAbsFileName($templatePath);
		return $absoluteTemplatePath;
	}

	/**
	 * Return path and filename for a file
	 * 		respect *RootPaths and *RootPath
	 *
	 * @param string $relativePathAndFilename e.g. Email/Name.html
	 * @param string $part "template", "partial", "layout"
	 * @return string
	 */
	public function getTemplatePath($relativePathAndFilename, $part = 'template') {
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		if (!empty($extbaseFrameworkConfiguration['view'][$part . 'RootPaths'])) {
			foreach ($extbaseFrameworkConfiguration['view'][$part . 'RootPaths'] as $path) {
				$absolutePath = GeneralUtility::getFileAbsFileName($path);
				if (file_exists($absolutePath . $relativePathAndFilename)) {
					$absolutePathAndFilename = $absolutePath . $relativePathAndFilename;
				}
			}
		} else {
			$absolutePathAndFilename = GeneralUtility::getFileAbsFileName(
				$extbaseFrameworkConfiguration['view'][$part . 'RootPath'] . $relativePathAndFilename
			);
		}
		if (empty($absolutePathAndFilename)) {
			$absolutePathAndFilename = GeneralUtility::getFileAbsFileName(
				'EXT:femanager/Resources/Private/' . ucfirst($part) . 's/' . $relativePathAndFilename
			);
		}
		return $absolutePathAndFilename;
	}
}