<?php
	if (!defined('ABSPATH')) { exit; }
	wp_cache_delete('atec_wpds_version');
	delete_option('atec_WPDP_settings');

	global $wp_filesystem; 	WP_Filesystem();
	$wp_filesystem->rmdir(wp_get_upload_dir()['basedir'].'/atec-deploy',true);
?>