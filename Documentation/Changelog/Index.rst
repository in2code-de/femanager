.. include:: ../Includes.rst.txt


Changelog
=========

-
      :Version: 7.2.3
      :Date: 2023-12-13
      :Changes:

      * [SECURITY] This update is needed for version for 7.0.0 to 7.2.2 older versions are not affected
      * BUGFIX] Dont use initilize actions for granting access - thx to Daniel Hofmann (in2code)

-
      :Version: 7.2.2
      :Date: 2023-10-04
      :Changes:

      * [SECURITY] This update is needed for version for 7.0.0 to 7.2.1 older versions are not affected
      * [BUGFIX] Add missing permission check for invitation controller - thx to Daniel Hofmann (in2code)

-
      :Version: 7.2.1
      :Date: 2023-08-08
      :Changes:

      * [TASK] Adds documentation for extended - thx to Daniel Hofmann (in2code)
      * [TASK] Update Image used for Github Actions
      * [BUGFIX] Fixes reaction when no typoscript configuration is set for redirect - thx to Daniel Hofmann (in2code)
      * [BUGFIX] Fixed Object Support for getDirtyPropertiesFromUser() - thx to Daniel Hofmann (in2code)

-
      :Version: 7.2.0
      :Date: 2023-07-17
      :Changes:

      * [FEATURE] Add column "inactive since" to backend list - thx to Thomas Löffler
      * [BUGFIX] missing email AdminNotify after editing of user profile - thx to Christian Ludwig
      * [BUGFIX] Do not re-evaluate object values in ServersideValidator - thx to Torben Hansen
      * [BUGFIX] Add full object support in getDirtyPropertiesFromUser() - thx to Daniel Haupt
      * [BUGFIX] Prevent undefined array key for empty configPID - thx to Daniel Haupt
      * [BUGFIX] Migrate usage of GU::lcfirst with Rector - thx to Torben Hansen
      * [TASK] Corrects Documenation Rendering Configuration - thx to Daniel Hoffmann
      * [TASK] [TASK] Replace jQuery scrollTop with vanilla scrollIntoView - thx to Felix Ranesberger

-
      :Version: 7.1.1
      :Date: 2023-03-16
      :Changes:

      * [BUGFIX] Notification email to admin now sends changes again
      * [BUGFIX] Notification email to admin is also sent when adding recipient's address to flex form only
      * [TASK] Removes configPID from ext_typoscript_setup.typoscript

-
      :Version: 7.1.0
      :Date: 2023-01-19
      :Changes:

      * [FEATURE] Add support for PHP 8 and 8.1  - thx to Stefan Busemann, Bastien Lutz, Mathias Bolt Lesniak, Thomas Löffler, Johannes Seipelt
      * [BUGFIX] Re-fetch session from database to update 'userSession' property of TSFE.	Thx to Thomas Off <thomas.off@retiolum.de>
      * [BUGFIX] Add hash check for invitation action - thx to Max Schäfer & Dennis Schober-Wenger
      * [REFACTOR] Remove deprecated code and introduce rector  - thx to Thomas Löffler

-
      :Version: 7.0.1
      :Date: 2022-10-31
      :Changes:

      * [BUGFIX] (!!!) Security Fix - Broken Access Control in Usergroup Validation (Andreas Nedbal - in2code) - thx to TYPO3 Security Team
      * [BUGFIX] login after registration (Re-fetch session from database to update 'userSession' property of TSFE) - thx to Thomas Off
      * [BUGFIX] CleanUserGroup DataProcessor - thx to Daniel Hoffmann (in2code)

-
      :Version: 7.0.0
      :Date: 2022-07-11
      :Changes:

      * [FEATURE] Add column "inactive since" to backend list - thx to SpoonerWeb
      * [TASK] Add support for TYPO3 11
      * [TASK] Update Test enviroment and add addtitional tests
      * [TASK] Add missing translation key for "createStatus"
      * [TASK] Bump jquery from 3.2.1 to 3.6.0 and migrate deprecated shorthand event .blur and .submit - thx to Patrick Lenk
      * [BUGFIX] replace is_array with isset - hx to AlexVossBu
      * [BUGFIX] no custom controller needed in TYPO3 10
      * [BUGFIX] Remove "endtime" for check of unique in db
      * [DOCS] Add note for configpid setting
      * [BUGFIX] updated sjbr/static-info-tables version

