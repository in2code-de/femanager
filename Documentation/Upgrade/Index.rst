.. include:: ../Includes.rst.txt

.. _upgrade:

Upgrade
=======

.. only:: html

	:ref:`v8.4.1` | :ref:`v8.1` | :ref:`v8.0` | :ref:`v7.1` | :ref:`v6.0` | :ref:`v5.2` | :ref:`v5.0` | :ref:`v4.0` |

.. _v8.4.1:

to version 8.4.1 (security update)
----------------------------------

This is a security release that closes a registration confirmation bypass. Two issues are fixed:

* The admin confirmation action (``status=adminConfirmation``) could be triggered with the regular
  user confirmation ``hash`` when the default settings were used, because the dedicated ``adminHash``
  was only validated when ``confirmAdminConfirmation`` was enabled. A registrant who obtained their
  own user confirmation hash could therefore approve their own account without admin interaction.
* The "Resend Confirmation Mail" action sent the user confirmation email (containing that ``hash``)
  for any submitted address, even on sites that only use admin confirmation.

.. important::

   After updating, run the upgrade wizard
   :guilabel:`Admin Tools > Upgrade > Run Upgrade Wizard > "EXT:femanager: Migrate required confirmation for pending users"`.
   It populates the new field ``fe_users.tx_femanager_confirmation_required`` for accounts that were
   still pending at the time of the update. See "Fallback" below for the behaviour if it is not run.

What changed
~~~~~~~~~~~~

* **adminHash is now mandatory for every admin action.** Admin confirmation, refusal and silent
  refusal always require a valid ``adminHash``, independently of the ``confirmAdminConfirmation``
  setting. The regular user ``hash`` alone is no longer sufficient. This applies both to the
  registration confirmation (``New`` controller) and to the profile change confirmation
  (``Edit`` controller, ``confirmUpdateRequest``).
* **The required confirmation is stored on the user.** During registration femanager now persists
  which confirmations are required (user and/or admin) in the new field
  ``fe_users.tx_femanager_confirmation_required``. The workflow reads this field instead of the
  ambient plugin settings, which the "Resend Confirmation Mail" plugin does not have access to.
* **Resend only resends a pending user confirmation.** The resend action sends the user confirmation
  email only when the account still has an outstanding user confirmation (already confirmed accounts,
  or accounts that only await admin approval, are not resent). To avoid disclosing whether an account
  exists for a given email address, the same neutral message is shown for every valid address -
  regardless of whether a mail was sent, nothing was pending, or no such account exists.

Customized email/confirmation templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you override any of these templates, add the ``adminHash`` argument to all admin action links,
otherwise the links will be rejected as "not authorized":

* :file:`Resources/Private/Templates/Email/CreateAdminConfirmation.html`
* :file:`Resources/Private/Templates/Email/CreateNotify.html`
* :file:`Resources/Private/Templates/Email/UpdateRequest.html`
* :file:`Resources/Private/Templates/New/ConfirmCreateRequest.html` (the ``adminHash`` hidden field
  in the ``confirmAdmin`` and ``confirmAdminRefused`` cases)

Example:

.. code-block:: html

   old: <f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, status:'adminConfirmation'}">
   new: <f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, adminHash:adminHash, status:'adminConfirmation'}">

Fallback if the upgrade wizard is not run
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The fix does not depend on the wizard for its security: for accounts that still have the default
value ``0`` (``none``) - i.e. accounts created before the field existed - the required confirmation
is inferred at runtime from the confirmation state, mirroring the wizard:

* disabled, confirmed by user, not by admin → admin confirmation is still required
* disabled, confirmed by neither → both confirmations are required (an admin can always release the
  account from the backend)
* already confirmed by admin, or enabled → no confirmation pending

As a result the registration workflow and the resend action behave correctly even without the wizard.
Running the wizard is still recommended: it persists the precise requirement so it is shown and
filterable in the backend and the runtime fallback is no longer needed.

.. warning::

   There is no way to find out retroactively whether an existing, still pending account originally
   required an admin confirmation or not. For the ambiguous case (a disabled account that has been
   confirmed by neither the user nor an admin) both the wizard and the runtime fallback therefore
   choose the safe option and require an admin confirmation. On a site that only uses user
   confirmation this means such legacy accounts now additionally wait for an admin, even though no
   admin confirmation was originally intended. These accounts are not lost: an administrator can
   release them at any time via :guilabel:`Web > Frontend Users` (femanager backend module). Only
   accounts that were already pending at the time of the update are affected; accounts created
   afterwards store their exact requirement and are never over-restricted.

.. _v8.1:

to version 8.1.0
----------------

Deprecations


Confirmation links
~~~~~~~~~~~~~~~~~~

New TypoScript options are added for this:
new.email.createUserConfirmation.confirmUserConfirmation
When enabled, a user confirming their profile creation via the email link will show a confirmation dialog beforehand. This prevents unexpected execution f.e. via Virus Scanners
new.email.createUserConfirmation.confirmAdminConfirmation
When enabled, an admin confirming/refusing/silently refusing someones profile creation via the email link will show a confirmation dialog beforehand
This PR also includes adjustments to HashUtility allowing to specify more data while hashing a user, so that another hash can be created to prevent meddling with the admin actions (which by default is already prevented using cHashes).

