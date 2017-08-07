jQuery(document).ready(function($) {

	/**
	 *	Process request to dismiss our admin notice
	 */
	$('#adsense-admin-upgrade-notice .notice-dismiss').click(function() {

		//* Data to make available via the $_POST variable
		data = {
			action: 'adsense_inpost_ads_admin_notice',
			adsense_inpost_ads_admin_nonce: adsense_inpost_ads_admin.adsense_inpost_ads_admin_nonce
		};

		//* Process the AJAX POST request
		$.post( ajaxurl, data );

		return false;
	});
});