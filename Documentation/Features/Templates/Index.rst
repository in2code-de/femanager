.. include:: ../../Includes.txt

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

.. code-block:: text

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

.. code-block:: text

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
