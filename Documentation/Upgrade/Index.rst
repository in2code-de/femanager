.. include:: ../Includes.txt
.. include:: Images.txt

.. _installation:

Upgrade
=======

.. only:: html

	:ref:`guide` |


.. _guide:

to version 5
------------

There are no big breaking changes include. Main change is, that all eid scripts were replace, by a page num approach.

In order that the js validation works, you need to take care, that you these page typenums are available:


1. Backend Module: Login as User feature

::
feManagerLoginAs.typeNum = 1548943013
::

see the complete config in file ext_typoscript_setup.txt

2. Frontend Validation via JS

::
feManagerLoginAs.typeNum = 1548935210
::

see the complete config in file Configuration/TypoScript/setup.ext
