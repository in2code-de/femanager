.. include:: ../Includes.txt
.. include:: Images.txt

.. _bestpractice:

Best Practice
=============

.. only:: html

	:ref:`changetemplates` | :ref:`countryselect` | :ref:`newfields` | :ref:`extendvalidators` | :ref:`signalslots` |


.. _changetemplates:

Use own HTML Templates
----------------------

Basics
^^^^^^

If you want to modify a HTML-File of femanager, you should not overwrite them directly in the extension folder.
Think about upcoming versions with important bugfixes or security-patches.

There are two ways to use own HTML-Templates (and Partials / Layouts) instead of the original Templates.

Replace all HTML Templates from Femanager with own Templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can copy all Files from
- EXT:femanager/Resources/Private/Templates/
- EXT:femanager/Resources/Private/Partials/
- EXT:femanager/Resources/Private/Layouts/

to a new folder in fileadmin - e.g. fileadmin/templates/femanager/ and modify them as you want.
After that, you should say femanager to use the new Templates with some lines of TypoScript setup:

.. code-block:: text

	plugin.tx_femanager {
		view {
			templateRootPath = fileadmin/templates/femanager/Templates/
			partialRootPath = fileadmin/templates/femanager/Partials/
			layoutRootPath = fileadmin/templates/femanager/Layouts/
		}
	}

Replace single HTML Template-Files
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can copy only the Files that you want to modify from
- EXT:femanager/Resources/Private/Templates/
- EXT:femanager/Resources/Private/Partials/
- EXT:femanager/Resources/Private/Layouts/

to a new folder in fileadmin - e.g. fileadmin/templates/femanager/ and modify them as you want.
After that, you should say femanager to use the old folders and merge them with the new folders

.. code-block:: text

	plugin.tx_femanager {
		view {
			templateRootPath >
			templateRootPaths {
				10 = EXT:femanager/Resources/Private/Templates/
				20 = templateRootPath = fileadmin/templates/femanager/Templates/
			}
			partialRootPath >
			partialRootPaths {
				10 = EXT:femanager/Resources/Private/Partials/
				20 = templateRootPath = fileadmin/templates/femanager/Partials/
			}
			layoutRootPath >
			layoutRootPaths {
				10 = EXT:femanager/Resources/Private/Layouts/
				20 = templateRootPath = fileadmin/templates/femanager/Layouts/
			}
		}
	}


.. _countryselect:

Using static_info_tables for country selection
----------------------------------------------

Basics
^^^^^^

- Install Extension static_info_tables
- Install Extension static_info_tables(_de)(_fr)(_pl) etc... for localized countrynames
- Import Records of the extensions via Extension Manager (see manual of static_info_tables)
- Clear Cache
- Copy all Partials from femanager to a fileadmin folder
- Set the new Partial Path via Constants: plugin.tx_femanager.view.partialRootPath = fileadmin/femanager/Partials/
- Open Partial Fields/Country.html and activate static_info_tables (see notes in HTML-File)

Details for Partial Country.html
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The idea is very simple. You can change the “options Attribute” of the form.select ViewHelper:



.. code-block:: text

	<femanager:form.select
		id="femanager_field_country"
		property="country"
		options="{femanager:Form.GetCountriesFromStaticInfoTables()}"
		defaultOption="{f:translate(key:'pleaseChoose')}"
		class="input-block-level"
		additionalAttributes="{femanager:Misc.FormValidationData(settings:'{settings}',fieldName:'country')}" />

The GetCountriesFromStaticInfoTables-ViewHelper

Possible options for this ViewHelper are:

