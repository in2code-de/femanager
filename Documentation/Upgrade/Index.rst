.. include:: ../Includes.txt
.. include:: Images.txt

.. _upgrade:

Upgrade
=======

.. only:: html

	:ref:`v4` | :ref:`v5` | :ref:`v5.2.0`


.. _v5.2.0:

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

.. _v5:

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

.. _v4:

to version 4.2.3 / 4.2.4 / 4.2.5
--------------------------------

If you use your own HTML templates of new/edit/invitation-templates you should compare them with the one from
EXT:femanager. There is a new additional attribute inside the form viewhelper: data-femanager-plugin, which contains
the content element id.

If you use a modified version of the Validation.js, there are also changes: plugin and action parameter is send to
the eID-Script now
