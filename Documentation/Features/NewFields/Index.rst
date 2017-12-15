.. include:: ../../Includes.txt
.. include:: Images.txt

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

See https://github.com/einpraegsam/femanagerextended for an example extension how to extend femanager with new fields
and validation methods


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

	{namespace femanager=In2code\Femanager\ViewHelpers}
	<div class="femanager_fieldset control-group">
			<label for="twitterId" class="control-label">
					<f:translate key="tx_femanagerextended_domain_model_user.twitter_id" extensionName="femanagerextended">Twitter</f:translate>
			</label>
			<div class="controls">
					<femanager:form.textfield
									id="twitterId"
									property="twitterId"
									class="input-block-level"
									additionalAttributes="{femanager:Validation.FormValidationData(settings:'{settings}',fieldName:'twitterId')}" />
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

	namespace In2code\Femanagerextended\Domain\Model;

	class User extends \In2code\Femanager\Domain\Model\User {

		/**
		 * twitterId
		 *
		 * @var string
		 */
		protected $twitterId;

		/**
		 * skypeId
		 *
		 * @var string
		 */
		protected $skypeId;

		/**
		 * Returns the twitterId
		 *
		 * @return string $twitterId
		 */
		public function getTwitterId() {
				return $this->twitterId;
		}

		/**
		 * Sets the twitterId
		 *
		 * @param string $twitterId
		 * @return void
		 */
		public function setTwitterId($twitterId) {
				$this->twitterId = $twitterId;
		}

		/**
		 * Returns the skypeId
		 *
		 * @return string $skypeId
		 */
		public function getSkypeId() {
				return $this->skypeId;
		}

		/**
		 * Sets the skypeId
		 *
		 * @param string $skypeId
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
				In2code\Femanager\Domain\Model\User {
					subclasses {
						0 = In2code\Femanagerextended\Domain\Model\User
					}
				}
				In2code\Femanagerextended\Domain\Model\User {
					mapping {
						tableName = fe_users
						recordType = 0
					}
				}
			}
		}
		objects {
			In2code\Femanager\Controller\NewController.className = In2code\Femanagerextended\Controller\NewController
			In2code\Femanager\Controller\EditController.className = In2code\Femanagerextended\Controller\EditController
		}
	}


Own Controller Files
""""""""""""""""""""

EditController.php:

.. code-block:: text

	namespace In2code\Femanagerextended\Controller;

	class EditController extends \In2code\Femanager\Controller\EditController {

		/**
		 * action update
		 *
		 * @param In2code\Femanagerextended\Domain\Model\User $user
		 * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
		 * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
		 * @return void
		 */
		public function updateAction(\In2code\Femanagerextended\Domain\Model\User $user) {
			parent::updateAction($user);
		}
	}


NewController.php:

.. code-block:: text

	namespace In2code\Femanagerextended\Controller;

	class NewController extends \In2code\Femanager\Controller\NewController {

		/**
		 * action create
		 *
		 * @param In2code\Femanagerextended\Domain\Model\User $user
		 * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
		 * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
		 * @return void
		 */
		public function createAction(\In2code\Femanagerextended\Domain\Model\User $user) {
			parent::createAction($user);
		}
	}

**Note:** If there are PHP warnings like “…should be compatible with…“ for PHP7, see discusion and solution:
https://stackoverflow.com/questions/45563671/how-to-extend-femanager-controller-under-php-7/45564378