.. t3-field-list-table::
 :header-rows: 1

 - :Name:
      Name
   :Description:
      Description
   :Default:
      Default Value
   :Examplevalue:
      Example Value

 - :Name:
      key
   :Description:
      Define the Record Column of static_countries table which should be used for storing to fe_users country

      Note: Please use lowerCamelCase Writing for Fieldnames

   :Default:
      isoCodeA3
   :Examplevalue:
      isoCodeA2

 - :Name:
      value
   :Description:
      Define the Record Column of static_countries table which should be visible in selection in femanager

      Note: Please use lowerCamelCase Writing for Fieldnames


   :Default:
      officialNameLocal
   :Examplevalue:
      shortNameFr

 - :Name:
      sortbyField
   :Description:
      Define the Record Column of static_countries which should be used for a sorting

      Note: Please use lowerCamelCase Writing for Fieldnames

   :Default:
      isoCodeA3
   :Examplevalue:
      shortNameDe

 - :Name:
      sorting
   :Description:
      Could be 'asc' or 'desc' for Ascending or Descending Sorting
   :Default:
      asc
   :Examplevalue:
      desc

Some Examples are:

.. code-block:: text

	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA2',value:'shortNameDe')}
	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA2',value:'shortNameFr',sortbyField:'shortNameFr')}
	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA3',value:'isoCodeA3',sortbyField:'isoCodeA3',sorting:'asc')}

.. _newfields:

Adding new fields to fe_users with your own extension
-----------------------------------------------------

Picture
^^^^^^^

|extendFields|

Add new Fields to the Registraion-/Editform


Basics
^^^^^^

- Use TSConfig to add one or more fields to the field selection in femanager flexform
- Copy the partial folder and add your fields
- Create a new extension with ext_tables.sql and ext_tables.php for adding one or more new fields to fe_users
- Create your own user model with getter/setter for your new fields that extends the user model from femanager
- Use TypoScript to include your model
- Override the createAction and updateAction to manipulte the object type

See EXT:femanager/Resources/Private/Software/femanagerextended_0.1.0.zip for an example extension how to extend femanager with new fields and validation methods (or see http://forge.typo3.org/projects/extension- femanager/repository/revisions/master/show/Resources/Private/Software )


Step by Step
^^^^^^^^^^^^


Add new fields to the flexform
""""""""""""""""""""""""""""""

|newFields|

Extend Fieldselection in Flexform

Add some Page-TSConfig to extend the selection:

.. code-block:: text

	tx_femanager {
		flexForm {
				new {
						addFieldOptions {
								twitterId = Twitter ID
								skypeId = Skype ID
								somethingElse = LLL:EXT:ext/Resources/Private/Language/locallang_be.xlf:custom
						}
				}
				edit < tx_femanager.flexForm.new
		}
	}


Modify the partial folder
"""""""""""""""""""""""""

“twitterId” (see TSConfig) means that femanager searches for a partial TwitterId.html to render the field in the form. So you have to copy the folder EXT:femanager/Resources/Private/Partials (e.g.) to fileadmin/Partials and set the new partial path via TypoScript Constants (see exmple below). In addition you have to add the new Partials files.

.. code-block:: text

	plugin.tx_femanager.view.partialRootPath = fileadmin/Partials/


Example file fileadmin/Partials/Fields/TwitterId.html:

.. code-block:: text

	{namespace femanager=In2\Femanager\ViewHelpers}
	<div class="femanager_fieldset control-group">
			<label for="twitterId" class="control-label">
					<f:translate key="tx_femanagerextended_domain_model_user.twitter_id" extensionName="femanagerextended">Twitter</f:translate>
			</label>
			<div class="controls">
					<femanager:form.textfield
									id="twitterId"
									property="twitterId"
									class="input-block-level"
									additionalAttributes="{femanager:Misc.FormValidationData(settings:'{settings}',fieldName:'twitterId')}" />
			</div>
	</div>


ext_tables.sql
""""""""""""""

Example SQL file in your extension which extends fe_users with your new fields:

.. code-block:: text

	CREATE TABLE fe_users (
		twitter_id varchar(255) DEFAULT '' NOT NULL,
		skype_id varchar(255) DEFAULT '' NOT NULL,
		tx_extbase_type varchar(255) DEFAULT '0' NOT NULL,
	);


ext_tables.php
""""""""""""""

Example ext_tables.php file:

.. code-block:: text

	\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
	$TCA['tx_test_domain_model_address']['ctrl']['type'] = 'tx_extbase_type';
	$tmp_fe_users_columns = array(
			'twitter_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:femanagerextended/Resources/Private/Language/locallang_db.xlf:tx_femanagerextended_domain_model_user.twitter_id',
					'config' => array(
							'type' => 'input',
							'size' => 30,
							'eval' => 'trim'
					),
			),
			'skype_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:femanagerextended/Resources/Private/Language/locallang_db.xlf:tx_femanagerextended_domain_model_user.skype_id',
					'config' => array(
							'type' => 'input',
							'size' => 30,
							'eval' => 'trim'
					),
			),
			'tx_extbase_type' => array(
					'config' => array(
							'type' => 'input',
							'default' => '0'
					)
			)
	);


	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_fe_users_columns, 1);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'twitter_id, skype_id');


