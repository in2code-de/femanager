.. include:: ../../Includes.rst.txt
.. include:: Images.txt

.. _showlistusers:

Show and List Frontend Users
----------------------------

This Feature allows you to display the data of the current user, a selected user by an editor or list users and provide
a detail page.

Caution: Please take care that you do not disclose information in public environments and be careful which data you show in the detail view.

Show the current user
^^^^^^^^^^^^^^^^^^^^^

Useful, if you want to show a "read only view" for the currently logged in frontend user.

**Configuration:**

#. Add a femanager_detail plugin to your page
#. in the field "User to show" choose "Logged in FE User"

|showlistusers1|

Show a given user
^^^^^^^^^^^^^^^^^

You can provide a detail view of a given frontend user

|showlistusers2|

#. Add a femanager_detail plugin to your page
#. select the user to be shown in the field "User to show"


.. attention::
   If you add this plugin with the selected view, take care that you do not disclose information in public environments and be careful which data you show in the detail view.
   If you don't select any frontend user, any users can be displayed by passing the get param &tx_femanager_pi1[user]=XX to the detail page url. Be careful to avoid unwanted information disclosure!



List Users
^^^^^^^^^^^

#. Add a a femanager_list plugin to your page
#. set the plugin options to show the users you want to display


|showlistusers3|

Plugin Options:

*  Show Searchfield: You can provide a searchfield, to filter the users
*  Limit: Define how many users are listed per page
*  Order by: Choose which field should be used to order the list
*  Sorting: Define sort ordering
*  Show from usergroup (empty = show all): Select one or more usergroups. If you don't select a group, all frontend users are displayed
