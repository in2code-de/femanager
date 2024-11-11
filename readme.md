# TYPO3 Extension femanager

TYPO3 Frontend User Registration and Management based on
a very flexible configuration and on TYPO3 11.5 LTS and newer with the possibility to extend it.
Extension basicly works like sr_feuser_register or any other frontend user registration.

## Support
This TYPO3 Extension is free to use. We as in2code and our developers highly appreciate your feedback and work hard to improve our extensions.
To do so, in2code provides two extra days per month for coding and developing (Coding Night and Freaky Friday). During these days our more than 20 developers spend their time with improvements and updates for this and other extensions.

You can support our work [here](https://www.in2code.de/extensionsupport).

Thank you very much in advance.

Your in2code Team

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

| Femanager | TYPO3     | PHP       | Support/Development                                            |
|-----------|-----------|-----------|----------------------------------------------------------------|
| 13.x      | 13 LTS    | 8.2 - 8.4 | Features, Bugfixes, Security Updates                           |
| 8.x       | 12 LTS    | 8.1 -     | Bugfixes, Security Updates                                     |
| 7.x       | 11 ELTS   | 7.4 - 8.1 | Security Updates                                               |
| 6.x       | 10 ELTS   | 7.2 - 7.4 | Security Updates                                               |
| 5.x       | 8, 9 ELTS | 7.2 - 7.3 | Out of support / security updates on demand (ask for an offer) |
| 4.x       | 8.7       | 7.0 - 7.2 | Out of support / security updates on demand (ask for an offer) |
| 3.x       | 8.7       | 7.0 - 7.2 | Out of support / security updates on demand (ask for an offer) |
| 2.x       | 7.6       | 5.5 - 7.0 | Out of support / security updates on demand (ask for an offer) |
| 1.x       | 6.2 - 7.6 | 5.5 - 7.0 | Out of support / security updates on demand (ask for an offer) |

## Your Contribution

**Pull requests** are welcome in general! Please note these requirements:
* Unit Tests must still work
* Behaviour Tests must still work
* Describe how to test your pull request
* TYPO3 coding guidelines must be respected

- **Bugfixes**: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going
to accept only bugfixes that can be reproduced.
- **Features**: Not every feature is relevant for the bulk of the users. In addition: We don't want to make the extension
even more complicated in usability for an edge case feature. Please discuss a new feature before.

### Branches

* Main: For the latest LTS Version (currently Version for TYPO3 12)
* V7: Version 7 for TYPO3 11)
* V6: Version 6 for TYPO3 10)
* V5: Version 5 for TYPO3 9)
* V2: Version 2 for TYPO3 8)

### Contribution with ddev

#### Requirements

1. Install ddev, see: https://ddev.readthedocs.io/en/stable/#installation
2. Install git-lfs, see: https://git-lfs.github.com/

#### Installation

1. Clone this repository
2. Run `ddev start`
3. Run `ddev initialize` to setup configurations and test database

## Screenshots

<img src="/Documentation/Images/femanager_create1.png" width="500" alt="Profile creation"/>

<img src="/Documentation/Images/femanager_create2.png" width="500" alt="Admin confirmation email"/>

<img src="/Documentation/Images/femanager_create3.png" width="500" alt="Field validation"/>

<img src="/Documentation/Images/femanager_edit1.png" width="500" alt="Profile editing"/>

<img src="/Documentation/Images/femanager_edit2.png" width="500" alt="Additional fields"/>

<img src="/Documentation/Images/femanager_backend1.png" width="500" alt="Plugin configuration"/>

<img src="/Documentation/Images/femanager_backend2.png" width="500" alt="User log"/>