Own User Model
""""""""""""""

Example Model User.php which extends to the default femanager Model:

.. code-block:: text

	namespace In2\Femanagerextended\Domain\Model;

	class User extends \In2\Femanager\Domain\Model\User {

		/**
		 * twitterId
		 *
		 * @var \string
		 */
		protected $twitterId;

		/**
		 * skypeId
		 *
		 * @var \string
		 */
		protected $skypeId;

		/**
		 * Returns the twitterId
		 *
		 * @return \string $twitterId
		 */
		public function getTwitterId() {
				return $this->twitterId;
		}

		/**
		 * Sets the twitterId
		 *
		 * @param \string $twitterId
		 * @return void
		 */
		public function setTwitterId($twitterId) {
				$this->twitterId = $twitterId;
		}

		/**
		 * Returns the skypeId
		 *
		 * @return \string $skypeId
		 */
		public function getSkypeId() {
				return $this->skypeId;
		}

		/**
		 * Sets the skypeId
		 *
		 * @param \string $skypeId
		 * @return void
		 */
		public function setSkypeId($skypeId) {
				$this->skypeId = $skypeId;
		}

		/**
		 * @param string $username
		 */
		public function setUsername($username) {
				$this->username = $username;
		}

	}


TypoScript to include Model and override default controller
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: text

	config.tx_extbase{
		persistence{
			classes{
				In2\Femanager\Domain\Model\User {
					subclasses {
						0 = In2\Femanagerextended\Domain\Model\User
					}
				}
				In2\Femanagerextended\Domain\Model\User {
					mapping {
						tableName = fe_users
						recordType = 0
					}
				}
			}
		}
		objects {
			In2\Femanager\Controller\NewController.className = In2\Femanagerextended\Controller\NewController
			In2\Femanager\Controller\EditController.className = In2\Femanagerextended\Controller\EditController
		}
	}


Own Controller Files
""""""""""""""""""""

EditController.php:

.. code-block:: text

	namespace In2\Femanagerextended\Controller;

	class EditController extends \In2\Femanager\Controller\EditController {

		/**
		 * action update
		 *
		 * @param \In2\Femanagerextended\Domain\Model\User $user
		 * @validate $user In2\Femanager\Domain\Validator\ServersideValidator
		 * @validate $user In2\Femanager\Domain\Validator\PasswordValidator
		 * @return void
		 */
		public function updateAction(\In2\Femanagerextended\Domain\Model\User $user) {
			parent::updateAction($user);
		}
	}


NewController.php:

.. code-block:: text

	namespace In2\Femanagerextended\Controller;

	class NewController extends \In2\Femanager\Controller\NewController {

		/**
		 * action create
		 *
		 * @param \In2\Femanagerextended\Domain\Model\User $user
		 * @validate $user In2\Femanager\Domain\Validator\ServersideValidator
		 * @validate $user In2\Femanager\Domain\Validator\PasswordValidator
		 * @return void
		 */
		public function createAction(\In2\Femanagerextended\Domain\Model\User $user) {
			parent::createAction($user);
		}
	}


.. _extendvalidators:

Adding own serverside and clientside validation to femanager forms
------------------------------------------------------------------

Picture
^^^^^^^

