.. include:: ../../Includes.txt
.. include:: Images.txt

.. _showlistusers:

Show and List Frontend Users
----------------------------

This Feature allows you to display the data of the current user, a selected user by an editor or list user and provide
a detail page.

Caution: Please take care, that you do not disclose information in public environments and be careful, which data you show in the detail view.

Show the current user
^^^^^^^^^^^^^^^^^^^^^

Useful, if you want to show a "read only view" for the current logged in
frontend user.

**Configuration:**

#. Add a femanager plugin to your page
#. choose "detail" view
#. select the tab "Detail" and choose "Logged in FE User"

|showlistusers1|

Show a given user
^^^^^^^^^^^^^^^^^

You can provide a list view of all frontend users or frontend users of selected groups

|showlistusers2|

#. Add a femanager plugin to your page
#. choose "detail" view
#. select the tab "Detail" and select your options


.. attention::
   If you add this plugin with the selected view, take care, that you do not disclose information in public environments and be careful, which data you show in the detail view.




List Users and provide a detail page
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Add a femanager plugin to your page
#. choose "list" view
#. select the tab "Detail" and choose a frontend user


.. attention::
   By combining a list aIf you don't select any frontend user, any users can be displayed by passing the get param &tx_femanager_pi1[user]=XX to the detail page url. Be careful to avoid unwanted information disclosure!


|showlistusers3|

Plugin Options:

*  Show Searchfield: You can provide a searchfield, to filter the users
*  Limit: Define, how many users are listed per page
*  Order by: Choose which field should be used to order the list
*  Sorting: Define sort ordering
*  Show from usergroup (empty = show all): Select one or more usergroups. If you don't select a group, all frontend users are displayed
