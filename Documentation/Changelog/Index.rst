.. include:: ../Includes.txt


Changelog
=========

All changes are documented on https://docs.typo3.org/typo3cms/extensions/femanager/Changelog/


.. t3-field-list-table::
 :header-rows: 1

 - :Version:
      Version
   :Date:
      Release Date
   :Changes:
      Release Description

 - :Version:
      5.2.0 (!!!)
   :Date:
      2019-11-26
   :Changes:
      * !!![BUGFIX] Allow password hashing none for TYPO3 V9

 - :Version:
      5.1.1
   :Date:
      2019-05-26
   :Changes:
      * [BUGFIX] Allow password hashing none for TYPO3 V9

 - :Version:
      Version
   :Date:
      Release Date
   :Changes:
      Release Description

 - :Version:
      5.1.0
   :Date:
      2019-05-21
   :Changes:
      * [FEATURE] Allow TYPO3 Version 8, allow PHP 7.3
      * [BUGFIX] Allow password hashing none for TYPO3 V9
      * [BUGFIX] Invitation: Peform expected redirect (thanks to Footrotflat)
      * [BUGFIX] Use HashFactory of V9 and fallback for V8
      * [BUGFIX] use hash password methods (thanks to Lex Frolenko)
      * [BUGFIX] Fix JS validation URL (thanks to Daniel Haupt)
      * [BUGFIX] Use doctrine contraints on pluginRespository (thanks to netcoop)
      * [BUGFIX] use doctrine for UserUtility queries
      * [BUGFIX] Update russian lang label
      * [BUGFIX] Update GetFirstViewHelper (thanks to Oliver Beck)
      * [BUGFIX] Fix translation of field names in form errors (thanks to Daniel Haupt)
      * [TASK] Add signal beforeSend (thanks to Michael Bakonyi)
      * [TASK] Updated 5.0.0 release date (thanks to Stephan Großberndt)
      * [TASK] Consistent naming for ConnectionPool method (thanks to Stephan Großberndt)
      * [TASK] Fix a small typo in FAQ (thanks to Thomas Deuling)
      * [DOCS] Add migration notes
      * [DOCS] Fix multiple errors and warnings (thanks to Daniel Haupt)
      * [DOCS] Add detailed changelog

 - :Version:
      5.0.0 (!!!)
   :Date:
      2019-02-01
   :Changes:

      * Feature: Support for TYPO3 version 9
      * Bugfix: Change feManagerLoginAs Feature to typeNum
      * Bugfix: Change Frontend Validation from eid script to typeNum

Pls look at https://github.com/in2code-de/femanager for a changelog for older versions

Older versions of femanager, even those which are probably not downloadable through TER, are available on github:
https://github.com/in2code-de/femanager/releases
