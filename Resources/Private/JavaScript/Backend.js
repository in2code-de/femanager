jQuery(document).ready(function($) {

	// Hide/Unhide User
	$('.hideUser, .unhideUser').click(function() {
		var $this = $(this);
		var moduleUri = $this.siblings('.container_module_uri').val();
		var uid = $this.siblings('.container_uid').val();
		var table = $this.siblings('.container_table').val();

		if ($this.hasClass('hideUser')) {
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-status-user-frontend').hide();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-status-user-frontend-disabled').show();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-edit-hide').hide();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-edit-unhide').show();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-system-backend-user-switch').hide();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled').show();
			var hidden = 1;
		} else {
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-status-user-frontend').show();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-status-user-frontend-disabled').hide();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-edit-hide').show();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-edit-unhide').hide();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-system-backend-user-switch').show();
			$this
				.closest('tr')
				.find('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled').hide();
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
		console.log('#1');
		e.preventDefault();
		var $this = $(this);
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-status-status-permission-granted').hide();
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-status-status-permission-denied').show();
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-actions-system-backend-user-switch-disabled').hide();
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-actions-system-backend-user-switch').show();
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-apps-pagetree-drag-place-denied').hide();
		$this
			.closest('tr')
			.find('.tx-feusermanager-icon-apps-pagetree-drag-place-denied-disabled').show();
		var url = $this.prop('href');
		$.ajax({
			url: url
		});
	});
});
