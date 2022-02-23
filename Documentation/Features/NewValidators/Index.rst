.. include:: ../../Includes.txt
.. include:: Images.txt

.. _extendvalidators:

Adding own serverside and clientside validation to femanager forms
------------------------------------------------------------------

Picture
^^^^^^^

|newField1|

Add own clientside (JcavaScript) Validation

|newField2|

Add own serverside (PHP) Validation


Basics
^^^^^^

- Use TypoScript to override ValidationClass of femanager with own classes – this enables your validation methods
- Config the new validation methods via TypoScript
- Add translation labels via TypoScript

.. attention::
   The following example works only with TYPO3 Version 10.4 LTS

See https://github.com/einpraegsam/femanagerextended for an example extension how to extend femanager
with new fields and validation methods


Step by Step
^^^^^^^^^^^^

New validation classes
""""""""""""""""""""""

CustomClientsideValidator.php:

.. code-block:: php

   namespace YourVendor\YourExtension\Domain\Validator;

   class CustomClientsideValidator extends \In2code\Femanager\Domain\Validator\ClientsideValidator
   {

      /**
       * Custom Validator
       *              Activate via TypoScript - e.g. plugin.tx_femanager.settings.new.validation.username.custom = validationSetting
       *
       * @param string $value Given value from input field
       * @param string $validationSetting TypoScript Setting for this field
       * @return bool
       */
      protected function validateCustom($value, $validationSetting): bool
      {
         // check if string has string inside
         if (stristr($value, $validationSetting)) {
            return true;
         }
         return false;
      }
   }

CustomServersideValidator.php:

.. code-block:: php

   namespace YourVendor\YourExtension\Domain\Validator;

   class CustomServersideValidator extends \In2code\Femanager\Domain\Validator\ServersideValidator
   {

      /**
       * Custom Validator
       *              Activate via TypoScript - e.g. plugin.tx_femanager.settings.new.validation.username.custom = validationSetting
       *
       * @param string $value Given value from input field
       * @param string $validationSetting TypoScript Setting for this field
       * @return bool
       */
      protected function validateCustom($value, $validationSetting): bool
      {
         // check if string has string inside
         if (stristr($value, $validationSetting)) {
            return true;
         }
         return false;
      }
   }


Configure override of ServersideValidator and ClientsideValidator
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

ext_localconf.php

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Domain\Validator\ServersideValidator::class] = [
      'className' => \YourVendor\YourExtension\Domain\Validator\CustomServersideValidator::class,
   ];
   $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Domain\Validator\ClientsideValidator::class] = [
      'className' => \YourVendor\YourExtension\Domain\Validator\CustomClientsideValidator::class,
   ];


TypoScript to enable new validation and set labels
""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: typoscript

   plugin.tx_femanager {
      settings.new.validation {
         _enable.client = 1
         _enable.server = 1
         username {
            # Custom Validator - check if value includes "abc"
            custom = abc
         }
      }
      _LOCAL_LANG {
         default.validationErrorCustom = "abc" is missing
         de.validationErrorCustom = "abc" wird erwartet
      }
   }

