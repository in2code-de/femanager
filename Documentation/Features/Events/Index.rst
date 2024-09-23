.. include:: ../../Includes.rst.txt

.. _events:

Use Events to extend femanager
----------------------------------------------------

Introduction
^^^^^^^^^^^^

Events can be used for developers to extend femanager processes with their own code.
Previously, signals or hooks had been used for this purpose. But since TYPO3 v10 PSR-14 Events should be used for signalling.

Please refer to the official TYPO3 documentation for an explanation of EventDispatcher (PSR-14 Events)

https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/EventDispatcher/Index.html

This blog article describes how to use PSR-14 Events in TYPO3.

https://usetypo3.com/psr-14-events.html

Events List
^^^^^^^^^^^^^^^^

.. t3-field-list-table::
   :header-rows: 1

   -  :Event:
         Event
      :Where:
         Where
      :Replaces signal:
         Replaces signal
      :Parameters:
         Parameters
      :Description:
         Description

   -  :Event:
         AdminConfirmationUserEvent
      :Where:
         UserBackendController->confirmUserAction()
      :Replaces signal:
         confirmUserAction
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after a user confirmation has been initiated by an admin in the backend

   -  :Event:
         AfterMailSendEvent
      :Where:
         SendMailService->send()
      :Replaces signal:
         AfterSend
      :Parameters:
         $email, $variables
      :Description:
         Use this event if you would like to trigger an action after an email has been sent

   -  :Event:
         AfterUserUpdateEvent
      :Where:
         EditController->confirmUpdateRequestAction()
      :Replaces signal:
         confirmUpdateRequestActionAfterPersist
      :Parameters:
         $user, $hash, $status
      :Description:
         Use this event if you would like to trigger an action after a changed user profile has been persisted

   -  :Event:
         BeforeMailSendEvent
      :Where:
         SendMailService->send()
      :Replaces signal:
         BeforeSend
      :Parameters:
         $email, $variables
      :Description:
         Use this event if you would like to trigger an action before an email is sent

   -  :Event:
         BeforeUpdateUserEvent
      :Where:
         EditController->updateAction()
      :Replaces signal:
         updateActionBeforePersist
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action before changes to a user profile are persisted

   -  :Event:
         BeforeUserConfirmEvent
      :Where:
         NewController->confirmCreateRequestAction()
      :Replaces signal:
         ??
      :Parameters:
         $user, $hash, $status
      :Description:
         Use this event if you would like to trigger an action after a new user has registered but not confirmed his account

   -  :Event:
         BeforeUserCreateEvent
      :Where:
         NewController->createAction()
      :Replaces signal:
         createActionBeforePersist
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action before a newly registered user is created

   -  :Event:
         CreateConfirmationRequestEvent
      :Where:
         NewController->createAdminConfirmationRequest()
      :Replaces signal:
         createAdminConfirmationRequestAutoConfirm
         createAdminConfirmationRequestManualConfirmation
      :Parameters:
         $user, $mode
      :Description:
         Use this event if you would like to trigger an action after an admin confirmation has been requested for a new user

   -  :Event:
         DeleteUserEvent
      :Where:
         EditController->deleteAction()
      :Replaces signal:
         deleteAction
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action when the deletion of a user is started

   -  :Event:
         FinalCreateEvent
      :Where:
         AbstractController->finalCreateAction()
      :Replaces signal:
         finalCreateAfterPersist
      :Parameters:
         $user, $action
      :Description:
         Use this event if you would like to trigger an action after a new user has been created and persisted

   -  :Event:
         FinalUpdateEvent
      :Where:
         AbstractController->updateAllConfirmed()
      :Replaces signal:
         updateAllConfirmedAfterPersist
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after changes to a user profile that do not require admin confirmation have been persisted

   -  :Event:
         ImpersonateEvent
      :Where:
         UserController->loginAsAction()
      :Replaces signal:
         loginAsAction
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after a backend user has logged in as a fronted user

   -  :Event:
         InviteUserConfirmedEvent
      :Where:
         InvitationController->createAllConfirmed()
      :Replaces signal:
         createAllConfirmedAfterPersist
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after a new user has been created and no more confirmations are required

   -  :Event:
         InviteUserCreateEvent
      :Where:
         InvitationController->createAction()
      :Replaces signal:
         ??
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after a new user has been created by invitation

   -  :Event:
         InviteUserEditEvent
      :Where:
         InvitationController->editAction()
      :Replaces signal:
         editActionAfterPersist
      :Parameters:
         $user, $hash
      :Description:
         Use this event if you would like to trigger an action when a newly invited user changes his profile

   -  :Event:
         InviteUserUpdateEvent
      :Where:
         InvitationController->updateAction()
      :Replaces signal:
         updateActionBeforePersist
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action after a newly invited user has changed his profile

   -  :Event:
         RefuseUserEvent
      :Where:
         UserBackendController->refuseAction()
      :Replaces signal:
         refuseUserAction
      :Parameters:
         $user
      :Description:
         Use this event if you would like to trigger an action when a user is refused by an admin in the backend

   -  :Event:
         UniqueUserEvent
      :Where:
         AbstractValidator->validateUniqueDb()
      :Replaces signal:
         ??
      :Parameters:
         $value, $field, $user, $uniqueDb
      :Description:
         Use this event if you would like to trigger an action if a check is made if the user is unique in the database

   -  :Event:
         UserLogEvent
      :Where:
         LogUitility->log()
      :Replaces signal:
         ??
      :Parameters:
         $additionalProperties, $state, $user
      :Description:
         Use this event if you would like to trigger an action if user changes are logged

   -  :Event:
         UserWasConfirmedByAdminEvent
      :Where:
         NewController->confirmCreateRequestAction()
      :Replaces signal:
         ??
      :Parameters:
         $additionalProperties, $state, $user
      :Description:
         Use this event if you would like to trigger an action after a new user has been confirmed by an admin in the backend

   -  :Event:
      :Where:
      :Replaces signal:
      :Parameters:
         Do you need a new Event in femanager? Just request one on https://github.com/in2code-de/femanager
