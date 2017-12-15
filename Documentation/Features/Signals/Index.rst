.. include:: ../../Includes.txt

.. _signalslots:

Using SignalSlots (Hook pendant) to extend femanager
----------------------------------------------------

Introduction
^^^^^^^^^^^^

SignalSlots (former Hooks) are the possibility for other developer to extend the runtime of a femanager process with their own code.

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
         NewController.php
      :Located:
         createAdminConfirmationRequest()
      :Signal:
         createAdminConfirmationRequestAutoConfirm
      :Parameters:
         $user, $this
      :Description:
         Signal if a user was auto-confirmed

    - :File:
         NewController.php
      :Located:
         createAdminConfirmationRequest()
      :Signal:
         createAdminConfirmationRequestManualConfirmation
      :Parameters:
         $user, $this
      :Description:
         Signal if a user was not auto-confirmed and must be confirmed manually

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
         confirmUpdateRequestActionAfterPersist
      :Parameters:
         $user, $hash, $status, $this
      :Description:
         Use this signal if you want to hook after a profile was accepted or refused

    - :File:
         EditController.php
      :Located:
         deleteAction()
      :Signal:
         deleteAction
      :Parameters:
         $user, $this
      :Description:
         Use this signal if you want to hook into the process before the user- profile will be deleted

    - :File:
         InvitationController.php
      :Located:
         createAction()
      :Signal:
         confirmUpdateRequestActionBeforePersist
      :Parameters:
         $user, $hash, $status, $this
      :Description:
         Use this signal if you want to hook into the process before a new user was persisted

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
         UserController.php
      :Located:
         loginAsAction()
      :Signal:
         loginAsAction
      :Parameters:
         $user, $this
      :Description:
         Use this signal if you want to hook into the process after you simulate a frontend user login

    - :File:
         UserBackendController.php
      :Located:
         confirmUserAction()
      :Signal:
         confirmUserAction
      :Parameters:
         $user, $this
      :Description:
         Signal if a user profile was confirmed in backend module

    - :File:
         UserBackendController.php
      :Located:
         refuseUserAction()
      :Signal:
         refuseUserAction
      :Parameters:
         $user, $this
      :Description:
         Signal if a user profile was refused in backend module

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
         ?
      :Located:
         ?
      :Signal:
         ?
      :Parameters:
         ?
      :Description:
         Do you need a new Signal in femanager? Just request one on https://github.com/einpraegsam/femanager


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

This is an example how to use a signal from femanager – in this case we decided to use the signal “createActionBeforePersist” in class “In2codeFemanagerControllerNewController” and want to call a slot in class “In2codeFemanagersignalslotDomainServiceSendMailService” with methodname “send()”

.. code-block:: text

	<?php

	$signalSlotDispatcher = t3lib_div::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	$signalSlotDispatcher->connect(
		'In2code\Femanager\Controller\NewController',
		'createActionBeforePersist',
		'In2code\Femanagersignalslot\Domain\Service\SendMailService',
		'send',
		FALSE
	);

femanagersignalslot/Classes/Domain/Service/SendMailService.php:

This is our main class which is called every time a new registration process was initiated.

.. code-block:: text

	<?php
	namespace In2code\Femanagersignalslot\Domain\Service;

	class SendMailService
	{

		/**
		 * Send mail about user information
		 *
		 * @param \In2code\Femanager\Domain\Model\User $user
		 * @param \In2code\Femanager\Controller\NewController $pObj
		 * @return void
		 */
		public function send($user, $pObj)
		{
			$message = '
				New user registered
				Username: ' . $user->getUsername() . '
				Email: ' . $user->getEmail() . '
			';
			mail('your@email.com', 'SignalSlot Test', $message);
		}
	}