-
      :Version: 6.3.2
      :Date: 2021-10-13
      :Changes:

      * [TASK] Add setter to allow modification of email object inside event listeners - thx to https://github.com/mediaessenz
      * [TASK] Refactor forceValues to FrontendUtility and add a test - thx to https://github.com/ute-arbeit
      * [TASK] Remove TCA configuration showRecordFieldList - thx to https://github.com/Patta
      * [BUGFIX] Make forceValues work for field names with underscores  - thx to https://github.com/ute-arbeit
      * [BUGFIX] Allow frontend user login via EXT:femanager - thx to https://github.com/webian
      * [BUGFIX] repairs confirmation view due to missing state column - thx to in2code / Bastien Lutz
      * [BUGFIX] refusing users from the admin confirmation backend list - Thx to https://github.com/fwg
      * [BUGFIX] correct v10 Extbase persistence config - thx to jonaseberle
      * [BUGFIX] add missing field mapping for custom properties for TYPO3v10+ - thx to jonaseberle
      * [BUGFIX] embedded images in emails - thx to https://github.com/fwg
      * [BUGFIX] Email subject for sendCreateUserConfirmationMail - thx to Pixelant

-
      :Version: 6.3.1
      :Date: 2021-07-19
      :Changes:

      * [BUGFIX] Security: Disallow SVG as Filetype

-
      :Version: 6.3.0
      :Date: 2021-07-02
      :Changes:

      * [FEATURE] Add divers as gender - thx to spoonerWeb
      * [FEATURE] Add possibility to set preferred or limited countries to selector - thx to spoonerWeb
      * [TASK] Use USER_INT instead of no_cache for better performance - thx to
      * [TASK] Update Behat Tests and Test Environment - thx to in2code
      * [TASK] Add option to run single behat test - thx to in2code
      * [TASK] Add terupload via Github Action - thx to in2code
      * [BUGIFX] subject translation for createUserConfirmationMail - thx to https://github.com/martinschoene
      * [BUGFIX] Fix field validation messages - thx to https://github.com/dahaupt
      * [BUGFIX] Fix link generation in general redirect method - thx to Andre Spindler
      * [BUGFIX] If user confirmation sent, do not send admin confirmation - thx to SpoonerWeb
      * [BUGFIX] Use exact email to check for resending confirmation mail - thx to SpoonerWeb


      :Version: 6.2.1
      :Date: 2021-14-06
      :Changes:

      * [BUGFIX] Use ratelimiter service only in FE context - thx to Thomas Löffler

-
      :Version: 6.2.0
      :Date: 2021-06-06
      :Changes:

      * [FEATURE] Add RateLimiter for registration form - :ref:`see documentation <countryselect>`
      * [BUGFIX] Improve date validation - thx to https://github.com/pfuju
      * [BUGFIX] Validation for checkUniqueDb ignores starttime/endtime - thx to in2code
      * [BUGFIX] return true for FileReferences  - https://github.com/marclindemann
      * [BUGFIX] Template missing for New->create - https://github.com/kitzberger
      * [BUGFIX] Select previously saved state in state menu - ttps://github.com/mabolek
      * [BUGFIX] Subject not translated on confirmation email - thx to https://github.com/Moongazer
      * [BUGFIX] Remove unnecessary paramter from disable url - thx to https://github.com/Patta
      * [TASK] change language detection to language aspect - thx to https://github.com/cehret
      * [TASK] Add validation for all unicode letters - thx to in2code
      * [TASK] Add Behaviour Test for terms and conditions - thx to in2code
      * [TASK] Add tests for countries and states, update test data - thx to in2code
      * [TASK] Resolve TYPO3 V11 breaking change for plugin registration - thx to https://github.com/Footrotflat

-
      :Version: 6.1.2
      :Date: 2020-12-03
      :Changes:

      * [BUGFIX] JavaScript error in FeManager.js - thanks to https://github.com/grischpel

-
      :Version: 6.1.1
      :Date: 2020-10-26
      :Changes:

      * [TASK] Set fixed install tool password for dev env (better dev env performance)
      * [BUGFIX] Prevent warning in UpperViewHelper - thanks to christophlehmann's PR https://github.com/in2code-de/femanager/pull/289
      * [BUGFIX] Use getter for userTS - thanks to siwa-pparzer's PR https://github.com/in2code-de/femanager/pull/287
      * [BUGFIX] fix statically called non-static method calls in InvitationController - thanks to ewokhias's PR https://github.com/in2code-de/femanager/pull/294
      * [BUGFIX] Fix Exception in wrong file upload - thanks to marclindemann's PR https://github.com/in2code-de/femanager/pull/262

