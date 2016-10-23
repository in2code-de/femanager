jQuery(document).ready(function($) {
	// javascript validation
	$('.feManagerValidation').femanagerValidation();

	// ajax uploader
	var images = createUploader();
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
		var message = $(this).data('confirm');
		if (!confirm(message)) {
			e.preventDefault();
		}
	});
});

/**
 * Create Fileuploader
 *
 * @return object
 */
function createUploader() {
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
