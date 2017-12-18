.. include:: ../../Includes.txt


.. _autoconfirmation:

Auto admin confirmation
-----------------------

Introduction
^^^^^^^^^^^^

**Available since 4.0.0**

If you turn on an adminconfirmation by adding an email address in the FlexForm field, admins normally have to confirm
new requests from users. But it's possible to implement some own magic to make a confirmation happen automaticly.
You can add own AutoAdminConfirmation classes to bring in own magic (auto confirmation for an IP-Range, etc...).
At the moment, femanager offers only one AutoAdminConfirmation class, which decided which users are auto-confirmed by
the top-level-domain of the email-address.


EmailDomainConfirmation
^^^^^^^^^^^^^^^^^^^^^^^


Small example
'''''''''''''

Accept every user profile from domains de, it, ch and at:

::

   plugin.tx_femanager.settings {
       autoAdminConfirmation {
            # Femanager autoAdminConfirmation classes
            10 {
                class = In2code\Femanager\Domain\Service\AutoAdminConfirmation\EmailDomainConfirmation
                config {
                    # Just look at the domains of the given Email-Addresses
                    confirmByEmailDomains = .de, .it, .ch, .at
                }
            }
       }
   }


Extended example
''''''''''''''''

Accept every user profile from domains de, it, ch and at but not if they are using gmail.de or gmx.de:

::

   plugin.tx_femanager.settings {
       autoAdminConfirmation {
            # Femanager autoAdminConfirmation classes
            10 {
                class = In2code\Femanager\Domain\Service\AutoAdminConfirmation\EmailDomainConfirmation
                config {
                    # Just look at the domains of the given Email-Addresses
                    confirmByEmailDomains = .de, .it, .ch, .at
                    confirmByEmailDomainsExceptions = gmail.de, gmx.de
                }
            }
       }
   }


YourAutoConfirmation
^^^^^^^^^^^^^^^^^^^^

Of course you can add your own classes to bring in some own magic.


Example
'''''''

TypoScript:

::

   plugin.tx_femanager.settings {
       autoAdminConfirmation {
            # Femanager autoAdminConfirmation classes
            100 {
                # Classname that should be called with method isAutoAdminConfirmationFullfilled()
                class = In2code\FemanagerExtended\Domain\Service\AutoAdminConfirmation\IpAddressConfirmation
                # optional: Add configuration for your PHP
                config {
                    ip = 127.0.0.1

                    foo = bar

                    fooCObject = TEXT
                    fooCObject.value = do something with this text
                }
            }
       }
   }

PHP:

::

   <?php
   declare(strict_types=1);
   namespace In2code\FemanagerExtended\Domain\Service\AutoAdminConfirmation;

   use In2code\Femanager\Domain\Service\AutoAdminConfirmation\AbstractConfirmation;
   use TYPO3\CMS\Core\Utility\GeneralUtility;

   /**
    * Class IpAddressConfirmation
    */
   class IpAddressConfirmation extends AbstractConfirmation
   {

       /**
        * @return bool
        */
       public function isAutoConfirmed(): bool
       {
           return GeneralUtility::getIndpEnv('REMOTE_ADDR') === $this->config['ip']);
       }
   }



Some notices
^^^^^^^^^^^^

* Called method in Confirmation class is always isAutoConfirmed()
* Confirmation classes must implement In2code\Femanager\Domain\Service\AutoAdminConfirmation\ConfirmationInterface or extend In2code\Femanager\Domain\Service\AutoAdminConfirmation\AbstractConfirmation
* You have to take care, that your class is included from TYPO3 autoloader otherwise it's not included
* Per default femanager offers only EmailDomainConfirmation class at the momment

