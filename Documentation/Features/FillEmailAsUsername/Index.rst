.. include:: ../../Includes.txt


.. _fillEmailAsUsername:

Fill Email as Username during registraion
-----------------------------------------

Introduction
^^^^^^^^^^^^

**Available since 2.0**

If this setting is enabled, the mail address is taken as username. The setting can be applied in three situations:
   * During registration (new)
   * During edit process of an existing user (edit)
   * During Invitation process (invite)

Configuration
^^^^^^^^^^^^^

Enable the "fillEmailWithUsername" option for the area (new, edit, invite), where you like to apply it.

Example for "new" area:

:typoscript:`plugin.tx_femanager.settings.new.fillEmailWithUsername = 1`

Please take care, that the username is not set to required, otherwise it will not work

:typoscript:`plugin.tx_femanager.settings.new.validation.username.required = 0`

Example
'''''''

TypoScript:

::

   plugin.tx_femanager.settings {
       new {
            fillEmailWithUsername = 1
            validation.username.required = 0
       }
   }

Remarks
^^^^^^^

Please mind, if you use this setting in the edit view, a user is able to change his username.
Therefor you should always take care, via validation settings, that a username (and also the mail address) stays
unique for your users.
