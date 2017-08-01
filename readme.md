# TYPO3 Extension femanager

TYPO3 Frontend User Registration and Management based on
Extbase and Fluid and on TYPO3 7.6 and the possibility to extend it.
Extension basicly works like sr_feuser_register

## Current Status

At the moment femanger is working only on TYPO3 7.6.
To get femanager working again on TYPO3 8.7 there is an ongogin refactoring process (see details in branch develop).

## Quick installation

Please look at the manual for a big documentation at https://docs.typo3.org/typo3cms/extensions/femanager/

Quick guide:
- Just install this extension - e.g. `composer require in2code/femanager dev-develop` or download it 
  or install it with the classic way (Extension Manager)
- Clear caches
- Add a sysfolder for your Frontend-Users and Usergroups (or separate it)
- Add a new page content of type plugin and choose femanager
- Set the storage page to the new sysfolder

## Supported version (for Branch develop)

| Software    | Versions   |
| ----------- | ---------- |
| TYPO3       | 8.7        |
| PHP         | 7.0 - 7.2  |

## Screenshots

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create2.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_create3.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_edit21.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend1.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend2.png" width="500" />

<img src="https://docs.typo3.org/typo3cms/extensions/femanager/_images/femanager_backend3.png" width="500" />
