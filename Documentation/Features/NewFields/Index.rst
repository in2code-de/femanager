.. include:: ../../Includes.rst.txt
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

- Create a new extension for the new fields
- Create ext_tables.sql and Configuration/TCA/Overrides/fe_users.php for adding one or more new fields to fe_users
- Add TSConfig file to add one or more fields to the field selection in femanager flexform and include it
- Use TypoScript to add your own partials folder and add the templates for your new fields
- Create your own user model with getter/setter for your new fields that extends the user model from femanager
- Override the user model in your ext_localconf.php

See https://github.com/in2code-de/femanagerextended for an example extension how to extend femanager with new fields
and validation methods


Step by Step
^^^^^^^^^^^^


Add new fields to the flexform
""""""""""""""""""""""""""""""

|newFields|

Extend Fieldselection in Flexform

Add some Page-TSConfig to extend the selection:

.. code-block:: typoscript

   tx_femanager {
      flexForm {
         new {
            addFieldOptions {
               twitterId = Twitter ID
               skypeId = Skype ID
               userCategories = User Categories
               somethingElse = LLL:EXT:yourextension/Resources/Private/Language/locallang_be.xlf:custom
            }
         }
         edit < tx_femanager.flexForm.new
      }
   }


Modify the partial folder
"""""""""""""""""""""""""

“twitterId” (see TSConfig) means that femanager searches for a partial TwitterId.html to render the field in the form. So you have to copy the folder EXT:femanager/Resources/Private/Partials (e.g.) to fileadmin/Partials and set the new partial path via TypoScript Constants (see exmple below). In addition you have to add the new Partials files.

.. code-block:: typoscript

   plugin.tx_femanager.view.partialRootPaths.100 = EXT:yourextension/Resources/Private/Partials/


Example file fileadmin/Partials/Fields/TwitterId.html:

.. code-block:: html

   {namespace femanager=In2code\Femanager\ViewHelpers}
   <div class="femanager_fieldset femanager_twitterid form-group">
      <label for="femanager_field_twitterid" class="col-sm-2 control-label">
         <f:translate key="tx_yourextension_domain_model_user.twitter_id" extensionName="yourextension" />
         <f:if condition="{femanager:Validation.IsRequiredField(fieldName:'twitterId')}">
            <span>*</span>
         </f:if>
      </label>
      <div class="col-sm-10">
         <femanager:form.textfield
               id="femanager_field_twitterid"
               property="twitterId"
               class="form-control"
               additionalAttributes="{femanager:Validation.FormValidationData(settings:settings,fieldName:'twitterId')}" />
      </div>
   </div>

Example file fileadmin/Partials/Fields/UserCategories.html:

.. code-block:: html

   <html
         xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
         xmlns:femanager="http://typo3.org/ns/In2code/Femanager/ViewHelpers"
         xmlns:femanagerextended="http://typo3.org/ns/In2code/Femanagerextended/ViewHelpers"
         data-namespace-typo3-fluid="true">
       <div class="femanager_fieldset femanager_user_categories form-group">
           <div class="col-sm-10">
               <fieldset>
                   <f:translate key="tx_femanagerextended_domain_model_user.user_categories" extensionName="femanagerextended" />
                   <f:if condition="{femanager:Validation.IsRequiredField(fieldName:'user_categories')}">
                       <span>*</span>
                   </f:if>
               </fieldset>
               <f:if condition="{femanager:Validation.IsRequiredField(fieldName:'user_categories')}">
                   <span>*</span>
               </f:if>
               <f:for as="category" each="{femanagerextended:form.getCategories(rootCategory:1)}">
                   <div class="form-check">
                       <f:form.checkbox property="userCategories" value="{category.uid}" multiple="true" class="form-check-input" />
                       <label class="form-check-label">{category.title}</label>
                   </div>
               </f:for>
               <f:comment>
                   <f:form.select property="userCategories" options="{femanagerextended:form.getCategories(rootCategory:1)}" optionLabelField="title" multiple="true" class="form-select" />
               </f:comment>
           </div>
       </div>
   </html>


ext_tables.sql
""""""""""""""

Example SQL file in your extension which extends fe_users with your new fields:

.. code-block:: sql

   CREATE TABLE fe_users (
      twitter_id varchar(255) DEFAULT '' NOT NULL,
      skype_id varchar(255) DEFAULT '' NOT NULL,
      user_categories tinytext DEFAULT '0'
   );


