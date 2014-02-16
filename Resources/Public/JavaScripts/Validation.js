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
			callBacks = [];
			var fireCallbacks = function () {
				$('body').css('cursor', 'default');
				submitForm(); // submit form
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
	$(document).on('submit', '.feManagerValidation', function(e) {
		$('body').css('cursor', 'wait');
		if (!submitFormAllowed) {
			e.preventDefault();
			validateAllFields(element);
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
			numRequest: element.find('*[data-validation]').length
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
		var user = $('input[name="tx_femanager_pi1[user][__identity]"]').val();
		var url = Femanager.getBaseUrl() + 'index.php' + '?eID=' + 'femanagerValidate';

		$.ajax({
			url: url,
			data:
				'tx_femanager_pi1[validation]=' + element.data('validation') +
				'&tx_femanager_pi1[value]=' + element.val() +
				'&tx_femanager_pi1[field]=' + getFieldName(element) +
				(user != undefined ? '&tx_femanager_pi1[user]=' + user : '') +
				'&storagePid=' + $('#femanagerStoragePid').val() +
				'&L=' + $('#femanagerLanguage').val() +
				'&id=' + $('#femanagerPid').val(),
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
			}
		});
	}

	/**
	 * Read fieldname
	 * 		get "email" out of "tx_femanager_pi1[user][email]"
	 *
	 * @param element
	 * @return string
	 */
	function getFieldName(element) {
		var nameParts = element.prop('name').split('[');
		var name = nameParts[2].replace(']', '');
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
	 */
	function submitForm() {
		// submit form if there are no errors
		if (element.find('.error').length == 0) {
			submitFormAllowed = true;
			element.submit();
		}
	}
};