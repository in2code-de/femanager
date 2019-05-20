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
      5.0.1 (!!!)
   :Date:
      2019-01-12
   :Changes:

      * Task: Unit test update
      * Task: Remove deprecated keys in ext_emconf.php
      * !!! Bugfix: Make JavaScript work again in BE-Module in TYPO3 9.5 - Path of all JavaScript files changes from Resources/Public/JavaScripts/ to Resources/Public/JavaScript/ - maybe you have to adjust your TypoScript
      * Bugfix: Allow default values directly in PrefillFieldViewHelper
      * Bugfix: No mails are sent if database storing was disabled with the disclaimer feature
      * Bugfix: Remove outdated eID inclusion
      * Bugfix: Show only allowed froms in plugin (in TYPO3 9.5)

 - :Version:
      5.0.0 (!!!)
   :Date:
      2018-10-25
   :Changes:

      * Feature: Support for TYPO3 version 9
      * Bugfix: Change feManagerLoginAs Feature to typeNum
      * Bugfix: Change Frontend Validation from eid script to typeNum

Pls look at https://github.com/in2code-de/femanager for a changelog for older versions

Older versions of femanager, even those which are probably not downloadable through TER, are available on github:
https://github.com/in2code-de/femanager/releases
