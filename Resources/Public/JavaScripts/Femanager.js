jQuery(document).ready(function() {
	// javascript validation
	$('.feManagerValidation').femanagerValidation();

	// ajax uploader
	createUploader();

	// delete image
	$('#preview-image').find('.qq-upload-delete').click(function(e) {
		e.preventDefault();
		$('#image').val('');
		$('.fileupload_image, .qq-upload-list').fadeOut('', function() {
			$(this).remove();
		});
	});
});

/**
 * Create Fileuploader
 *
 * @return void
 */
function createUploader() {
	if ($('#fine-uploader').length == 0) {
		return;
	}

	var image;
	var uploader = new qq.FineUploader({
		element: document.getElementById('fine-uploader'),
		request: {
			endpoint: getBaseUrl() + 'index.php?eID=femanagerFileUpload',
			customHeaders: {
				Accept: 'application/json'
			}
		},
		multiple: false,
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
			endpoint: getBaseUrl() + 'index.php?eID=femanagerFileDelete' // TODO delete file on server
		},
		classes: {
			success: 'alert alert-success',
			fail: 'alert alert-error'
		},
		validation: {
			allowedExtensions: ['jpeg', 'jpg', 'gif', 'png', 'bmp'],
			sizeLimit: 25000000, // in bytes
			itemLimit: 1 // limit number of uploads
		},
		callbacks: {
			onComplete: function(id, fileName, responseJSON) {
				if (responseJSON.success) {
					// show preview image
					image = $('<img />')
						.addClass('fileupload_image')
						.attr('src', 'uploads/pics/' + responseJSON.uploadName)
						.attr('alt', responseJSON.uploadName)
					$('#preview-image').html(image);

					// fill filename to hidden field
					$('#image').val(responseJSON.uploadName);
				}
			},
			onDeleteComplete: function(id, xhr, isError) {
				image.fadeOut('', function() {
					$(this).remove();
				});
			}
		}
	});
}

/**
 * Return BaseUrl as prefix
 *
 * @return string		Base Url
 */
function getBaseUrl() {
	var baseurl;
	if (jQuery('base').length > 0) {
		baseurl = jQuery('base').attr('href');
	} else if (window.location.hostname.indexOf('localhost') !== -1) {
		baseurl = '';
	} else {
		if (window.location.protocol != "https:") {
			baseurl = 'http://' + window.location.hostname;
		} else {
			baseurl = 'https://' + window.location.hostname;
		}
	}
	return baseurl;
}