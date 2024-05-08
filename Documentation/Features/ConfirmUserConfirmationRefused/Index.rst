.. include:: ../../Includes.txt


.. _ConfirmUserConfirmationRefused:

ConfirmUser Confirmation Refused
-----------------------

Introduction
^^^^^^^^^^^^

**Available since 6.4.0**

Some virus scanners are preloading all links in an email.
If this happens in an user confirmation mail, the user is deleted automatically.
This feature can prevent this behavior.

Configuration
^^^^^^^^^^^^^

This feature can be enabled via typoscript.automatically

The default value is 0, so this feature has to be enabled.

::

   plugin.tx_femanager.settings {
   new {
      email.createUserConfirmation.confirmUserConfirmationRefused = 1
      }
   }


