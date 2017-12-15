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

See https://github.com/einpraegsam/femanagerextended for an example extension how to extend femanager
with new fields and validation methods


Step by Step
^^^^^^^^^^^^

Override Validation Classes with TypoScript
"""""""""""""""""""""""""""""""""""""""""""

.. code-block:: text

	config.tx_extbase{
		objects {
			In2code\Femanager\Domain\Validator\ServersideValidator.className = In2code\Femanagerextended\Domain\Validator\CustomServersideValidator
			In2code\Femanager\Domain\Validator\ClientsideValidator.className = In2code\Femanagerextended\Domain\Validator\CustomClientsideValidator
		}
	}

New validation classes
""""""""""""""""""""""

CustomClientsideValidator.php:

.. code-block:: text

	namespace In2code\Femanagerextended\Domain\Validator;

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
		protected function validateCustom($value, $validationSetting)
		{
			// check if string has string inside
			if (stristr($value, $validationSetting)) {
				return TRUE;
			}
			return FALSE;
		}
	}

CustomServersideValidator.php:

.. code-block:: text

	namespace In2code\Femanagerextended\Domain\Validator;

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
		protected function validateCustom($value, $validationSetting)
		{
			// check if string has string inside
			if (stristr($value, $validationSetting)) {
				return TRUE;
			}
			return FALSE;
		}
	}


TypoScript to enable new validation and set labels
""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: text

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

