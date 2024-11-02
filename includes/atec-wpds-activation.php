<?php
if (!defined('ABSPATH')) { exit; }
if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');
atec_integrity_check(__DIR__);

$optName='atec_WPDS_settings';
$options=atec_create_options($optName,['foldersize']);
update_option($optName,$options);
?>