Configuration/TCA/Overrides/fe_users.php
""""""""""""""""""""""""""""""""""""""""

Example Configuration/TCA/Overrides/fe_users.php file:

.. code-block:: php

   $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
   $tmpFeUsersColumns = [
      'twitter_id' => [
         'exclude' => 1,
         'label' => 'LLL:EXT:yourextension/Resources/Private/Language/locallang_db.xlf:' .
            'tx_yourextension_domain_model_user.twitter_id',
         'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
         ],
      ],
      'skype_id' => [
         'exclude' => 1,
         'label' => 'LLL:EXT:yourextension/Resources/Private/Language/locallang_db.xlf:' .
            'tx_yourextension_domain_model_user.skype_id',
         'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
         ],
      ],
      'tx_extbase_type' => [
         'config' => [
            'type' => 'input',
            'default' => '0',
         ],
      ],
      'user_categories' => [
         'exclude' => 1,
         'label' => 'LLL:EXT:femanagerextended/Resources/Private/Language/locallang_db.xlf:' .
             'tx_femanagerextended_domain_model_user.user_categories',
         'config' => [
             'type' => 'category',
             'relationship' => 'oneToMany',
             'treeConfig' => [
                 'startingPoints' => 1,
             ],
             'minitems' => 0,
             'maxitems' => 99,
         ]
      ],
   ];

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmpFeUsersColumns);
   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'twitter_id, skype_id');


Own User Model
""""""""""""""

Example Model User.php which extends to the default femanager Model:

.. code-block:: php

   namespace In2code\Femanagerextended\Domain\Model;

   use TYPO3\CMS\Extbase\Domain\Model\Category;
   use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

   class User extends \In2code\Femanager\Domain\Model\User
   {

       /**
        * twitterId
        *
        * @var string
        */
       protected string $twitterId;

       /**
        * skypeId
        *
        * @var string
        */
       protected string $skypeId;

       /**
        * @var ObjectStorage<Category>
        */
       protected ObjectStorage $userCategories;

       public function __construct($username = '', $password = '')
       {
           parent::__construct($username, $password);
           $this->userCategories = new ObjectStorage();
       }


       /**
        * Returns the twitterId
        *
        * @return string $twitterId
        */
       public function getTwitterId(): string
       {
           return $this->twitterId;
       }

       /**
        * Sets the twitterId
        *
        * @param string $twitterId
        * @return void
        */
       public function setTwitterId(string $twitterId): void
       {
           $this->twitterId = $twitterId;
       }

       /**
        * Returns the skypeId
        *
        * @return string $skypeId
        */
       public function getSkypeId(): string
       {
           return $this->skypeId;
       }

       /**
        * Sets the skypeId
        *
        * @param string $skypeId
        * @return void
        */
       public function setSkypeId(string $skypeId): void
       {
           $this->skypeId = $skypeId;
       }

       /**
        * @return ObjectStorage
        */
       public function getUserCategories(): ObjectStorage
       {
           return $this->userCategories;
       }

       /**
        * @param ObjectStorage $userCategories
        */
       public function setUserCategories(ObjectStorage $userCategories): void
       {
           $this->userCategories = $userCategories;
       }

       /**
        * @param Category $category
        * @return void
        */
       public function addUserCategory(Category $category)
       {
           $this->userCategories->attach($category);
       }

       /**
        * @param Category $category
        * @return void
        */
       public function removeUserCategory(Category $category)
       {
           $this->userCategories->detach($category);
       }
   }


Include model with TYPO3 10.4.x
"""""""""""""""""""""""""""""""

Configuration/Extbase/Persistence/Classes.php:

.. code-block:: php

   return [
       \YourVendor\YourExtension\Domain\Model\User::class => [
           'tableName' => 'fe_users',
           'recordType' => 0,
       ],
   ];

ext_localconf.php:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Domain\Model\User::class] = [
       'className' => \YourVendor\YourExtension\Domain\Model\User::class,
   ];
   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
       ->registerImplementation(
           \In2code\Femanager\Domain\Model\User::class,
           \YourVendor\YourExtension\Domain\Model\User::class
       );


TypoScript to include Model and override default controller with TYPO3 9.5.x
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: typoscript

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


Own Controller Files with TYPO3 9.5.x
"""""""""""""""""""""""""""""""""""""

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

**Note:** If there are PHP warnings like “…should be compatible with…“ for PHP7, see discussion and solution:
https://stackoverflow.com/questions/45563671/how-to-extend-femanager-controller-under-php-7/45564378

**or try to change the Controller Files to:**

Own Controller Files with PHP 7.2 / TYPO3 9.5.x
"""""""""""""""""""""""""""""""""""""""""""""""

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
		public function updateAction($user) {
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
		public function createAction($user) {
			parent::createAction($user);
		}
	}
