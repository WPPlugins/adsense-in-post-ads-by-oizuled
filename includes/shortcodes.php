<?php
/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}
/* Set Shortcodes */
function adsense_inpost_ads_a() {
	global $aip_options;
	if(!isset($adsense_a)) {
		$adsense_a = do_shortcode( $aip_options[ 'unit_a' ] );
	}
	return $adsense_a;
}
add_shortcode('AdSense-A', 'adsense_inpost_ads_a');

function adsense_inpost_ads_b() {
	global $aip_options;
	if(!isset($adsense_b)) {
		$adsense_b = do_shortcode( $aip_options[ 'unit_b' ] );
	}
	return $adsense_b;
}
add_shortcode('AdSense-B', 'adsense_inpost_ads_b');

function adsense_inpost_ads_c() {
	global $aip_options;
	if(!isset($adsense_c)) {
		$adsense_c = do_shortcode( $aip_options[ 'unit_c' ] );
	}
	return $adsense_c;
}
add_shortcode('AdSense-C', 'adsense_inpost_ads_c');