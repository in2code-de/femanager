.. include:: ../../Includes.txt


.. _resendUserConfirmationRequest:

Resend User Confirmation Request
--------------------------------

Introduction
^^^^^^^^^^^^

**Available since 4.2.0**

This feature adds a new view in the backend module to list user, which did not confirm their user accounts. An frontend
usera dmin is now able to resend the confirmation mail or decline (and delete) the user via backend.


Configuration
^^^^^^^^^^^^^

To activate the feature add the userTSConfig:

::

   tx_femanager.UserBackend.confirmation.ResendUserConfirmationRequest = 1