|newField1|

Add own clientside (JcavaScript) Validation

|newField2|

Add own serverside (PHP) Validation


Basics
^^^^^^

- Use TypoScript to override ValidationClass of femanager with own classes – this enables your validation methods
- Config the new validation methods via TypoScript
- Add translation labels via TypoScript

See EXT:femanager/Resources/Private/Software/femanagerextended_0.1.0.zip for an example extension how to extend femanager with new fields and validation methods


Step by Step
^^^^^^^^^^^^

Override Validation Classes with TypoScript
"""""""""""""""""""""""""""""""""""""""""""

.. code-block:: text

	config.tx_extbase{
		objects {
			In2\Femanager\Domain\Validator\ServersideValidator.className = In2\Femanagerextended\Domain\Validator\CustomServersideValidator
			In2\Femanager\Domain\Validator\ClientsideValidator.className = In2\Femanagerextended\Domain\Validator\CustomClientsideValidator
		}
	}

New validation classes
""""""""""""""""""""""

CustomClientsideValidator.php:

.. code-block:: text

	namespace In2\Femanagerextended\Domain\Validator;

	class CustomClientsideValidator extends \In2\Femanager\Domain\Validator\ClientsideValidator {

		/**
		 * Custom Validator
		 *              Activate via TypoScript - e.g. plugin.tx_femanager.settings.new.validation.username.custom = validationSetting
		 *
		 * @param \string $value Given value from input field
		 * @param \string $validationSetting TypoScript Setting for this field
		 * @return bool
		 */
		protected function validateCustom($value, $validationSetting) {

			// check if string has string inside
			if (stristr($value, $validationSetting)) {
				return TRUE;
			}
			return FALSE;
		}
	}

CustomServersideValidator.php:

.. code-block:: text

	namespace In2\Femanagerextended\Domain\Validator;

	class CustomServersideValidator extends \In2\Femanager\Domain\Validator\ServersideValidator {

		/**
		 * Custom Validator
		 *              Activate via TypoScript - e.g. plugin.tx_femanager.settings.new.validation.username.custom = validationSetting
		 *
		 * @param \string $value Given value from input field
		 * @param \string $validationSetting TypoScript Setting for this field
		 * @return bool
		 */
		protected function validateCustom($value, $validationSetting) {

			// check if string has string inside
			if (stristr($value, $validationSetting)) {
				return TRUE;
			}
			return FALSE;
		}
	}


TypoScript to enable new validation and set labels
""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: text

	plugin.tx_femanager {
		settings.new.validation {
			_enable.client = 1
			_enable.server = 1
			username {
				# Custom Validator - check if value includes "abc"
				custom = abc
			}
		}
		_LOCAL_LANG {
			default.validationErrorCustom = "abc" is missing
			de.validationErrorCustom = "abc" wird erwartet
		}
	}


.. _signalslots:

Using SignalSlots (Hook pendant) to extend femanager
----------------------------------------------------

Introduction
^^^^^^^^^^^^

SignalSlots (former Hooks) are the possibility for other developer to extend the runtime of a femanager process with their own code.

Introduction
^^^^^^^^^^^^

As an example let's build an extension which sends username and email address of a new registered user to a defined email address.

Note: this is a little bit useless because there is already a setting in flexform to inform administrators and there is a setting in TypoScript to POST values to a third-party-software, but let's use this case for an example.

SignalSlots List
^^^^^^^^^^^^^^^^


.. t3-field-list-table::
 :header-rows: 1

 - :File:
      File
   :Located:
      Located in
   :Signal:
      Signal Name
   :Parameters:
      Available Parameters
   :Description:
      Description

 - :File:
      NewController.php
   :Located:
      createAction()
   :Signal:
      createActionBeforePersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process before the new user was persisted

 - :File:
      NewController.php
   :Located:
      confirmCreateRequestAction()
   :Signal:
      confirmCreateRequestActionBeforePersist
   :Parameters:
      $user, $hash, $status, $this
   :Description:
      Use this signal if you want to hook into the confirmation process

 - :File:
      AbstractController.php
   :Located:
      finalCreate()
   :Signal:
      finalCreateAfterPersist
   :Parameters:
      $user, $action, $this
   :Description:
      Use this signal if you want to hook into the process after the new user was persisted

 - :File:
      AbstractController.php
   :Located:
      updateAllConfirmed()
   :Signal:
      updateAllConfirmedAfterPersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process after the new user was persisted

 - :File:
      EditController.php
   :Located:
      updateAction()
   :Signal:
      updateActionBeforePersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process before the user- profile was updated

 - :File:
      EditController.php
   :Located:
      confirmUpdateRequestAction()
   :Signal:
      updateActionBeforePersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process before the user- profile was updated

 - :File:
      InvitationController.php
   :Located:
      createAction()
   :Signal:
      confirmUpdateRequestActionAfterPersist
   :Parameters:
      $user, $hash, $status, $this
   :Description:
      Use this signal if you want to hook into the process after a new user was persisted

 - :File:
      InvitationController.php
   :Located:
      createAllConfirmed()
   :Signal:
      createAllConfirmedAfterPersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process after a new user was persisted

 - :File:
      InvitationController.php
   :Located:
      editAction()
   :Signal:
      editActionAfterPersist
   :Parameters:
      $user, $hash, $this
   :Description:
      Use this signal if you want to hook into the process before a user adds a new password (step 1)

 - :File:
      InvitationController.php
   :Located:
      updateAction()
   :Signal:
      updateActionAfterPersist
   :Parameters:
      $user, $this
   :Description:
      Use this signal if you want to hook into the process after a user adds a new password (step 2)

 - :File:
      ?
   :Located:
      ?
   :Signal:
      ?
   :Parameters:
      ?
   :Description:
      Do you need a new Signal in femanager? Just request one on forge.typo3.org


Use a SignalSlot
^^^^^^^^^^^^^^^^

Introduction
""""""""""""

As described before, we want to send an email to a defined address every time when a new user is registered.

Creating an extension
"""""""""""""""""""""

