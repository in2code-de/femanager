.. include:: ../Includes.txt
.. include:: Images.txt

.. _introduction:

Introduction
============

.. only:: html

	:ref:`what` | :ref:`requirements` | :ref:`suggests` | :ref:`features` | :ref:`screenshots` |


.. _what:

What does it do?
----------------

Femanager is an extension for a TYPO3 Frontend-User Registration.
Maybe you know sr_feuser_register but you want to use a more modern extension, give femanager a try.

This extension basicly brings an easy-to-use frontend-user-registration with a profile manager to your system.
In addition femanager was developed to be very flexible and to bring a lot of features out of the box.

Note: Login and a *I forgot my password* function is part of the core and not part of femanager.


.. _requirements:

Requirements of femanager
-------------------------

- TYPO3 8.7
- Extbase / Fluid
- Best use with jQuery and bootstrap


.. _suggests:

Suggestions
-----------

- static_info_tables (to show localized countryselection)
- sr_freecap (captcha for a spam prevention)


.. _features:

Features
--------

Frontend-User Registration
^^^^^^^^^^^^^^^^^^^^^^^^^^

- One step registration with autologin
- Main configuration with Flexform
- User confirmation (Double-Opt In) (optional)
- Administration confirmation (optional)
- Refuse and Silent Refuse
- Fill email field with username (optional)
- Redirect with TypoScript standardWrap (optional)
- Prefill Formfields via TypoScript standardWrap (optional)
- Multiple Validation Possibilities (JavaScript and PHP) (required, email, min, max, intOnly, lettersOnly, uniqueInPage, uniqueInDb, mustInclude(number,letter,special), inList(1,2,3))
- Same PHP Methods for JavaScript and PHP Validation
- Simply extend validation methods with your extension
- Override a lot of Email settings with TypoScript if needed
- Set mail attachments or embeded images
- Override field values on every single step (e.g. push user to usergroup1 and if he is ready confimed push him to usergroup2)
- Send user values to a third party software (e.g. a CRM like salesforce)
- Store values in other database tables (e.g. tt_address)
- Add Captcha (sr_freecap) for spam prevention


Edit Profile
^^^^^^^^^^^^

- Main configuration with Flexform
- Administration confirmation for change request (optional)
- Refuse and Silent Refuse
- Fill email field with username (optional)
- Prefill Formfields via TypoScript standardWrap (optional)
- Multiple Validation Possibilities (JavaScript and PHP) (required, email, min, max, intOnly, lettersOnly, uniqueInPage, uniqueInDb, mustInclude(number,letter,special), inList(1,2,3))
- Same PHP Methods for JavaScript and PHP Validation
- Simply extend validation methods with your extension
- Override a lot of Email settings with TypoScript if needed
- Set mail attachments or embeded images
- Delete profile with TypoScript redirect


Invitation
^^^^^^^^^^

- Admin could create a new User in Frontend
- The new user receives a mail with a secured link, which leads to a password generation form
- Same validations as in edit and new
- A lot of configuration possibilities with TypoScript


Backend Module
^^^^^^^^^^^^^^

- Fulltext search for fe_users
- Hide and delete of fe_users via AJAX
- Shows Login status
- Logout of a frontend user


General
^^^^^^^

- Logging of every change
- Saltedpasswords support
- List FE-Users in the frontend
- Show a user profile in frontend
- jQuery include must activate via constants (per default no extra jQuery inclusion)
- Show fe_user crdate and tstamp for editors
- Store values in other database tables (e.g. tt_address)
- Save password as md5 or sha1 per default
- HTML with twitter bootstrap classes to reduce integration time
- Image upload with status bar
- Supports static_info_tables
- Extend this extension with new validators or new fields in fe_users (see example in best practice section) or use some SignalSlots
- Extension uses namespaces (so TYPO3 version 6.0 or higher is needed)


.. _screenshots:

Screenshots
-----------

Frontend: Registration
^^^^^^^^^^^^^^^^^^^^^^

|create1|

TYPO3 Frontend User Registration


Frontend: Email
^^^^^^^^^^^^^^^

|create2|

Profile must be confirmed from Admin


Frontend: Validation
^^^^^^^^^^^^^^^^^^^^

|create3|

Client- and serverside Validators can be configured


Frontend: Profile Update
^^^^^^^^^^^^^^^^^^^^^^^^

|edit1|

Request for Profile Update


Frontend: Add new fields
^^^^^^^^^^^^^^^^^^^^^^^^

|edit2|

Extend Femanager (fe_users) with new Fields


Backend: Pluginview for Editors
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|backend1|

Example settings in Plugin


Backend: FE-Users record
^^^^^^^^^^^^^^^^^^^^^^^^

|backend2|

FE-Users record will be extended with useful information


Backend: Manage Frontend-Users
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|backend3|

A small backend module helps to manage the Frontend-Users
