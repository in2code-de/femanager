.. include:: ../../Includes.rst.txt


.. _loginAs:

Login as Frontend User (Impersonate)
-------------------------------------

Introduction
^^^^^^^^^^^^

The "Login as" feature allows backend administrators to impersonate a frontend user directly from the
femanager backend module. Clicking the "Login as" button opens a new browser tab where the administrator
is logged in as the selected frontend user. This is useful for debugging, testing or supporting users
without knowing their password.

The button is only available for users who are **offline** and **not disabled**. For users who are
currently online or disabled, the button is shown in a disabled state.

.. attention::
   This feature is restricted to backend administrators only. Non-admin backend users cannot use
   this feature even if it is enabled.


Configuration
^^^^^^^^^^^^^

The feature is disabled by default and must be enabled via UserTSConfig:

.. code-block:: typoscript

   tx_femanager.UserBackend.enableLoginAs = 1

A default configuration file is shipped with the extension at
:file:`Configuration/UserTsConfig/BackendModule.typoscript` which can be included in your
site configuration or UserTSConfig.


Redirect Page
^^^^^^^^^^^^^

By default, the administrator is redirected to the page configured in ``module.tx_femanager.settings.configPID``
after the impersonation login. You can customize the redirect target via TypoScript:

.. code-block:: typoscript

   plugin.tx_femanager.settings.loginAs {
       redirect = TEXT
       redirect {
           typolink {
               parameter = 42
               returnLast = url
           }
       }
   }


Events
^^^^^^

The ``ImpersonateEvent`` is dispatched when a backend administrator logs in as a frontend user. This can
be used to log or audit impersonation actions.

See :ref:`Events <events>` for more details.
