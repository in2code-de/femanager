jQuery.fn.femanagerValidation = function() {
	var element = $(this);
	var requestCallback;
	var submitFormAllowed = false;
	if (element.find('*[data-validation]').length == 0) {
		submitFormAllowed = true;
	}

	/**
	 * AJAX queue function
	 */
	var MyRequestsCompleted = (function() {
		var numRequestToComplete,
			requestsCompleted,
			callBacks,
			singleCallBack;

		return function(options) {
			if (!options) options = {};

			numRequestToComplete = options.numRequest || 0;
			requestsCompleted = options.requestsCompleted || 0;
			element = options.element || 0;
			callBacks = [];
			var fireCallbacks = function() {
				$('body').css('cursor', 'default');
				submitForm(element); // submit form
				for (var i = 0; i < callBacks.length; i++) callBacks[i]();
			};
			if (options.singleCallback) callBacks.push(options.singleCallback);

			this.addCallbackToQueue = function(isComplete, callback) {
				if (isComplete) requestsCompleted++;
				if (callback) callBacks.push(callback);
				if (requestsCompleted == numRequestToComplete) fireCallbacks();
			};
			this.requestComplete = function(isComplete) {
				if (isComplete) requestsCompleted++;
				if (requestsCompleted == numRequestToComplete) fireCallbacks();
			};
			this.setCallback = function(callback) {
				callBacks.push(callBack);
			};
		};
	})();

	// on field blur
	$('*[data-validation]').blur(function() {
		validateField($(this), false); // validate this field only
	});

	// form submit
	element.submit(function(e) {
		$('body').css('cursor', 'wait');
		if (!submitFormAllowed) {
			e.preventDefault();
			validateAllFields($(this));
		}
	});

	/**
	 * Validate every field in form
	 *
	 * @param object element		Form object
	 * @return void
	 */
	function validateAllFields(element) {
		// Store number of ajax requests for queue function
		requestCallback = new MyRequestsCompleted({
			numRequest: element.find('*[data-validation]').length,
			element: element
		});

		// one loop for every field to validate
		element.find('*[data-validation]').each(function() {
			validateField($(this), true);
		});
	}

	/**
	 * Validate single filed
	 *
	 * @param object element		Field object
	 * @return void
	 */
	function validateField(element, countForSubmit) {
		var user = element.closest('form').find('div:first').find('input[name="tx_femanager_pi1[user][__identity]"]').val();
		var url = Femanager.getBaseUrl() + 'index.php' + '?eID=' + 'femanagerValidate';
		var validations = getValidations(element);
		var elementValue = element.val();
		if ((element.prop('type') == 'checkbox') && (element.prop('checked') == false)) {
			elementValue = '';
		}
		var additionalValue = '';
		if (indexOfArray(validations, 'sameAs')) { // search for "sameAs(password)"
			var validationSameAs = indexOfArray(validations, 'sameAs');
			var fieldToCompare = getStringInBrackets(validationSameAs);
			var fieldToCompareObject = $('input[name="tx_femanager_pi1[user][' + fieldToCompare + ']"]');
			additionalValue = fieldToCompareObject.val();
			if ((fieldToCompareObject.prop('type') == 'checkbox') && (fieldToCompareObject.prop('checked') == false)) {
				additionalValue = '';
			}
		}

		$.ajax({
			url: url,
			data: {
				'tx_femanager_pi1[validation]': element.data('validation'),
				'tx_femanager_pi1[value]': elementValue,
				'tx_femanager_pi1[field]': getFieldName(element),
				'tx_femanager_pi1[user]': (user !== undefined ? user : ''),
				'tx_femanager_pi1[additionalValue]=': (additionalValue ? additionalValue : ''),
				'storagePid': $('#femanagerStoragePid').val(),
				'L': $('#femanagerLanguage').val(),
				'id': $('#femanagerPid').val()
			},
			type: 'POST',
			cache: false,
			success: function(data) { // return values
				if (countForSubmit) {
					requestCallback.addCallbackToQueue(true);
				}
				if (data) {
					try {
						var json = $.parseJSON(data);
						if (!json.validate) {
							writeErrorMessage(element, json.message)
						} else {
							cleanErrorMessage(element);
						}
					} catch(e) {
						element.before(data)
					}

				}
			},
			error: function() {
				logAjaxError();
			}
		});
	}

	/**
	 * Read fieldname
	 * 		get "email" out of "tx_femanager_pi1[user][email]"
	 * 		get "passwort_repeat" out of "tx_femanager_pi1[password_repeat]"
	 *
	 * @param element
	 * @return string
	 */
	function getFieldName(element) {
		var nameParts = element.prop('name').split('[');
		var name = nameParts[nameParts.length-1].replace(']', '');
		return name;
	}

	/**
	 * Write errormessage next to the field
	 *
	 * @param element				Field
	 */
	function writeErrorMessage(element, message) {
		cleanErrorMessage(element); // remove all errors to this field at the beginning
		var errorMessage = $('.femanager_validation_container').html().replace('{messages}', message); // get html for error
		element.before(errorMessage); // add message
		element.closest('.control-group').addClass('error');
		element.addClass('error');
	}

	/**
	 * Remove one error message
	 *
	 * @param element
	 */
	function cleanErrorMessage(element) {
		element.closest('.control-group').removeClass('error');
		element.siblings('.alert').remove(); // hide message to this field
		element.removeClass('error');
	}

	/**
	 * Check if there are errors and submit form
	 *
	 * @param element
	 */
	function submitForm(element) {
		// submit form if there are no errors
		if (element.find('.error').length == 0) {
			submitFormAllowed = true;
			element.submit();
		} else {
			$('html,body').animate({
				scrollTop: element.find('.error:first').offset().top
			});
		}
	}

	/**
	 * Check if part of a value exist in an array
	 *
	 * @param array
	 * @return string found value
	 */
	function indexOfArray(array, string) {
		for (var i=0; i < array.length; i++) {
			if (array[i].indexOf(string) !== -1) {
				return array[i];
			}
		}
		return '';
	}

	/**
	 * Get validation methods of a field
	 * 		data-validation="required,max(5)" => array
	 * 			required,
	 * 			max(5)
	 *
	 * @param element
	 * @return array
	 */
	function getValidations(element) {
		return element.data('validation').split(',');
	}

	/**
	 * Get string in brackets
	 * 		lala(lulu) => lulu
	 *
	 * @param string
	 * @return string
	 */
	function getStringInBrackets(string) {
		var result = '';
		if (string.indexOf('(') !== -1) {
			var parts = string.split('(');
			result = parts[1].substr(0, parts[1].length - 1);
		}
		return result;
	}

	/**
	 * Log Error in Console
	 *
	 * @return void
	 */
	function logAjaxError() {
		if (typeof console === 'object') {
			console.log('Error: The called url is not available - if you use TYPO3 in a subfolder, please use config.baseURL in TypoScript');
		}
	}
};
