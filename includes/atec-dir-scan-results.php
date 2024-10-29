<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpds_results { 

private function foldersize($path): array
{
	$count=0; $size = filesize($path);
	$files = scandir($path);
	foreach($files as $file) 
	{
		 $fullpath=$path.'/'.$file;
		if (is_file($fullpath) && is_readable($fullpath)) $size+=@filesize($fullpath);
	
		 if ($file != '.' && $file != '..') 
		 {
			$isDir=is_dir($fullpath);
			if (!$isDir && $file!=='.DS_Store') $count++;
			if (is_dir($fullpath)) 
			{ $result=$this->foldersize($fullpath); $size+=$result[1]; $count+=$result[0]; }
		}
		} 
	return [$count,$size];
}
	
private function getIcon($ext): string
{
	$icon='media-default';
	if ($ext!=='')
	switch ($ext) {
		case (in_array($ext,['xls','xlsx','csv','numbers'])): $icon='media-spreadsheet'; break;
		case (in_array($ext,['ppt','pptx','key'])): $icon='media-interactive'; break;
		case (in_array($ext,['doc','docx','pages'])): $icon='media-document'; break;
		case (in_array($ext,['php','html','html','json','css','js'])): $icon='media-code'; break;
		case (in_array($ext,['txt','log'])): $icon='media-text'; break;
		case (in_array($ext,['pdf'])): $icon='pdf'; break;
		case (in_array($ext,['aac','aiff','flac','m4a','m4p','mp3','ogg','wav','webm'])): $icon='media-audio'; break;
		case (in_array($ext,['mp4','mov','avi','wmv','webm','flv'])): $icon='media-video'; break;
		case (in_array($ext,['rar','zip','gz','tar'])): $icon='media-archive'; break;
		case (in_array($ext,['svg','png','gif','jpeg','jpg','apng','bmp','ico','webp'])): $icon='format-image'; break;
	}
	return $icon;
}
	
function __construct() {
	
// @codingStandardsIgnoreStart
// Scanning can take some time.
set_time_limit(600);
// @codingStandardsIgnoreEnd

	
echo '<div class="atec-page">';

	atec_header(__DIR__,'wpds','Dir Scan');	
	
	echo '<div class="atec-main">';
		atec_progress();
		$root=ABSPATH;
		if (!atec_is_linux()) $root=str_replace('/','\\',$root);
		
		$url			= atec_get_url();
		$nonce 	= wp_create_nonce(atec_nonce());
		$nav			= atec_clean_request('nav');
	
		if ($nav=='Info') { require_once('atec-info.php'); new ATEC_info(__DIR__,$url,$nonce); }
		else
		{

			echo '
			<div>
				<div class="atec-dilb">'; atec_little_block(__('Root','atec-dir-scan').': '.esc_attr($root)); echo '</div>
				<div class="atec-dilb atec-right">
					<span class="atec-dilb atec-bg-white atec-border-tiny atec-box-30">'; atec_readme_button($url,$nonce); echo '	</span>
				</div>
			</div>';
	
			echo '
				<div class="atec-g atec-border atec-mmt-10">
			
					<div id="dirScanButtons" style="display:none; width:100%;">
						<a class="atec-mr-10" id="jsTreeCloseAll" href="" onclick="return jsTreeCloseAll();"><button class="button button-secondary">', esc_attr__('Close all','atec-dir-scan'), '</button></a>
						<a id="jsTreeOpenAll" href="" onclick="return jsTreeOpenAll();"><button class="button button-secondary">', esc_attr__('Open all','atec-dir-scan'), '</button></a>
					</div>
						
					<div id="dirScanLoading">
						<small>', esc_attr__('Loading can take a while, as the whole directory tree is scanned','atec-dir-scan'), ' ...</small><br><br>
						<img alt="', esc_attr__('Loading','atec-dir-scan'), '" src="',esc_url( plugins_url( '/assets/css/themes/default/throbber.gif', __DIR__ ) ) ,'" style="height:22px;"><br>
					</div>';
					atec_progress();
							
						$home=get_home_url().'/';
						function lightBox($root,$home,$file,$icon)
						{
							$fullpath=str_replace($root, $home, $file->getPath().'/'.$file->getFilename());
							echo '<span onclick="lightBox(\'', esc_html($fullpath), '\',\'', esc_attr($icon), '\');">', esc_html($file->getFilename()), '</a>';
						}
						
						$totalSize=0; $totalCount=0; $c=0; $level=0;
			
						$directory = new RecursiveDirectoryIterator($root,RecursiveDirectoryIterator::SKIP_DOTS);
						$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST); 
						// {
						// 	echo 'FULL '.$current->getPathName();
						// 	echo '<br>';
						// }

					echo '
					<div id="dirScan">
									
						<ul>';
						//$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($root,RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
						foreach ($iterator as $file)
						//foreach (new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST) as $filename=>$file) 
						{
							$c++;
							$filename=$file->getFilename();
	
							if (!$file->isDir()) $totalCount++;
							$size=($file->isFile() && $file->isReadable())?@$file->getSize():0; 
							$totalSize+=$size;
			
							while ($iterator->getDepth()<$level) { echo '</ul></li>'; $level--; }
	
							if ($file->isDir()) 
							{
								echo '<li>', esc_html($filename);
							}
							else 
							{
								$ext=$iterator->getExtension();
								$icon=$this->getIcon($ext);
								$preview=!in_array($icon,['media-default','media-archive']) && $ext!=='php';
								echo '<li ', ($preview?'class="blue"':''), ' data-jstree=\'{"icon":"dashicons dashicons-',esc_attr($icon),'"}\'>';
								if ($preview) lightBox($root,$home,$file,$icon);			
								else echo esc_html($filename);
							}
							if ($file->isDir()) 
							{
								$fullpath=$file->getPathName(); //.'/'.$file->getFilename();
								$total=$this->foldersize($fullpath);
								$class=$total[1]>1000000 || $total[0]>100?'atec-red':'';
								echo ' – <span class="',esc_html($class),'">',esc_attr(size_format($total[1])),' (',esc_attr($total[0]),' <span class="small">', esc_attr__('files','atec-dir-scan'), '</span>)</span><ul>'; 
								$level++;
							}
							else 
							{
								$class=$size>1000000?'atec-red':'';
								echo ' – <span class="',esc_html($class),'">',esc_attr(size_format($size)),'</span></li>';
							}
						}          
									
					echo '
						</ul>
					</div>';
				
				echo '
					<br>
					<div id="summary">
						<table class="atec-table atec-table-tiny">
							<tr><td class="atec-label">', esc_attr__('Files','atec-dir-scan'), ':</td><td>',esc_attr(number_format($totalCount)),'</td></tr>
							<tr><td class="atec-label">', esc_attr__('Size','atec-dir-scan'), ':</td><td>',esc_attr(number_format($totalSize)),' Bytes | ',esc_html(size_format($totalSize)),'</td></tr>
						</table>
					</div>
				</div>';
		}
	echo '
	</div>
</div>';

if (!class_exists('ATEC_footer')) require_once('atec-footer.php');

	atec_reg_inline_script('wpds_dir_scan', '
	jQuery(function () 
	{
		jQuery("#dirScan")
		.jstree({"plugins" : [ "themes", "html_data", "sort" ]})
		.bind("select_node.jstree", function (e, data) { data.instance.toggle_node(data.node); })
		.bind("ready.jstree", function()
		{ 
			jQuery("#dirScanLoading").remove(); 
			jQuery("#dirScan, #summary, #dirScanButtons").show(); 
		});
		jQuery(document).keyup(function(e) { if (e.keyCode == 27 && instance) { instance.close(); } })
	});', true);

}}

new ATEC_wpds_results;
?>