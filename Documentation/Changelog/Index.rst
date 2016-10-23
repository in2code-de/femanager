.. include:: ../Includes.txt

.. _changelog:

Changelog
=========

Introduction
------------

Older versions of femanager, even those which are probably not downloadable through TER, are available at
git://git.typo3.org/TYPO3CMS/Extensions/femanager.git or
https://forge.typo3.org/projects/extension-femanager/repository

Changes
-------

.. t3-field-list-table::
 :header-rows: 1

 - :Version:
      Version
   :State:
      State
   :Date:
      Date
   :Changes:
      Changes

 - :Version:
      2.6.0
   :State:
      stable
   :Date:
      2016-10-23
   :Changes:

      * Feature use backend pagination widget instead of fe pagination for backend module
      * Feature _`#77425`: https://forge.typo3.org/issues/77425 Add another signal to the deleteAction()
      * Feature _`#78154`: https://forge.typo3.org/issues/78154 Hook for LoginAs
      * Bugfix _`#77240`: https://forge.typo3.org/issues/77240 Backend-Entry in Plugin cause error
      * Bugfix _`#77244`: https://forge.typo3.org/issues/77244 Javascript does not work with config.javascriptLibs.jQuery.noConflict = 1
      * Bugfix _`#77402`: https://forge.typo3.org/issues/77402 \In2code\Femanager\Controller\NewController::redirectByAction: Wrong URLs
      * Bugfix _`#77697`: https://forge.typo3.org/issues/77697 Swift Mailer Exception when using multiple confirm/notify adresses
      * Bugfix _`#78119`: https://forge.typo3.org/issues/78119 Wrong CSS class for edit form
      * Task cleaned up composer.json

 - :Version:
      2.5.1
   :State:
      stable
   :Date:
      2016-07-18
   :Changes:

      * Bugfix: Correction of copy/paste failure in composer.json

 - :Version:
      2.5.0
   :State:
      stable
   :Date:
      2016-06-27
   :Changes:

      * Bugfix _`#76615`: https://forge.typo3.org/issues/76615 Missing captcha validation
      * Task: Added composer.json and readme.md for github mirror
      * Task: Updated behat configuration
      * Task: Removed femanagerextended from extension (now located on github)
      * Task: Some PHP cleanup

 - :Version:
      2.4.0
   :State:
      stable
   :Date:
      2016-05-14
   :Changes:

      * Task Replace outdated icon with a new one
      * Bugfix _`#76140`: https://forge.typo3.org/issues/76140 PHP Warning: #1: PHP Runtime Deprecation Notice: Non-static method
      * Bugfix _`#76074`: https://forge.typo3.org/issues/76074 PHP Warning: Declaration of In2code\Femanager\Domain\Model\User::addUsergroup($usergroup) should be compatible ...

 - :Version:
      2.3.0
   :State:
      stable
   :Date:
      2016-04-28
   :Changes:

      * Task Replace outdated icon with a new one
      * Task Small HTML markup refactoring of the filter mask in backend module
      * Bugfix _`#75560`: https://forge.typo3.org/issues/75560 storage PID is not handled correct
      * Bugfix _`#75418`: https://forge.typo3.org/issues/75418 AdditionalProperties 'existingUser' forwarded to SignalSlot inside LogUtility do not contain changes anymore

 - :Version:
      2.2.0
   :State:
      stable
   :Date:
      2016-03-08
   :Changes:

      * Feature _`#73796`: https://forge.typo3.org/issues/73796 IsRequiredFieldViewHelper uses wrong validation settings for invitation actions. ATTENTION Breaking change in HTML-Partials!

 - :Version:
      2.1.0
   :State:
      stable
   :Date:
      2016-02-29
   :Changes:

      * Feature _`#73583`: https://forge.typo3.org/issues/73583 New hashing option "none"
      * Feature _`#73634`: https://forge.typo3.org/issues/73634 Log changes more detailed
      * Feature _`#73708`: https://forge.typo3.org/issues/73708 "Login As" Feature in Backend
      * Bugfix _`#73632`: https://forge.typo3.org/issues/73632 LogUtility::log is not working
      * Bugfix _`#73678`: https://forge.typo3.org/issues/73678 New password not saved
      * Bugfix _`#73689`: https://forge.typo3.org/issues/73689 PHP Warning: Declaration of In2code\Femanager\Domain\Model\User::addUsergroup( ...

 - :Version:
      2.0.1
   :State:
      stable
   :Date:
      2016-02-20
   :Changes:

      * Bugfix _`#73527`: https://forge.typo3.org/issues/73527 Own mm relations throws exception
      * Bugfix _`#73492`: https://forge.typo3.org/issues/73492 Userdata not updated after Confirmation
      * Bugfix _`#73440`: https://forge.typo3.org/issues/73440 Searchform condition broken
      * Bugfix _`#70830`: https://forge.typo3.org/issues/70830 \In2\Femanager\Controller\NewController::redirectByAction: Wrong URLs

 - :Version:
      2.0.0
   :State:
      stable
   :Date:
      2016-01-05
   :Changes:
      Refactored version of femanager
      Added Finisher implementation

 - :Version:
      1.5.2
   :State:
      stable
   :Date:
      2015-10-09
   :Changes:
      Task: Added CSS classes for BE Module TYPO3 7.x
      Bugfix: #70457 BE Module fix for TYPO3 7.x

 - :Version:
      1.5.1
   :State:
      stable
   :Date:
      2015-04-23
   :Changes:
      Task: Edit depends for Typo3

 - :Version:
      1.5.0
   :State:
      stable
   :Date:
      2015-04-23
   :Changes:
      Task: Make femanager ready for TYPO3 7.x

 - :Version:
      1.4.3
   :State:
      stable
   :Date:
      2015-01-13
   :Changes:
      Bugfixes #63714, #64164

 - :Version:
      1.4.2
   :State:
      stable
   :Date:
      2015-01-03
   :Changes:
      Bugfixes #64007, #64097

 - :Version:
      1.4.1
   :State:
      stable
   :Date:
      2015-01-02
   :Changes:
      Bugfixes #63065, #62701, #63035, #62016

 - :Version:
      1.4.0
   :State:
      stable
   :Date:
      2014-09-22
   :Changes:
      Features #61697, #61641, #61573, #61329, #60756, #60745, #60619, #60617, #60400

      Bugfixes #61380, #61136, #61000, #60967, #60558, #60510. #60508, #60480, #60414, #60409, #60406, #60401, #60348, #60297

 - :Version:
      1.3.0
   :State:
      stable
   :Date:
      2014-07-10
   :Changes:
      Features #60138, #60051

      Bugfixes #60229, #60137, #59888, #56065

 - :Version:
      1.2.2
   :State:
      stable
   :Date:
      2014-06-25
   :Changes:
      Features #59842, #59599, #59425

      Bugfixes #59877, #59623, #59580, #59542, #59238, #59188, #59178

 - :Version:
      1.2.1
   :State:
      stable
   :Date:
      2014-05-26
   :Changes:
      Features #58397, #58392

      Bugfixes #58423

 - :Version:
      1.2.0
   :State:
      stable
   :Date:
      2014-04-30
   :Changes:
      Features #56981, #57333, #57753, #57808, #58054

      Bugfixes #57347, #57535, #57889, #57987, #58078, #58079, #58112, #58335, #58345

 - :Version:
      1.1.3
   :State:
      stable
   :Date:
      2014-03-24
   :Changes:
      Bugfixes #57041, #57077, #57097

      Fix for TYPO3 6.2

 - :Version:
      1.1.2
   :State:
      stable
   :Date:
      2014-03-14
   :Changes:
      Bugfixes #56756

      BE Module fix for TYPO3 6.2

 - :Version:
      1.1.1
   :State:
      stable
   :Date:
      2014-03-11
   :Changes:
      Bugfixes #56663, #56309, #56082

      Features #56692
      Using static_info_tables for Countryselection

 - :Version:
      1.1.0
   :State:
      stable
   :Date:
      2014-02-16
   :Changes:
      Bugfixes #55857, #55851, #55824, #55709, #55275

      Features #56022, #55825, #55717

 - :Version:
      1.0.11
   :State:
      stable
   :Date:
      2014-02-05
   :Changes:
      Bugfixes #55460, #55461, #55607, #55647

      Features #54590

 - :Version:
      1.0.10
   :State:
      beta
   :Date:
      2014-01-28
   :Changes:
      Bugfixes #53088, #53089, #53323, #54307, #55275, #55295

      Features #52884, #53075, #53086, #53094, #53325, #53372, #53383, #53392, #53393, #53395, #53839, #54590, #54699

 - :Version:
      1.0.9
   :State:
      beta
   :Date:
      2014-01-12
   :Changes:
      Security fix, please update!

 - :Version:
      1.0.8
   :State:
      beta
   :Date:
      2013-10-10
   :Changes:
      Bugfixes #52575, #52573, #52363

      Features #52362, #52361

      See details in forge.typo3.org

 - :Version:
      1.0.7
   :State:
      beta
   :Date:
      2013-09-24
   :Changes:
      Femanger is now in beta state.

      Some smaller bugfixes #52063, #52062, #52004, #51834, #51470

 - :Version:
      1.0.6
   :State:
      alpha
   :Date:
      2013-08-22
   :Changes:
      Bugfix #51243 (see forge.typo3.org for details) – still looking forward for your feedback!

 - :Version:
      1.0.5
   :State:
      alpha
   :Date:
      2013-08-21
   :Changes:
      Feature #50767

      Bugfixes #51184, #50769, #50374 (see forge.typo3.org for details) - Looking forward for your feedback about femanager!

 - :Version:
      1.0.4
   :State:
      alpha
   :Date:
      2013-08-03
   :Changes:
      Feature #50348 Added date_of_birth and gender fields, so migration from sr_feuser_register is easier and #50755

      Bugfixes #50569, #50634 (see forge.typo3.org for details) – still looking forward for your feedback!

 - :Version:
      1.0.3
   :State:
      alpha
   :Date:
      2013-07-22
   :Changes:
      Added saltedpasswords support, Added some CSS classes,

      Bugfix #50086, #50084, #50037 (see forge.typo3.org for details) – still looking forward for your feedback!

 - :Version:
      1.0.2
   :State:
      alpha
   :Date:
      2013-07-09
   :Changes:
      Feature #49779 and #49821

      Bugfixes #49824 and #49812 – looking forward for your feedback!

 - :Version:
      1.0.1
   :State:
      alpha
   :Date:
      2013-07-07
   :Changes:
      Bugfix #49691 (autologin with user confirmation)

      Feature #49763 (add basic SignalSlots and add an example how to use them in the manual) – looking forward for your feedback!

 - :Version:
      1.0.0
   :State:
      alpha
   :Date:
      2013-07-04
   :Changes:
      Initial TER upload – looking forward for your feedback!
