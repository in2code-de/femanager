jQuery(document).ready(function() {

	// Hide/Unhide User
	// Example JS jumpToUrl('tce_db.php?&data[fe_users][49][disable]=0&redirect='+T3_THIS_LOCATION+'&vC=39d21224a5&formToken=b92111ada60f4a360e95c29537f4d57ddfb96c73&prErr=1&uPT=1');
	$('.hideUser, .unhideUser').click(function() {
		var $this = $(this);
		var formToken = $this.siblings('.container_formtoken').val();
		var uid = $this.siblings('.container_uid').val();
		var table = $this.siblings('.container_table').val();

		if ($this.hasClass('hideUser')) {
			$this.removeClass('t3-icon-edit-hide').removeClass('hideUser').addClass('t3-icon-edit-unhide').addClass('unhideUser');
			$this.closest('.femanager_list_line').children('.col-icon').children(':first').html('<span class="t3-icon t3-icon-status t3-icon-status-overlay t3-icon-overlay-hidden t3-icon-overlay">&nbsp;</span>');
			var hidden = 1;
		} else {
			$this.removeClass('t3-icon-edit-unhide').removeClass('unhideUser').addClass('t3-icon-edit-hide').addClass('hideUser');
			$this.closest('.femanager_list_line').children('.col-icon').children(':first').html('');
			var hidden = 0;
		}
		url = 'tce_db.php?&data[' + table + '][' + uid + '][disable]=' + hidden + '&redirect=' + T3_THIS_LOCATION + '&vC=b601970a97' + formToken + '&prErr=1&uPT=1';
		$.ajax({
			url: url
		});
	});

	// Delete User
	$('.deleteUser').click(function() {
		var $this = $(this);
		var formToken = $this.siblings('.container_formtoken').val();
		var uid = $this.siblings('.container_uid').val();
		var table = $this.siblings('.container_table').val();
		var confirmationMessage = $this.siblings('.container_label_delete_confirmation').val();

		if (confirm(confirmationMessage)) {
			$this.closest('tr').fadeOut('fast');
			var url = 'tce_db.php?&cmd[' + table + '][' + uid + '][delete]=1&redirect=' + T3_THIS_LOCATION + '&vC=3c76f1d3bb&prErr=1&uPT=1' + formToken
			$.ajax({
				url: url
			});
		}
	});

	// User Logout
	$('.logoutUser').click(function(e) {
		e.preventDefault();
		$(this).closest('tr').find('.t3-icon-status-permission-granted').removeClass('t3-icon-status-permission-granted').addClass('t3-icon-status-permission-denied');

		var url = $(this).prop('href');
		$.ajax({
			url: url
		});
	});
});