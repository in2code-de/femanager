.. include:: ../../Includes.txt
.. include:: Images.txt


.. _resendUserConfirmationRequest:

Resend User Confirmation Request
--------------------------------

Introduction
^^^^^^^^^^^^

**Available since 4.2.0**

This feature adds a new view in the backend module to list user, which did not confirm their user accounts. An frontend
usera admin is now able to resend the confirmation mail or decline (and delete) the user via backend.


Frontend View
^^^^^^^^^^^^^

An editor can setup a new view "resend confirmation mail". Frontend users, who started their registration process and did
not confirm their email so far, can resend a confirmation mail.

Configuration. Add the plugin "Femanager" to a page and select "resend confirmation mail".

|femanager_plugin3|



Backend View
^^^^^^^^^^^^

Lists all frontend users, which did not confirm their email so far. An admin is able, to decline (delete) users or
resend an email with a confirmation link.

|backend3|

To activate the feature add the userTSConfig:

::

   tx_femanager.UserBackend.confirmation.ResendUserConfirmationRequest = 1