-
      :Version: 6.1.0
      :Date: 2020-08-13
      :Changes:

      * [FEATURE] Add support for country selection options - :ref:`see documentation <countryselect>`
      * [BUGFIX] Allow validation via captcha - thanks to Germar https://github.com/Germar and dnozdrin https://github.com/dnozdrin

-
      :Version: 6.0.1 (!!!)
      :Date: 2020-07-15
      :Changes:

      * [TASK] Remove falsly declarated support for TYPO3 V9 in femanager 6 branch

-
      :Version: 6.0.0 (!!!)
      :Date: 2020-07-06
      :Changes:

      * [FEATURE] Add Support for TYPO3 10 LTS - Sponsored by inixmedia and in2code, Thanks to Markus Bachmann, Filar
      * [FEATURE] Add support for PageTS and UserTS configuration for femanager backend module - Sponsored by in2code
      * [FEATURE] Change user confirmation process to frontend requests - Sponsored by in2code
      * [TASK] (!!!) Remove Support for TYPO3 8.7 and PHP < 7.2
      * [TASK] Add docker based development env - Sponsored by in2code
      * [TASK] Update Unit and Behaviour Tests - Sponsored by inixmedia and in2code
      * [TASK] Remove legacy password hashing code - Sponsored by in2code

-
      :Version: 5.4.2
      :Date: 2020-12-03
      :Changes:

      * [BUGFIX] JavaScript error in FeManager.js - thanks to https://github.com/grischpel

-
      :Version: 5.4.1
      :Date: 2020-10-26
      :Changes:

      * [BUGFIX] Prevent Exceptions on wrong file upload - thanks to https://github.com/in2code-de/femanager/pull/262

-
      :Version: 5.4.0
      :Date: 2020-08-30
      :Changes:

      * [FEATURE] Add support for country selection options (for TYPO3 V8 / V9) - :ref:`see documentation <countryselect>`- Sponsored by Resultify.se

-
      :Version: 5.3.1
      :Date: 2020-08-06
      :Changes:

      * [BUGFIX] Allow validation via captcha - thanks to Germar https://github.com/Germar and dnozdrin https://github.com/dnozdrin

-
      :Version: 5.3.0
      :Date: 2020-07-06
      :Changes:

      * [FEATURE] Adds uppercase validation for password (https://github.com/in2code-de/femanager/issues/91) - thanks to https://github.com/alexkue7911
      * [FEATURE] Add support for PageTS and UserTS configuration for femanager backend module - Sponsored by in2code
      * [FEATURE] Change user confirmation process to frontend requests - Sponsored by in2code
      * [BUGFIX] Use FlexFormService to validate allowed views (https://github.com/in2code-de/femanager/issues/177) - thanks to https://github.com/nigelmann
      * [BUGFIX] TCA migrations for TYPO3 v9.5 - thanks to https://github.com/TrueType
      * [BUGFIX] Make ajax validation URL more robust - thanks to https://github.com/baschny
      * [BUGFIX] Allow '0' if field is required (https://github.com/in2code-de/femanager/issues/52) - thanks to https://github.com/DanielSiepmann
      * [DOCS] Add info about PHP7 and extending femanager - thanks to https://github.com/uwejakobs

-
      :Version: 5.2.0 (!!!)
      :Date: 2019-11-26
      :Changes:

      * !!![BUGFIX] Allow password hashing none for TYPO3 V9

 -
      :Version: 5.1.1
      :Date: 2019-05-26
      :Changes:

      * [BUGFIX] Allow password hashing none for TYPO3 V9

 -
      :Version: 5.1.0
      :Date: 2019-05-21
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

-
      :Version: 5.0.0 (!!!)
      :Date: 2019-02-01
      :Changes:

      * Feature: Support for TYPO3 version 9
      * Bugfix: Change feManagerLoginAs Feature to typeNum
      * Bugfix: Change Frontend Validation from eid script to typeNum

Pls look at https://github.com/in2code-de/femanager for a changelog for older versions

Older versions of femanager, even those which are probably not downloadable through TER, are available on github:
https://github.com/in2code-de/femanager/releases
