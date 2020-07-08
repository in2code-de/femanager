jQuery(document).ready(function($) {
    var labels = JSON.parse(document.querySelector('.tx-femanager[data-labels]').dataset['labels']);

	// javascript validation
	$('.feManagerValidation').femanagerValidation($);

	// ajax uploader
	var images = createUploader($);
	// Store initially present filenames from hidden #image input in data structure
	if ($('#femanager_field_image').length > 0) {
		$.each($('#femanager_field_image').val().split(','), function(index, filename) {
			if(filename.trim().length > 0) {
				images.addImageName(filename, filename)
			}
		});
	}

	// delete image
	$('#femanager_field_preview-image').find('.qq-upload-delete').click(function(e) {
		e.preventDefault();

		var item = $(e.target).parent();
		// Remove filename from hidden #image input
		images.deleteImageName(item.find('.qq-upload-file').text());

		item.fadeOut('', function() {
			$(this).remove();
		});
	});

	// confirmation
	$('*[data-confirm]').click(function(e) {
		var message = $(this).attr('data-confirm');
		if (!confirm(message)) {
			e.preventDefault();
		}
	});

    function lcfirst (str) {
        //  discuss at: https://locutus.io/php/lcfirst/
        // original by: Brett Zamir (https://brett-zamir.me)
        //   example 1: lcfirst('Kevin Van Zonneveld')
        //   returns 1: 'kevin Van Zonneveld'

        str += ''
        var f = str.charAt(0).toLowerCase()
        return f + str.substr(1)
    }

	// data fields
    document.querySelectorAll('[data-data-endpoint]').forEach(function (nodeElement) {
        var triggerCallback = function (event) {
            console.log('Triggered event', event);

            var endpoint = nodeElement.dataset['dataEndpoint'];

            var data = {
                'tx_femanager_pi1[action]': endpoint,
                'tx_femanager_pi1[controller]': 'Data',
            };

            // Convert DOMStringMap to object. https://stackoverflow.com/a/48235245/2025722
            // According to Mozilla this should work all the way back to IE 8 without a polyfill.
            arguments = JSON.parse(JSON.stringify(nodeElement.dataset));

            console.log('Searching for additional arguments', arguments);

            for (var argument in arguments) {
                if (arguments.hasOwnProperty(argument)) {
                    console.log(' - Found argument', argument);
                    if (argument.match('arguments')) {
                        var sourceField = document.getElementById(arguments[argument]);
                        console.log('   - argument matched, source field ID, source field', argument, arguments[argument], sourceField);
                        if (sourceField) {
                            var argumentName = lcfirst(argument.substr(9));
                            console.log('   - Adding value', argumentName, sourceField.value);
                            data['tx_femanager_pi1[' + argumentName + ']'] = sourceField.value;
                        }
                    }
                }
            }

            console.log(' - Data', data);

            var url = Femanager.getBaseUrl() + 'index.php?id=' + $('#femanagerPid').val() + '&type=1594138042';

            console.log(' - URL', url);

            $.ajax({
                url: url,
                data: data,
                type: 'POST',
                cache: false,
                beforeSend: function () {
                    console.log(' - Disabling element before ajax send', nodeElement);
                    nodeElement.disabled = 1;
                    nodeElement.options.length = 0
                    nodeElement.options[0] = new Option(labels.loading_states);
                },
                success: function(options) {
                    console.log('Got response');
                    if (typeof(options) === 'object') {
                        if (Object.keys(options).length) {
                            console.table(options);
                        } else {
                            console.log('Response is empty', options);
                        }
                    } else {
                        console.log(options);
                    }

                    nodeElement.options.length = 0
                    for (var option in options) {
                        if (options.hasOwnProperty(option)) {
                            console.log(' - Adding option:', option, options[option]);
                            nodeElement.options[nodeElement.options.length] = new Option(options[option], option);
                        }
                    }
                    if (nodeElement.options.length === 0) {
                        console.log(' - Disabling element', nodeElement)
                        nodeElement.disabled = 1;
                    } else {
                        console.log(' - Enabling element', nodeElement)
                        nodeElement.disabled = 0;
                    }

                },
                error: function() {
                    console.log('Error: The called url is not available - if you use TYPO3 in a subfolder, please use config.baseURL in TypoScript');
                }
            });
        };
        var triggerFieldsString = nodeElement.dataset['triggerFields']
        if (undefined !== triggerFieldsString) {
            console.log('Element has trigger fields', nodeElement, triggerFieldsString);
            triggerFieldsString.split(',').forEach(function(element) {
                var triggerElement = document.getElementById(element);
                console.log(' - trigger field', element, triggerElement);
                if (undefined !== triggerElement) {
                    console.log(' - added event listener', triggerElement);
                    triggerElement.addEventListener('change', triggerCallback);
                }
            });
        } else {
            nodeElement.addEventListener('change', triggerCallback);
        }
        triggerCallback();
    });
});

