.. include:: ../Includes.txt
.. include:: Images.txt

.. _upgrade:

Upgrade
=======

.. only:: html

	:ref:`v6.4.4` | :ref:`v6.3.4` | :ref:`v6.0` |:ref:`v5.2` | :ref:`v5.0` | :ref:`v4.0`

.. _v6.4.4:

to version 6.4.4 (security update)
----------------------------------

This security release closes multiple security issues:

----

Privilege escalation:
~~~~~~~~~~~

Closes a privilege escalation in the frontend usergroup selection.

The registration, edit and invitation forms can render a usergroup ``<select>``. The frontend
template and the rendered dropdown were treated as the only restriction on which usergroup a user
could choose. They are **not** a security boundary: a crafted request can submit any usergroup uid,
regardless of what the form offers. A logged-in frontend user could therefore assign **any**
frontend usergroup - including privileged ones - to their own account.

What changed
"""""""""""

The submitted usergroup relation is now validated on the server before the user is persisted, in a
single place (``UserGroupSanitizationService``). The form is **secure by default / fail closed**:

* **Forced groups** - if ``settings.<form>.overrideUserGroup`` is set, the configured group(s)
  always win and the submitted value is ignored (unchanged behaviour).
* **Field not editable** - if ``usergroup`` is not part of the configured ``fields``, any submitted
  usergroup change is reverted.
* **Allowlist** - if ``settings.<form>.validation.usergroup.inList`` is set, the submitted uids are
  reduced to that list. This is the recommended way to let users choose a group.
* **Opt-in for unrestricted selection** - if no allowlist is configured but
  ``settings.<form>.misc.allowUnrestrictedUserGroupSelection = 1`` is set, every offered group may
  be selected (legacy behaviour).
* **Fail closed** - if neither an allowlist nor the opt-in is configured, the submitted usergroup
  change is **ignored** and a log entry (``Profile update not authorized``) is written.

Every reverted or reduced submission is logged so unexpected usergroup changes become visible.

.. important::

   Installations that previously relied on an **unconfigured** usergroup selection (no
   ``validation.usergroup.inList``) will no longer accept user-submitted usergroups by default. This
   is intentional. To keep offering a usergroup selection, do **one** of the following per form
   (``new``, ``edit``, ``invitation``):

   * Configure an allowlist (recommended):

     .. code-block:: typoscript

        plugin.tx_femanager.settings.edit.validation.usergroup.inList = 1,2,3

   * Or explicitly restore the former, unrestricted behaviour:

     .. code-block:: typoscript

        plugin.tx_femanager.settings.edit.misc.allowUnrestrictedUserGroupSelection = 1

Customized usergroup templates
""""""""""""""""""""""""""""""

If you override :file:`Resources/Private/Partials/Fields/Usergroup.html`, note that the field is now
rendered depending on the new ``usergroupFieldMode`` variable
(``select`` / ``hidden`` / ``notice``). When neither an allowlist nor the opt-in is configured, a
generic notice (``usergroupSelectionNotConfigured``) is shown instead of the selection; the specific
missing configuration is **not** exposed in the frontend but written to the TYPO3 log. Compare your
template with the shipped partial to pick up this behaviour.

See :ref:`usergroupsecurity` for the full description of the feature.

----

Registration confirmation bypass:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Closes a registration confirmation bypass. Two issues are fixed:

* The admin confirmation action (``status=adminConfirmation``) could be triggered with the regular
  user confirmation ``hash``, because this version had no dedicated admin hash at all. A registrant
  who obtained their own user confirmation hash could therefore approve their own account without
  admin interaction.
* The "Resend Confirmation Mail" action sent the user confirmation email (containing that ``hash``)
  for any submitted address, even on sites that only use admin confirmation.

.. important::

   After updating, run the upgrade wizard
   :guilabel:`Admin Tools > Upgrade > Run Upgrade Wizard > "EXT:femanager: Migrate required confirmation for pending users"`.
   It populates the new field ``fe_users.tx_femanager_confirmation_required`` for accounts that were
   still pending at the time of the update. See "Fallback" below for the behaviour if it is not run.

What changed
""""""""""""

* **A dedicated adminHash is now mandatory for every admin action.** ``HashUtility`` can now derive a
  separate ``admin`` hash for a user, and admin confirmation, refusal and silent refusal always
  require a valid ``adminHash``. The regular user ``hash`` alone is no longer sufficient. This applies
  both to the registration confirmation (``New`` controller) and to the profile change confirmation
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

Customized email templates
""""""""""""""""""""""""""

If you override any of these templates, add the ``adminHash`` argument to all admin action links,
otherwise the links will be rejected as "not authorized":

* :file:`Resources/Private/Templates/Email/CreateAdminConfirmation.html`
* :file:`Resources/Private/Templates/Email/CreateNotify.html`
* :file:`Resources/Private/Templates/Email/UpdateRequest.html`

Example:

.. code-block:: html

   old: <f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, status:'adminConfirmation'}">
   new: <f:link.action action="confirmCreateRequest" controller="New" absolute="1" arguments="{user:user, hash:hash, adminHash:adminHash, status:'adminConfirmation'}">

Fallback if the upgrade wizard is not run
"""""""""""""""""""""""""""""""""""""""""

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

----

.. _v6.3.4:

to version 6.3.4
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

.. code-block:: text

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

The argument token was introduced :html:`(token:token)`

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
