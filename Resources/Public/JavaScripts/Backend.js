jQuery(document).ready(function($) {

	// Hide/Unhide User
	$('.hideUser, .unhideUser').click(function() {
		var $this = $(this);
		var moduleUri = $this.siblings('.container_module_uri').val();
		var uid = $this.siblings('.container_uid').val();
		var table = $this.siblings('.container_table').val();

		if ($this.hasClass('hideUser')) {
			$this
				.removeClass('t3-icon-edit-hide')
				.removeClass('hideUser')
				.addClass('t3-icon-edit-unhide')
				.addClass('unhideUser');
			$this
				.closest('.femanager_list_line')
				.children('.col-icon')
				.children(':first')
				.html('<span class="t3-icon t3-icon-status t3-icon-status-overlay t3-icon-overlay-hidden t3-icon-overlay">&nbsp;</span>');
			var hidden = 1;
		} else {
			$this.removeClass('t3-icon-edit-unhide').removeClass('unhideUser').addClass('t3-icon-edit-hide').addClass('hideUser');
			$this.closest('.femanager_list_line').children('.col-icon').children(':first').html('');
			var hidden = 0;
		}
		url = moduleUri + '&data[' + table + '][' + uid + '][disable]=' + hidden + '&redirect=' + T3_THIS_LOCATION;
		$.ajax({
			url: url
		});
	});

	// Delete User
	$('.deleteUser').click(function() {
		var $this = $(this);
		var moduleUri = $this.siblings('.container_module_uri').val();
		var uid = $this.siblings('.container_uid').val();
		var table = $this.siblings('.container_table').val();
		var confirmationMessage = $this.siblings('.container_label_delete_confirmation').val();

		if (confirm(confirmationMessage)) {
			$this.closest('tr').fadeOut('fast');
			var url = moduleUri + '&cmd[' + table + '][' + uid + '][delete]=1&redirect=' + T3_THIS_LOCATION
			$.ajax({
				url: url
			});
		}
	});

	// User Logout
	$('.logoutUser').click(function(e) {
		e.preventDefault();
		var $this = $(this);
		$this
			.closest('tr')
			.find('.t3-icon-status-permission-granted')
			.removeClass('t3-icon-status-permission-granted')
			.addClass('t3-icon-status-permission-denied');

		var url = $this.prop('href');
		$.ajax({
			url: url
		});
	});
});
