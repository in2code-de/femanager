.. include:: ../../Includes.txt
.. include:: Images.txt


.. _resendUserConfirmationRequest:

Resend User Confirmation Request
--------------------------------

Introduction
^^^^^^^^^^^^

**Available since 4.2.0**

This feature adds a new view in the backend module to list user, which did not confirm their user accounts. An frontend
user admin is now able to resend the confirmation mail or decline (and delete) the user via backend.


Frontend View
^^^^^^^^^^^^^

An editor can setup a new view "resend confirmation mail". Frontend users, who started their registration process and did
not confirm their email so far, can resend a confirmation mail.

Configuration. Add the plugin "Femanager" to a page and select "resend confirmation mail".

IMPORTANT: If you want to use these new views and you did use femanager version 4.1 or older, you need open existing plugins and save them again, in order to allow the
usage of this views.

|femanager_plugin3|

Sometimes users try to register again with the same email in order to trigger the confirmation mail again. They get an error message saying that the username/email is alredy existing (if it is also confirmed or not is not taken into account).

To to make sure the users don't get stuck at this point, an additional message is displayed if the existing user has not yet confirmed his registration. For this message to be displayed in that case, you need to have the following typoscript setting configured with the pid where your "resend confirmation mail" plugin resides.

::
settings.showResendUserConfirmationRequestView = {your pid}
::



Backend View
^^^^^^^^^^^^

Lists all frontend users, which did not confirm their email so far. An admin is able, to decline (delete) users or
resend an email with a confirmation link.

|backend3|

To activate the feature add the userTSConfig:

::

   tx_femanager.UserBackend.confirmation.ResendUserConfirmationRequest = 1

