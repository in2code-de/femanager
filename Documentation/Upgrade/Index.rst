.. include:: ../Includes.txt
.. include:: Images.txt

.. _upgrade:

Upgrade
=======

.. only:: html

	:ref:`v4` | :ref:`v5`


.. _v4:

to version 4.2.3 / 4.2.4 / 4.2.5
--------------------------------

If you use your own HTML templates of new/edit/invitation-templates you should compare them with the one from
EXT:femanager. There is a new additional attribute inside the form viewhelper: data-femanager-plugin, which contains
the content element id.

If you use a modified version of the Validation.js, there are also changes: plugin and action parameter is send to
the eID-Script now

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
