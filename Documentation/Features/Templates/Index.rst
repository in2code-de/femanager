.. include:: ../../Includes.rst.txt

.. _changetemplates:

Use own HTML Templates
----------------------

Basics
^^^^^^

If you want to modify a HTML-File of femanager, you should not overwrite them directly in the extension folder.
Think about upcoming versions with important bugfixes or security-patches.

There are two ways to use own HTML-Templates (and Partials / Layouts) instead of the original Templates.

Replace all HTML Templates from Femanager with own Templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can copy all Files from
- EXT:femanager/Resources/Private/Templates/
- EXT:femanager/Resources/Private/Partials/
- EXT:femanager/Resources/Private/Layouts/

to a new folder in fileadmin - e.g. fileadmin/templates/femanager/ and modify them as you want.
After that, you should say femanager to use the new Templates with some lines of TypoScript setup:

.. code-block:: typoscript

	plugin.tx_femanager {
		view {
			templateRootPath = fileadmin/templates/femanager/Templates/
			partialRootPath = fileadmin/templates/femanager/Partials/
			layoutRootPath = fileadmin/templates/femanager/Layouts/
		}
	}

Replace single HTML Template-Files
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can copy only the Files that you want to modify from
- EXT:femanager/Resources/Private/Templates/
- EXT:femanager/Resources/Private/Partials/
- EXT:femanager/Resources/Private/Layouts/

to a new folder in fileadmin - e.g. fileadmin/templates/femanager/ and modify them as you want.
After that, you should say femanager to use the old folders and merge them with the new folders

.. code-block:: typoscript

	plugin.tx_femanager {
		view {
			templateRootPath >
			templateRootPaths {
				10 = EXT:femanager/Resources/Private/Templates/
				20 = fileadmin/templates/femanager/Templates/
			}
			partialRootPath >
			partialRootPaths {
				10 = EXT:femanager/Resources/Private/Partials/
				20 = fileadmin/templates/femanager/Partials/
			}
			layoutRootPath >
			layoutRootPaths {
				10 = EXT:femanager/Resources/Private/Layouts/
				20 = fileadmin/templates/femanager/Layouts/
			}
		}
	}

Email templates with useFluidMail
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If ``useFluidMail`` is enabled in the extension configuration, femanager sends all emails via the TYPO3 ``FluidEmail``
API. The templates are then taken from the folder ``Mail/`` instead of ``Email/``:

- EXT:femanager/Resources/Private/Templates/Mail/ (``.html`` and ``.txt`` for the plain text part)
- EXT:femanager/Resources/Private/Partials/Mail/
- EXT:femanager/Resources/Private/Layouts/Mail/

You can override them in two ways:

1. Via TypoScript (recommended). These paths are added after the paths of
   ``$GLOBALS['TYPO3_CONF_VARS']['MAIL']`` and always take precedence over them. Use the constants
   ``plugin.tx_femanager.settings.email.templateRootPath`` (``partialRootPath``, ``layoutRootPath``) or the setup.
   The configuration follows the email template configuration of the TYPO3 core extension felogin
   (``plugin.tx_felogin_login.settings.email``). In contrast to felogin, the TypoScript paths always
   take precedence over the paths in ``$GLOBALS['TYPO3_CONF_VARS']['MAIL']``, regardless of their key:

.. code-block:: typoscript

	plugin.tx_femanager.settings.email {
		templateRootPaths {
			20 = EXT:sitepackage/Resources/Private/Extensions/Femanager/Templates/Mail/
		}
		partialRootPaths {
			20 = EXT:sitepackage/Resources/Private/Extensions/Femanager/Partials/Mail/
		}
		layoutRootPaths {
			20 = EXT:sitepackage/Resources/Private/Extensions/Femanager/Layouts/Mail/
		}
	}

2. Via the global mail configuration in the ``ext_localconf.php`` of your sitepackage. Use a key higher than
   ``1408`` (the key femanager uses), so that your paths take precedence:

.. code-block:: php

	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1500] = 'EXT:sitepackage/Resources/Private/Extensions/Femanager/Templates/Mail/';
	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'][1500] = 'EXT:sitepackage/Resources/Private/Extensions/Femanager/Partials/Mail/';
	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][1500] = 'EXT:sitepackage/Resources/Private/Extensions/Femanager/Layouts/Mail/';

The paths in ``plugin.tx_femanager.view`` are not used for emails if ``useFluidMail`` is enabled.

The femanager mail templates use the layout ``Default`` (EXT:femanager/Resources/Private/Layouts/Mail/Default.html),
which only renders the section ``main`` without any additional markup. If you want to use the TYPO3 system mail
design, override the template and use the layout ``SystemEmail`` of the TYPO3 core with the sections ``Title``
(optional) and ``Main``:

.. code-block:: html

	<f:layout name="SystemEmail" />

	<f:section name="Title">Please confirm your registration</f:section>

	<f:section name="Main">
		...
	</f:section>
