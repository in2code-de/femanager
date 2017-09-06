# TYPO3 Extension femanager

TYPO3 Frontend User Registration and Management based on
a very flexible configuration and on TYPO3 8.7 LTS with the possibility to extend it.
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

## Supported version

| Software    | Versions   |
| ----------- | ---------- |
| TYPO3       | 8.7        |
| PHP         | 7.0 - 7.2  |

## Changelog

| Version    | Date       | State        | Description                                                                                                                |
| ---------- | ---------- | ------------ | -------------------------------------------------------------------------------------------------------------------------- |
| 3.1.2      | 2017-09-06 | Task         | New version due to TER security incident. See https://typo3.org/teams/security/security-bulletins/psa/typo3-psa-2017-001/  |
| 3.1.1      | 2017-08-28 | Bugfix       | Small fixes in code to prevent errors with extension   Flux                                                                |
| 3.1.0      | 2017-08-15 | Task         | Minimize JavaScripts, Fix new button in module, Small fixes                                                                |
| 3.0.2      | 2017-08-13 | Bugfix       | Fix unserialize() exception if config is missing, add help to FAQ section                                                  |
| 3.0.1      | 2017-08-11 | Bugfix       | Enfore user for showAction, Autoload in ext_emconf, JavaScript fix, cleanup                                                |
| 3.0.0      | 2017-08-08 | Major update | Refactored version for TYPO3 8.7                                                                                           |

## Screenshots

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create2.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create3.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit21.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend2.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend3.png" width="500" />