For new.email.createUserConfirmation.confirmAdminConfirmation, this technically is a breaking change as template
adjustments were required to pass the admin hash to the email links. If you have customized templates, please add the
variable "adminHash:adminHash" to all action links in Resources/Private/Templates/Email/CreateAdminConfirmation.html

Example:
old:		<f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, status:'adminConfirmation'}">
new		<f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, adminHash:adminHash, status:'adminConfirmation'}">

These settings are getting the default settings for V14! This change applies also for V13!

.. _v8.0:

to version 8.0.0
----------------

Upgrade Plugins
~~~~~~~~~~~~~~~

All Plugins used in your pages **must** be updated.
The Updater is located in :guilabel:`Admin Tools > Upgrade > Run Upgrade Wizard > "EXT:femanager: Migrate plugins"`

TypoScript Changes
~~~~~~~~~~~~~~~~~~

If you use the uploading feature for avatar images, the configuration has to be changed to the combined identifier

**Constants:**

.. code-block:: typoscript

   plugin.tx_femanager {
      settings {
         uploadFolder = 1:users/
      }
   }

DataProcessors
~~~~~~~~~~~~~~

The DataProcessors configured under plugin.tx_femanager.settings.dataProcessors are now without a return value, if you need to change data in the request use PSR-15 Middlewares

Autologin
~~~~~~~~~

Currently the AutoLogin feature does not work. When we have found a solution the feature will be enabled again, until then a link to the login page could be inserted.

.. _v7.1:

to version 7.1.0
----------------

**Invitation Template**

If you are using customized templates for the invitation function, please check the Resources/Private/Templates/Invitation/Edit.html

You need to add

.. code-block:: html

   <f:form.hidden name="hash" value="{hash}"/>

in order,that the function is working.


.. _v6.0:

to version 6.0.0
----------------

Version 6.0 support TYPO3 9 LTS and 10 LTS. The support for TYPO3 8 was dropped. No changes on Templates are needed.

**Backend Module "Frontend User" - View User Confirmation**

If you want to use the Backend Module to confirm or refuse Frontend User, you need to setup the configPID. The extension
uses now a frontend call out of the backend, to organise these actions.

.. code-block:: typoscript

    module.tx_femanager {
        settings {
            configPID = 1
        }
    }


.. _v5.2:

to version 5.2.0
----------------

The edit template has to be adjusted, as there is a new parameter 'token' is introduced.

Please update these partials:

:file:`/Partials/Misc/DeleteLink.html`

The argument token was introduced.

**old:**

.. code-block:: html

      <f:link.action
         action="delete"
         arguments="{user:user}"
         class="btn btn-warning btn-large"
         additionalAttributes="{data-confirm:'{f:translate(key:\'UserDeleteConfirmation\')}'}">
         <i class="icon-trash icon-white"></i>
         <f:translate key="deleteProfile" />
      </f:link.action>

**new:**

.. code-block:: html

      <f:link.action
         action="delete"
         arguments="{user:user, token:token}"
         class="btn btn-warning btn-large"
         additionalAttributes="{data-confirm:'{f:translate(key:\'UserDeleteConfirmation\')}'}">
         <i class="icon-trash icon-white"></i>
         <f:translate key="deleteProfile" />
      </f:link.action>

:file:`/Templates/Edit/Edit.html`

You need to add: :html:`<f:form.hidden name="token" value="{token}" />` between the form tag.

Example:

.. code-block:: html

	<f:form
		name="user"
		object="{user}"
		action="update"
		enctype="multipart/form-data"
		additionalAttributes="{data-femanager-plugin:data.uid}"
		class="form-horizontal {f:if(condition:'{settings.edit.validation._enable.client}',then:'feManagerValidation',else:'')}">
	<fieldset>
		<legend>
			<f:translate key="titleUpdateProfile" />
		</legend>

		<f:form.hidden name="token" value="{token}" />

		more stuff here in the template file…

	</f:form>

.. _v5.0:

to version 5
------------

There are minor breaking changes include. Main change is, that all eid scripts were replace, by a page num approach.

In order that the js validation works, you need to take care, that you these page typenums are available:


1. Backend Module: Login as User feature

::

    feManagerLoginAs.typeNum = 1548943013

see the complete config in file ext_typoscript_setup.txt

2. Frontend Validation via JS

::

    feManagerLoginAs.typeNum = 1548935210

see the complete config in file Configuration/TypoScript/setup.ext

.. _v4.0:

to version 4.2.3 / 4.2.4 / 4.2.5
--------------------------------

If you use your own HTML templates of new/edit/invitation-templates you should compare them with the one from
EXT:femanager. There is a new additional attribute inside the form viewhelper: data-femanager-plugin, which contains
the content element id.

If you use a modified version of the Validation.js, there are also changes: plugin and action parameter is send to
the eID-Script now
