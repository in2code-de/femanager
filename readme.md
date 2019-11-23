# TYPO3 Extension femanager

TYPO3 Frontend User Registration and Management based on
a very flexible configuration and on TYPO3 8.7 LTS and newer with the possibility to extend it.
Extension basicly works like sr_feuser_register or any other frontend user registration.

## Quick installation

Please look at the manual for a big documentation at 
https://docs.typo3.org/typo3cms/extensions/femanager/

Quick guide:
- Just install this extension - e.g. `composer require in2code/femanager` or download it 
  or install it with the classic way (Extension Manager)
- Clear caches
- Add a sysfolder for your Frontend-Users and Usergroups (or separate it)
- Add a new page content of type plugin and choose femanager
- Set the storage page to the new sysfolder

## Which femanager for which TYPO3 and PHP?

| Femanager   | TYPO3      | PHP       | Support/Development                     |
| ----------- | ---------- | ----------|---------------------------------------- |
| 5.x         | 8, 9 LTS   | 7.2 - 7.3 | Features, Bugfixes, Security Updates    |
| 4.x         | 8.7        | 7.0 - 7.2 | Security Updates|
| 3.x         | 8.7        | 7.0 - 7.2 | Security Updates on demand (ask for an offer)|
| 2.x         | 7.6        | 5.5 - 7.0 | Security Updates on demand (ask for an offer)|
| 1.x         | 6.2 - 7.6  | 5.5 - 7.0 | Security Updates on demand (ask for an offer)|

Official support (fee-based) via https://www.in2code.de/kontakt 

## Changelog

| Version    | Date       | State        | Description                                                                                                                |
| ---------- | ---------- | ------------ | -------------------------------------------------------------------------------------------------------------------------- |
| 5.2.0      | 2019-05-26 | Security     | !!!Implement CSFR Protection - please check upgrade instructions                                                           |
| 5.1.1      | 2019-05-26 | Bugfix       | Repair Admin Confirmation for TYPO3 9                                                                                      |
| 5.1.0      | 2019-05-21 | Minor update | Allow PHP 7.3, Allow TYPO3 8.7, many bugfixes                                                                              | 
| 5.0.0      | 2019-02-01 | Major update | Refactored version for TYPO3 9 LTS                                                                                         |
| 4.2.5      | 2019-01-30 | Bugfix       | Validation failed for date and some other datetypes                                                                        |
| 4.2.4      | 2019-01-24 | Bugfix       | Validation failed, if more then one content element was besides femanager plugin                                           |
| 4.2.3      | 2019-01-22 | Security     | Don't allow a complete bypass of the validation                                                                            |
| 4.2.2      | 2018-05-11 | Bugfix       | Allow filtering in OpenConfirmationView (BE), Fix case sensitive filename for OpenConfirmationView, Support TYPO3 CMS Subtree packages, update documentaion |
| 4.2.1      | 2018-05-04 | Task         | Update documenation, fix broken custom validators, enable TS for BE Module, check if admin receive is not empty            |
| 4.2.0      | 2018-04-24 | Task         | Allow to resend confirmation mail via Backend or Frontend - sponsored by Constructiva Solutions GmbH and in2code GmbH      |
| 4.1.1      | 2018-01-29 | Task         | Update license information in composer.json                                                                                |
| 4.1.0      | 2018-01-21 | Task         | Testing update: Re-include unit test and make behaviour tests more transparent                                             |
| 4.0.2      | 2018-01-19 | Bugfix       | Fix required settings for terms                                                                                            |
| 4.0.1      | 2017-12-20 | Bugfix       | Prevent exception in backend module in some special cases                                                                  |
| 4.0.0      | 2017-12-18 | Task         | - Add new field "accept terms and conditions"<br />- Add a new backend module view "accept/decline users"<br />- Implement AutoAdminConfirmation feature<br />- Add some new signals<br />- Add link to delete account in admin notification mail<br />- Admin notification settings via TypoScript |
| 3.3.0      | 2017-11-25 | Feature      | Show only relevant users in FlexForm, some small bugfixes                                                                  |
| 3.2.0      | 2017-11-10 | Task         | Add alternative login function https://github.com/in2code-de/femanager/issues/27                                           |
| 3.1.3      | 2017-10-12 | Bugfix       | Allow the usage in special contexts like with Flux. https://github.com/in2code-de/femanager/issues/17                      |
| 3.1.2      | 2017-09-06 | Task         | New version due to TER security incident. See https://typo3.org/teams/security/security-bulletins/psa/typo3-psa-2017-001/  |
| 3.1.1      | 2017-08-28 | Bugfix       | Small fixes in code to prevent errors with extension   Flux                                                                |
| 3.1.0      | 2017-08-15 | Task         | Minimize JavaScripts, Fix new button in module, Small fixes                                                                |
| 3.0.2      | 2017-08-13 | Bugfix       | Fix unserialize() exception if config is missing, add help to FAQ section                                                  |
| 3.0.1      | 2017-08-11 | Bugfix       | Enfore user for showAction, Autoload in ext_emconf, JavaScript fix, cleanup                                                |
| 3.0.0      | 2017-08-08 | Major update | Refactored version for TYPO3 8.7                                                                                           |

## Your Contribution

**Pull requests** are welcome in general! Nevertheless please don't forget to add a description to your pull requests. This
is very helpful to understand what kind of issue the **PR** is going to solve.

- Bugfixes: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going
to accept only bugfixes that can be reproduced.
- Features: Not every feature is relevant for the bulk of the users. In addition: We don't want to make the extension
even more complicated in usability for an edge case feature. Please discuss a new feature before.

## Screenshots

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create2.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create3.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit21.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend2.png" width="500" />

<img src="https://s.nimbus.everhelper.me/attachment/1317619/v5ea61f9y80o3utaf1lv/262407-HlClHQYRv0uU0oRE/screen.png" width="500" />

<img src="https://s.nimbus.everhelper.me/attachment/1317613/kway0rezl7cmockn03xm/262407-pFkmYEVCHkLZLUHv/screen.png" width="500" />
