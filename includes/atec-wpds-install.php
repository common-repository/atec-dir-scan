<?php
if (!defined( 'ABSPATH' )) { exit; }

if (!defined('ATEC_INIT_INC')) require_once('atec-init.php');
add_action('admin_menu', function() { atec_wp_menu(__DIR__,'atec_wpds','Dir Scan'); } );

add_action('init', function()
{ 
	if (in_array($slug=atec_get_slug(), ['atec_group','atec_wpds']))
	{
		if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');	
		add_action( 'admin_enqueue_scripts', function() { atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.002'); });
		
		if (!function_exists('atec_load_pll')) { require_once('atec-translation.php'); }
		atec_load_pll(__DIR__,'dir-scan');
		
		if ($slug!=='atec_group')
		{
			function atec_wpds(): void { require_once('atec-dir-scan-results.php'); }			
			add_action( 'admin_enqueue_scripts', function()
			{
				atec_reg_style('atec_wpds',__DIR__,'atec-wpds.min.css','1.0.0');
				atec_reg_style('jstree',__DIR__,'themes/default/jstree.min.css','3.3.16');
				atec_reg_style('basicLightbox',__DIR__,'basicLightbox.min.css','1.0.0');
				
				atec_reg_script('atec_wpds',__DIR__,'atec-wpds.min.js','1.0.0');
				atec_reg_script('jstree',__DIR__,'jstree.min.js','3.3.16');
				atec_reg_script('basicLightbox',__DIR__,'basicLightbox.min.js','1.0.0');	  
			});		
		}
	}	
});
?>