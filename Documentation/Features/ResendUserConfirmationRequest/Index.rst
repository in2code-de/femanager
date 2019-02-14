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

|plugin3|

Sometimes unconfirmed users are trying to trigger the confirmation mail again by registering again with the same email or username. They get an error "username / email already existing". Since 4.2, an additional message with a "resend confirmation mail" link is displayed in those cases where the existing user has not yet confirmed his registration. 

For this message to be displayed, you need to have the following typoscript setting configured:

::

    settings.showResendUserConfirmationRequestView = {your pid}

The pid is the page uid where your "resend confirmation mail" plugin resides.


Backend View
^^^^^^^^^^^^

Lists all frontend users, which did not confirm their email so far. An admin is able, to decline (delete) users or
resend an email with a confirmation link.

|backend4|

To activate the feature add the userTSConfig:

::

    tx_femanager.UserBackend.confirmation.ResendUserConfirmationRequest = 1
