.. include:: ../Includes.txt
.. include:: Images.txt

.. _upgrade:

Upgrade
=======

.. only:: html

	:ref:`v6.3.4` | :ref:`v6.0` |:ref:`v5.2` | :ref:`v5.0` | :ref:`v4.0`

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