/**
 * Create Fileuploader
 *
 * @return object
 */
function createUploader($) {

	if ($('#femanager_field_fine-uploader').length == 0) {
		return;
	}

	var imageNameHandler = {
		// Data structure to store image names for Femanager
		imageNames: {},
		// Join image names in data structure to be stored in hidden #image input
		getImageNames: function () {
			return $.map(this.imageNames, function(item) { return item; } ).join(',');
		},
		// Add filename to data structure and hidden #image input
		addImageName: function(id, filename) {
			this.imageNames[id] = filename;
			$('#femanager_field_image').val(this.getImageNames());
		},
		// Remove filename from data structure and hidden #image input
		deleteImageName: function (idToDelete) {
			delete this.imageNames[idToDelete];
			$('#femanager_field_image').val(this.getImageNames());
		}
	};
	var uploader = new qq.FineUploader({

		element: document.getElementById('femanager_field_fine-uploader'),
		request: {
			endpoint: Femanager.getBaseUrl() + 'index.php?eID=femanagerFileUpload&id=' + $('#femanagerPid').val(),
			customHeaders: {
				Accept: 'application/json'
			}
		},
		multiple: true,
		template: $('.image_container_template:first').html(),
		fileTemplate: '<li>' +
			'<div class="qq-progress-bar"></div>' +
			'<span class="qq-upload-spinner"></span>' +
			'<span class="qq-upload-finished"></span>' +
			'<span class="qq-upload-file"></span>' +
			'<span class="qq-upload-size"></span>' +
			'<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' +
			'<a class="qq-upload-retry" href="#">{retryButtonText}</a>' +
			'<a class="qq-upload-delete icon-trash" href="#">{deleteButtonText}</a>' +
			'<span class="qq-upload-status-text">{statusText}</span>' +
			'</li>',
		deleteFile: {
			enabled: true,
			forceConfirm: true,
			endpoint: Femanager.getBaseUrl() + 'index.php?eID=femanagerFileDelete&id=' + $('#femanagerPid').val() // TODO delete file on server
		},
		classes: {
			success: 'alert alert-success',
			fail: 'alert alert-error'
		},
		validation: {
			allowedExtensions: getValueFromField('#uploadFileExtension', 'jpeg, jpg, gif, png, bmp', 'array'), // allowed file extensions
			sizeLimit: getValueFromField('#uploadSize', 25000000, 'int'), // in bytes
			itemLimit: getValueFromField('#uploadAmount', 1, 'int') // limit number of uploads
		},
		callbacks: {
			onComplete: function(id, fileName, responseJSON) {
				if (responseJSON.success) {
					// show preview image
					var image = $('<img />')
						.addClass('fileupload_image')
						.prop('src', $('#uploadFolder').val() + '/' + responseJSON.uploadName)
						.prop('alt', responseJSON.uploadName)

					image.appendTo(this.getItemByFileId(id));

					// add filename to Femanager data structure
					imageNameHandler.addImageName(id, responseJSON.uploadName);
				}
			},
			onDeleteComplete: function(id, xhr, isError) {
				// Remove filename from Femanager data structure
				imageNameHandler.deleteImageName(id);
			}
		}
	});
	return imageNameHandler;
}

window.Femanager = {};

/**
 * Get value from a hidden field
 *
 * @param selector string
 * @param fallback mixed
 * @param mode string ("int", "array")
 * @returns {*}
 */
function getValueFromField(selector, fallback, mode) {
	var value = fallback;
	if ($(selector).length) {
		value = $(selector).val();
	}
	if (mode !== undefined) {
		if (mode === 'int') {
			value = parseInt(value);
		} else if (mode === 'array') {
			value = value.toString();
			value = value.replace(/[\s,]+/g, ','); // replace " , " to ","
			value = value.split(',');
		}
	}
	return value;
}

/**
 * Return BaseUrl as prefix
 *
 * @return string Base Url
 */
window.Femanager.getBaseUrl = function() {
	var baseurl;
	if (jQuery('base').length > 0) {
		baseurl = jQuery('base').prop('href');
	} else if (window.location.hostname.indexOf('localhost') !== -1) {
		baseurl = '';
	} else {
		var port = '';
		if (window.location.port.length > 0) {
			port = ':' + window.location.port;
		}
		if (window.location.protocol !== "https:") {
			baseurl = 'http://' + window.location.hostname + port + '/';
		} else {
			baseurl = 'https://' + window.location.hostname + port + '/';
		}
	}
	return baseurl;
};
