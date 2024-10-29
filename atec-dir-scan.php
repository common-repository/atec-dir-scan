<?php
if ( !defined('ABSPATH') ) { die; }
/**
* Plugin Name:  atec Dir Scan
* Plugin URI: https://atecplugins.com/
* Description: Navigate through the whole directory tree of your WP installation, including file count and file size.
* Version: 1.3.9
* Requires at least: 5.2
* Tested up to: 6.6.3
* Requires PHP: 7.4
* Author: Chris Ahrweiler
* Author URI: https://atec-systems.com
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-dir-scan
*/
  
if (is_admin()) 
{
	wp_cache_set('atec_wpds_version','1.3.9');
	register_activation_hook( __FILE__, function() { require_once('includes/atec-wpds-activation.php'); });
    require_once('includes/atec-wpds-install.php');
}
?>