femanagersignalslot/ext_emconf.php:

This file is important to install your new extension – write something like:

.. code-block:: text

	<?php

	$EM_CONF[$_EXTKEY] = array(
			'title' => 'femanagersignalslot',
			'description' => 'signalslotexample for femanager',
			'state' => 'alpha',
			'version' => '0.0.1',
			'constraints' => array(
					'depends' => array(
							'extbase' => '6.0.0-6.1.99',
							'fluid' => '6.0.0-6.1.99',
							'typo3' => '6.0.0-6.1.99',
							'femanager' => '1.0.0-1.0.99',
					),
					'conflicts' => array(
					),
					'suggests' => array(
					),
			),
	);

femanagersignalslot/ext_localconf.php:

This is an example how to use a signal from femanager – in this case we decided to use the signal “createActionBeforePersist” in class “In2FemanagerControllerNewController” and want to call a slot in class “In2FemanagersignalslotUtilitySendMail” with methodname “send()”

.. code-block:: text

	<?php

	$signalSlotDispatcher = t3lib_div::makeInstance('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
	$signalSlotDispatcher->connect(
		'In2\Femanager\Controller\NewController',
		'createActionBeforePersist',
		'In2\Femanagersignalslot\Utility\SendMail',
		'send',
		FALSE
	);

femanagersignalslot/Classes/Utility/SendMail.php:

This is our main class which is called every time a new registration process was initiated.

.. code-block:: text

	<?php
	namespace In2\Femanagersignalslot\Utility;

	class SendMail {

		/**
		 * Send mail about user information
		 *
		 * @param \In2\Femanager\Domain\Model\User $user
		 * @param \In2\Femanager\Controller\NewController $pObj
		 * @return void
		 */
		public function send($user, $pObj) {
			$message = '
				New user registered
				Username: ' . $user->getUsername() . '
				Email: ' . $user->getEmail() . '
			';
			mail('your@email.com', 'SignalSlot Test', $message);
		}
	}