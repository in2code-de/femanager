.. include:: ../../Includes.rst.txt

.. _usergroupsecurity:

Securing the usergroup selection
--------------------------------

Why this matters
^^^^^^^^^^^^^^^^^

The registration-, edit- and invitation forms can render a usergroup selection. The frontend
template and the rendered ``<select>`` are **not** a security boundary: a crafted request can
submit any usergroup uid, regardless of what the dropdown offers. Without a server-side allowlist
a logged-in frontend user could therefore assign **any** frontend usergroup to their own account
(privilege escalation).

femanager enforces the allowed usergroups on the server. The following rules apply to the ``new``,
``edit`` and ``invitation`` forms.

How femanager decides which usergroups are allowed
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The submitted usergroup relation is checked before the user is persisted:

#. **Forced groups** – if ``settings.<form>.overrideUserGroup`` is set, the configured group(s)
   always win and the submitted value is ignored.
#. **Field not editable** – if the usergroup field is not part of the selected ``fields``, any
   submitted usergroup change is reverted.
#. **Allowlist** – if ``settings.<form>.validation.usergroup.inList`` is set, the submitted uids are
   reduced to that list. This is the recommended way to let users choose a group.
#. **Opt-in for unrestricted selection** – if no allowlist is configured but
   ``settings.<form>.misc.allowUnrestrictedUserGroupSelection = 1`` is set, every offered group may
   be selected (legacy behaviour).
#. **Fail closed** – if neither an allowlist nor the opt-in is configured, the submitted usergroup
   change is **ignored** and a log entry (``Profile update not authorized``) is written.

.. warning::
   If you want frontend users to choose their usergroup, you **must** configure an allowlist with
   ``validation.usergroup.inList``. Otherwise the selection is rejected. Only enable
   ``allowUnrestrictedUserGroupSelection`` if you really want users to be able to pick **any**
   frontend usergroup that the form offers.

Recommended configuration (allowlist)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: typoscript

   plugin.tx_femanager.settings.edit.validation.usergroup.inList = 1,2,3

The same setting exists for the ``new`` and ``invitation`` forms.

Behaviour in the form
^^^^^^^^^^^^^^^^^^^^^^

The usergroup field reacts to the configuration:

- An allowlist or the opt-in is configured: the selection is rendered.
- ``overrideUserGroup`` is configured: the field is hidden (the group is assigned automatically).
- Nothing is configured: a generic notice ("please contact the administrator") is shown instead of
  the field. The specific missing configuration is **not** exposed in the frontend; instead a
  warning with the details is written to the TYPO3 log.

Upgrade note
^^^^^^^^^^^^

Installations that previously relied on an unconfigured usergroup selection (no
``validation.usergroup.inList``) will no longer accept user-submitted usergroups by default. Add an
allowlist via ``validation.usergroup.inList`` or set
``misc.allowUnrestrictedUserGroupSelection = 1`` to restore the former behaviour.
