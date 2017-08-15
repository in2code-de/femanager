.. include:: ../Includes.txt

.. _faq:

Frequently Asked Questions (FAQ)
================================


Q: How to use static_info_tables for countryselection?
------------------------------------------------------

A: See code in best practice section


Q: How to add new fields to fe_users?
-------------------------------------

A: See code in best practice section


Q: How to add my own field validation?
--------------------------------------

A: See code in best practice section


Q: Class 'In2code\Femanager\Domain\Model\Log' not found - what can I do?
------------------------------------------------------------------------

A: This problem normally occurs only if you have installed femanager without composer (btw: it's time for composer :),
so your instance is running in *classic mode*.
It's possible that you have added femanager via Extension Manager. Please go into the install tool by opening the URL
www.yourdomain.org/typo3/install (of course you have to add a file named *ENABLE_INSTALL_TOOL* to typo3conf/ folder
first) and click on *Create autoload information for extensions* in section *Dump Autoload Information*.
That will create a new autoload file.


Q: An exception occurred while executing 'SELECT `uid` FROM `tx_femanager_domain_model_log` WHERE ... doesn't exist
-------------------------------------------------------------------------------------------------------------------

A: There are some tables missing in your database. Please open the install tool under
www.yourdomain.org/typo3/install (of course you have to add a file named *ENABLE_INSTALL_TOOL* to typo3conf/ folder
first) and click on Compare current database with specification.


Q: How can I disable the clientside/serverside validation?
----------------------------------------------------------

A: Enable/Disable Validation via TypoScript – disable example:

.. code-block:: text

	plugin.tx_femanager {
		settings.new.validation {
			_enable.client = 0
			_enable.server = 0
		}
	}


Q: How can I configure the validationof my fields?
--------------------------------------------------

A: Have a look into TypoScript:

.. code-block:: text

	plugin.tx_femanager {
		settings.new.validation {
			_enable.client = 1
			_enable.server = 2

			# validation of user input values
			# possible validations for each field are: required, email, min, max, intOnly, lettersOnly, uniqueInPage, uniqueInDb, date, mustInclude(number,letter,special,space), mustNotInclude(number,letter,special,space), inList(1,2,3), captcha, sameAs(password)
			# see manual for an example how to add custom serverside and clientside validation
			validation {
				# Enable clientside Formvalidation (JavaScript)
				_enable.client = 1

				# Enable serverside Formvalidation (PHP)
				_enable.server = 1

				username {
					required = 1
					uniqueInDb = 1
					mustNotInclude = special,space
				}
				email {
					required = 1
					email = 1
					#uniqueInPage = 1
				}
				password {
					required = 1
					#min = 10
					#mustInclude = number,letter,special
				}
				usergroup {
					#inList = 1,2,3
				}
			}
		}
	}

Note: If you use validation for passwords, values will be send via AJAX to server to check if all is right. It's recommended to use https connections for the registration form.


Q: System should generate random passwords – possible?
------------------------------------------------------

Since version 1.0.10 femanager is able to create passwords and username per random if there is no input. If you want to use this, please disable required settings of password (and username)

- If no username given, try to get email (if set)
- If no username and no email given, create a username by random
- If no password given, create a password by random
- It's possible to create a new user without filling out any field – this could be used as “onetimeaccount”


Q: How can I prefill form fields?
---------------------------------

A: You can use TypoScript cObj to fill form fields in registration- or edit-form:

.. code-block:: text

	plugin.tx_femanager {
		settings {
			new {
				prefill {
					username = TEXT
					username.value = ExampleUsername

					email = TEXT
					email.value = test@in2code.de
				}
			}
			edit {
				prefill {
					# fill from GET or POST param like &username=Alex
					username = TEXT
					username.data = GP:username

					# fill from GET or POST param like &email=info@test.de
					email = TEXT
					email.data = GP:email
				}
			}
		}
	}


Q: JavaScript Validation won't work – what can I do?
----------------------------------------------------

A: Check if all needed JavaScript files are loaded (see frontend html- source). Add some JavaScript with constant editor or directly:

.. code-block:: text

    plugin.tx_femanager.settings.jQuery = 1
    plugin.tx_femanager.settings.bootstrap = 1
    plugin.tx_femanager.settings.bootstrapCSS = 1

Example ordering and needed JavaScripts:

.. code-block:: text

    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js" type="text/javascript"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/typo3conf/ext/femanager/Resources/Public/JavaScript/Validation.min.js?1502824793" type="text/javascript"></script>
    <script src="/typo3conf/ext/femanager/Resources/Public/JavaScript/Femanager.min.js?1502824793" type="text/javascript"></script>


Q: How can I send user values to a third-party-software like a CRM?
-------------------------------------------------------------------

A: Use some lines of TypoScript to send values after a registration to a tool like a CRM. Test it with a simple php file on your server which sends an email to you with the $_REQUEST Array.

TypoScript:

.. code-block:: text

	plugin.tx_femanager {
		settings {
			new {
				# Send Form values via POST to another system (e.g. CRM like salesforce or eloqua)
				sendPost {
					# Activate sendPost (0/1)
					_enable = TEXT
					_enable.value = 1

					# Target URL for POST values (like http://www.target.com/target.php)
					targetUrl = https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8
					#targetUrl = http://eloqua.com/e/f.aspx

					# build your post datas like &param1=value1&param2=value2
					data = COA
					data {
						10 = TEXT
						10 {
							# value from field {username}
							field = username
							wrap = &username=|
						}

						20 = TEXT
						20 {
							# value from field {email}
							field = email
							wrap = &email=|
						}

						30 = TEXT
						30 {
							# value from field {title}
							field = title
							wrap = &title=|
						}
					}

					# activate debug mode - shows all configuration from curl settings (needs extension devlog)
					debug = 0
				}
			}
		}
	}


Q: How can I store values in another table?
-------------------------------------------

A: With some lines of TypoScript it's possible to store values to any table in the TYPO3 database:

.. code-block:: text

	plugin.tx_femanager {
		settings {
			new {
				# Save user values to one or more other tables (e.g. tt_address or something else)
				#       With .field=[fieldname] you have access to the user object
				#       Possible values are: uid, username, address, city, company, country, email, fax, firstName, lastName, middleName, name, password, telephone, fax, title, www, zip and lastGeneratedUid (to have access to the uid of the last loop in the next loop)
				storeInDatabase {
					tt_address {
						_enable = TEXT
						_enable.value = 0

						pid = TEXT
						pid.value = 21

						name = TEXT
						name.field = username

						email = TEXT
						email.field = email

						first_name = TEXT
						first_name.field = firstName

						last_name = TEXT
						last_name.field = lastName
					}
				}
			}
		}
	}


Q: How can I overwrite labels and errormessages with my own text?
-----------------------------------------------------------------

A: Every TYPO3 extension can be extended with own labels in any language. You have to search for the key, that you want to overwrite – have a look into the file EXT:femanager/Resources/Private/Language/locallang.xlf – example:

.. code-block:: text

	plugin.tx_femanager {
		_LOCAL_LANG {
			# Field Labels
			default.tx_femanager_domain_model_user\.username = Email
			de.tx_femanager_domain_model_user\.username = E-Mail
			fr.tx_femanager_domain_model_user\.username = E-mail

			# Errormessages
			default.validationErrorRequired = This is a mandatory field
			de.validationErrorRequired = Hierbei handelt es sich um ein Pflichtfeld
			fr.validationErrorRequired = Ce champ est obligatoire
		}
	}


Q: How to add a captcha for spam prevention
-------------------------------------------

A: Since version 1.1.0 femanager allows sr_freecap as captcha extension. Import sr_freecap to your TYPO3. From this moment on, you can add a new fieldtype in your flexform “captcha”. In addition you have to enable captcha with TypoScript:

.. code-block:: text

	plugin.tx_femanager.settings.new.validation.captcha.captcha = 1
	plugin.tx_femanager.settings.edit.validation.captcha.captcha = 1


Q: How to change the dateformat of the birthday field?
------------------------------------------------------

A: Per default “m/d/Y” (for EN) and “d.m.Y “ (for DE) will be used. If you want to overwrite this, you can use some lines of TypoScript

.. code-block:: text

	plugin.tx_femanager {
		_LOCAL_LANG {
			# Default Language
			default.tx_femanager_domain_model_user.dateFormat = Y-m-d
			default.tx_femanager_domain_model_user.dateOfBirth.placeholder = Y-m-d

			# For DE
			de.tx_femanager_domain_model_user.dateFormat = Y-m-d
			de.tx_femanager_domain_model_user.dateOfBirth.placeholder = Y-m-d
		}
	}


Q: Flashmessage from other plugins are shown in femanager!
----------------------------------------------------------

A: If you want to hide flashmessages in other extbase plugins, use following TypoScript setup:

.. code-block:: text

	config.tx_extbase.legacy.enableLegacyFlashMessageHandling = 0


Q: “Please log in before” but I'm already logged in?
----------------------------------------------------

A: Check if the logged in user is really logged in (e.g. add a content-element which should not be viewed if the FE-User is not yet logged in). Check if the FE-Users have an empty value or 0 for column tx_extbase_type in your database.


Q: How to change the extbase_type of new Users?
-----------------------------------------------

A: You can change this via TypoScript:

.. code-block:: text

	config.tx_extbase.persistence.classes {
		In2code\Femanager\Domain\Model\User {
			mapping {
				tableName = fe_users
				RecordType = anyExtbaseType
			}
		}
	}


Q: What else can I do with femanager?
-------------------------------------

A: Have a look into the TypoScript of femanager (see EXT:femanager/Configuration/TypoScript/Main/setup.txt) – here you see how to configure your mails, forceValues, redirect, autologin and much more...
