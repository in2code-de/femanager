.. include:: ../Includes.rst.txt


Changelog
=========
-
      :Version: 8.3.3
      :Date: 2025-09-25
      :Changes:
      * [BUGFIX] Usage of FlexForm value settings.edit.notifyAdmin - thx to https://github.com/Aletschhorn
      * [BUGFIX] fix image upload for confirmation and invitation actions - thx to Sebastian Stein (in2code)

-
      :Version: 8.3.2
      :Date: 2025-09-25
      :Changes:
      * [FEATURE] Show all usergroups of a feuser in BE Module - thx to bsschmd / Stefan Busemann
      * [BUGFIX] Resolve country select list without EXT:static:info_tables (https://github.com/in2code-de/femanager/issues/652) - thx to alexander-nitsche-governikus / Sebastian Stein
      * [BUGFIX] User confirmation buttons are active even if not enabled in - Stefan Busemann
      * [TASK] Keep development-only files out of Composer installations - thx to Oliver Klee / Stefan Busemann

-
      :Version: 8.3.1
      :Date: 2025-07-22
      :Changes:
      * [SECURITY] Avoid unintended persistence: You can disable logging function of femanager or update to the latest version.

-
      :Version: 8.3.0
      :Date: 2025-06-10
      :Changes:
      * [FEATURE] Add confirmation buttons for a final confirm - backport from V13 thx to stigfaerch

-
      :Version: 8.2.2
      :Date: 2025-05-20
      :Changes:
      * [BUGFIX] Security: Missing Hash Check for invitation controller - Invitation Templates must be updated (if a custom template is used)

-
      :Version: 8.2.1
      :Date: 2024-11-11
      :Changes:
      * [BUGFIX] Restore function to impersonate frontend user (loginAs) - thx to Christine Zoglmeier (in2code)
      * [BUGFIX] auto login - thx to Christine Zoglmeier (in2code)
      * [BUGFIX] configuration fetching for admin edit notify mails - thx to Andreas Nedbal (in2code)
      * [BUGFIX] Remove side-effect in User crdate getter - thx to Andreas Nedbal (in2code)
      * [BUGFIX] Add max size validations for default fe_user fields - thx to Andreas Nedbal (in2code)
      * [BUGFIX] Use configured receiver name in createAdminNotify - thx to Andreas Nedbal (in2code)
      * [BUGFIX] file required validation - thx to Andreas Nedbal (in2code)
      * [BUGFIX] changing image only - thx to Stefan Busemann (in2code)
      * [BUGFIX] CreateUserConfirmation.html refers to non-existing labe - thx to Stefan Busemann (in2code)
      * [BUGFIX] Resend confirmation - thx to Stefan Busemann (in2code)
      * [BUGFIX] Make upload work again - thx to Stefan Busemann (in2code)
      * [BUGFIX] TypoScript setting removeFromUserGroupSelection - thx to Stefan Busemann (in2code)
      * [BUGFIX] Exception by selecting wrong hashing integration - thx to Stefan Busemann (in2code)
      * [TASK] Change text of createRequestWaitingForAdminConfirm localization - thx to Andreas Nedbal (in2code)
      * [TASK] GitHub Actions - thx to Andreas Nedbal (in2code)
      * [TASK] Disable autocomplete for the CAPTCHA field - thx to Oliver Klee
      * [TASK] Clean up RemovePasswordIfEmptyMiddleware - thx to Andreas Nedbal (in2code)
      * [TASK] Remove unused code - thx to Stefan Busemann (in2code)
      * [REFACTOR] unify checkPageAndUserAccess - thx to Stefan Busemann (in2code)

-
      :Version: 8.2.0
      :Date: 2024-10-04
      :Changes:
      * [FEATURE] Add type and autocomplete information to input fields (port from 7.4 by in2code)
      * [TASK] Docs - add simple render command - thx to Stefan Busemann (in2code)
      * [TASK] Update Symfony dependency - thx to Julian Hofmann (in2code)
      * [TASK] Switch to PHP-based Documentation Rendering (port from 7.4 by in2code)
      * [TASK] Add autocomplete attribute to password fields (port from 7.4 by in2code)
      * [TASK] Mitigate browser "spell jacking" in form elements (port from 7.4 by in2code)
      * [TASK] Improve the code formatting of additional input attributes (port from 7.4 by in2code)
      * [TASK] Use semantic HTML for tables in the emails (port from 7.4 by in2code)
      * [BUGFIX] Update composer.json - thx to Oliver Klee
      * [BUGFIX] Pass proper object name to form error partial - thx to Pierrick Caillon
      * [BUGFIX] Improve coding style - thx to Stefan Busemann (in2code)
      * [BUGFIX] Add missing if clause in UserProperties.html  (port from 7.4 by in2code)
      * [BUGFIX] Remove type url to improve user experience - thx to Stefan Busemann (in2code)
      * [BUGFIX] Add missing make command to generate docs - thx to Stefan Busemann (in2code)
      * [BUGFIX] fix response object usage - thx Hannes Bochmann
      * [DOCS] Add header row to Features/Events/Index.rst - thx to haegelixatwork
      * [DOCS] Update EAP note - thx to Stefan Busemann (in2code)

-
      :Version: 8.1.0
      :Date: 2024-05-09
      :Changes:

      * [FEATURE] Add confirmation form to delete profile during registration - thx to Stefan Busemann (in2code)
      * [FEATURE] Add BeforeMailBodyRenderEvent - thx to Michael Bakonyi
      * [FEATURE] Include bootstrap directly from repository instead of maxcdn.bootstrapcdn.com  - thx to Felix Ranesberger (in2code)
      * [FEATURE] Allow multiple CC recipients With this change more than one CC recipient for emails can be configured - thx to Marco Huber
      * [BUGFIX] Fix a typo in the labels - thx to Oliver Klee
      * [BUGFIX] Open the "terms & conditions" page in a new tab - thx to Oliver Klee
      * [BUGFIX] errorClass attribute not working - thx to Stig Nørgaard Færch
      * [BUGFIX] Use mixed return type for ServersideValidator::getValu - thx to Andreas Nedbal (in2code)
      * [BUGFIX] Replace incorrect response in AdminConfirmation - thx Thomas Anders
      * [BUGFIX] Restore email notification to admin after registration - thx Patrick Lenk
      * [DOC] Update documenation - thx to Daniel Hoffman (in2code)

-
      :Version: 8.0.1
      :Date: 2023-12-19
      :Changes:

      * [FEATURE] Integrate Static Info tables via Service - thx to Daniel Hoffmann (in2code)
      * [TASK] Upport all fixes from 7.1-7.2 - thx to Daniel Hoffmann (in2code)
      * [TASK] Upport from scroll-fix - thx to Daniel Hoffmann (in2code)
      * [BUGFIX] Bugfix wrong colspan - thx to Luis Thaler (in2code)
      * [BUGFIX] Make backend module accessible for editors - thx to Luis Thaler (in2code)
      * [BUGFIX] Fix image URLs in README.md - thx to Daniel Haupt
      * [BUGFIX] Shows the labels in New Content Wizard again  - thx to Daniel Hoffmann (in2code)
      * [BUGFIX] Make captcha parameter optional  - thx to Daniel Hoffmann (in2code)
      * [BUGFIX] Add missing use statement for ObjectAccess - thx to Stefan Busemann (in2code)
      * [BUGFIX] Add full object support in getDirtyPropertiesFromUser() -  thx Torben Hansen
      * [BUGFIX] Do not re-evaluate object values in ServersideValidator - thx to Torben Hansen
      * [BUGFIX] Add missing pluginName in FrontendUtility - thx to Daniel Hoffmann (in2code) /  bpaulsen
      * [BUGFIX] Exception when accessing a fe_user record in the TYPO3 Backend - thx to Daniel Hoffmann (in2code)

-
      :Version: 8.0.0
      :Date: 2023-06-01
      :Changes:

      * [FEATURE] Add support for TYPO3 12 - please use the Upgrade wizzard to get your plugins to work!
      * [TASK] Remove dependencies of generic Extbase domain classes - Thx to https://github.com/theLine
      * [REFACTOR] Use Country API from TYPO3
      * [REFACTOR] Dataprocessor for CleanUserGroup is transferred to a Middleware
      * [REFACTOR] Because of new handling for uploaded files the Dataprocessor for Imagemanipulation is removed
      * [DOCUMENTATION] Replace outdated signal documentation with event description
      * [TASK] Update unit tests & behaviour tests
      * [BUGFIX] Fix validation for allowed usergroups

-
      :Version: 7.4.1
      :Date: 2024-11-11
      :Changes:
      * [TASK] Disable autocomplete for the CAPTCHA field - thx to Oliver Klee
      * [DOCS] Update Roadmap

-
      :Version: 7.4.0
      :Date: 2024-10-04
      :Changes:
      * [FEATURE] Add type and autocomplete information to input fields - thx to Oliver Klee
      * [TASK] Use semantic HTML for tables in the emails - thx to Oliver Klee
      * [TASK] Improve the code formatting of additional input attributes - thx to Oliver Klee
      * [TASK] Apply the latest Rector fixes for TYPO3 11LTS  - thx to Oliver Klee
      * [TASK] Mitigate browser "spell jacking" in form elements- thx to Patrick Lenk
      * [TASK] Add autocomplete attribute to password fields - thx to Patrick Lenk
      * [TASK] Switch to PHP-based Documentation Rendering - thx to Sandra Erbel
      * [BUGFIX] Avoid deprecated PHP function `utf8_decode` - thx to Oliver Klee
      * [BUGFIX] Add missing scope - thx to Stefan Busemann (in2code)
      * [BUGFIX] Add missing if clause in UserProperties.html - thx to Sandra Erbel
      * [BUGFIX] Use specific seleniarm image for behat tests - thx to Stefan Busemann (in2code)
      * [BUGFIX] Restore email notification to admin after registration - thx to Patrick Lenk
      * [BUGFIX] Fix composer script calls in the `test:rector:fix` script  - thx to Oliver Klee
      * [BUGFIX] Fix the Rector PHP target version - thx to Oliver Klee
      * [BUGFIX] Allow the PHPStan extension installer for Composer - thx to Oliver Klee

-
      :Version: 7.3.0
      :Date: 2024-05-08
      :Changes:

      * [FEATURE] Add confirmation form to delete profile during registration - thx to Stefan Busemann (in2code)
      * [FEATURE] Add BeforeMailBodyRenderEvent - thx to Michael Bakonyi
      * [FEATURE] Include bootstrap directly from repository instead of maxcdn.bootstrapcdn.com  - thx to Felix Ranesberger (in2code)
      * [BUGFIX] Fix a typo in the labels - thx to Oliver Klee
      * [BUGFIX] Open the "terms & conditions" page in a new tab - thx to Oliver Klee
      * [BUGFIX] errorClass attribute not working - thx to Stig Nørgaard Færch
      * [DOC] Update documenation - thx to Daniel Hoffman (in2code)

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
      :Version: 6.4.0
      :Date: 2024-05-08
      :Changes:

      * [FEATURE] Add confirmation form to delete profile during registration
      * [TASK] Remove unnecessary scrollIntoView property - thx to Felix Ranesberger (in2code)
      * [TASK] Replace jQuery scrollTop with vanilla scrollIntoView - thx to Felix Ranesberger (in2code)
      * [TASK] Build new JS distribution file - thx to Felix Ranesberger (in2code)

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
