<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpds_results { 
	
private $atec_wpds_root, $atec_wpds_home, $totalCount, $totalSize, $dirSizeArr, $inclFoldersize;

private function foldersize($path): array
{
	$count=0; $size = filesize($path);
	$files = glob($path.DIRECTORY_SEPARATOR.'{.[!.],}*', GLOB_BRACE);
	foreach($files as $file) 
	{
		if (is_dir($file)) { $result=$this->foldersize($file); $size+=$result[1]; $count+=$result[0]; }
		else { $count++; $size+=@filesize($file)??0; }
	} 
	return [$count,$size];
}

public function lightBox($fullpath,$filename,$icon)
{
	$fullpath=str_replace($this->atec_wpds_root, $this->atec_wpds_home, $fullpath);
	echo '<span onclick="lightBox(\'', esc_html($fullpath), '\',\'', esc_attr($icon), '\');">', esc_html($filename), '</a>';
}

// @codingStandardsIgnoreStart
public function atec_wpds_find_files($dir,$depth,&$level)
{
	$files = glob($dir.'{.[!.],}*', GLOB_BRACE);
	foreach($files as $f)
	{
		$count=substr_count($dir,DIRECTORY_SEPARATOR)-$depth;
		while ($count<$level) { echo '</ul></li>'; $level--; }

		$baseName=basename($f);
		if (is_dir($f)) 
		{ 		
			if ($this->inclFoldersize)
			{
				$total=$this->foldersize($f);
				$class=$total[1]>1000000 || $total[0]>100?'atec-red':'';
				echo '<li>', esc_attr($baseName), ' – <span class="', esc_html($class), '">', 
				esc_attr(size_format($total[1])),' (',esc_attr($total[0]),' <span class="small">', esc_attr__('files','atec-dir-scan'), '</span>)</span><ul>'; 
			}
			else echo '<li>', esc_attr($baseName), '<ul>';
			$level++;
			$this->atec_wpds_find_files($f.DIRECTORY_SEPARATOR,$depth,$level);
		}
		else
		{
			$this->totalCount++;
			$size=filesize($f); 
			$this->totalSize+=$size;
			$ext=pathinfo($baseName, PATHINFO_EXTENSION);
			$icon=getIcon($ext);
			$preview=!in_array($icon,['media-default','media-archive']) && $ext!=='php';
			echo '
			<li ', ($preview?'class="blue"':''), ' data-jstree=\'{"icon":"dashicons dashicons-',esc_attr($icon),'"}\'>';
				if ($preview) $this->lightBox($f,$baseName,$icon);			
				else echo esc_attr($baseName);
			echo ' – <span class="',esc_html($size>1000000?'atec-red':''),'">',esc_attr(size_format($size)),'</span>
			</li>';
		}
	}
}
// @codingStandardsIgnoreEnd
	
function __construct() {
	
$this->atec_wpds_root=ABSPATH;
if (!atec_is_linux()) $this->atec_wpds_root=str_replace('/','\\',$this->atec_wpds_root);
$this->atec_wpds_home=get_home_url().DIRECTORY_SEPARATOR;

$this->totalCount=0;
$this->totalSize=0;
$this->dirSizeArr=[];

function getIcon($ext): string
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
	
// @codingStandardsIgnoreStart
// Scanning can take some time.
set_time_limit(3600);
// @codingStandardsIgnoreEnd
	
echo '<div class="atec-page">';

	atec_header(__DIR__,'wpds','Dir Scan');	
	
	echo '<div class="atec-main">';
		atec_progress();
		
		$url			= atec_get_url();
		$nonce 	= wp_create_nonce(atec_nonce());
		$nav			= atec_clean_request('nav');
		$action		= atec_clean_request('action');
		
		$this->inclFoldersize = $action==='foldersize';
	
		if ($nav=='Info') { require_once('atec-info.php'); new ATEC_info(__DIR__,$url,$nonce); }
		else
		{
			echo '
			<div>
				<div class="atec-dilb">'; atec_little_block(__('Root','atec-dir-scan').': '.esc_attr($this->atec_wpds_root)); echo '</div>
				<div class="atec-dilb atec-right"><span class="atec-dilb atec-bg-w atec-border-tiny atec-box-30">'; atec_readme_button($url,$nonce); echo '	</span></div>
			</div>';
	
			echo '
			<div class="atec-g atec-border atec-mmt-10">
		
				<div id="dirScanButtons" class="atec-btn-div" style="display:none;">
					<div class="tablenav">
						';
							atec_nav_button($url,$nonce,'foldersize','','Calculate folder size',false);
							echo '
							<a class="atec-ml-10 alignleft" id="jsTreeCloseAll" href="" onclick="return jsTreeCloseAll();"><button class="button button-secondary">', esc_attr__('Close all','atec-dir-scan'), '</button></a>
							<a class="atec-ml-10 alignleft" id="jsTreeOpenAll" href="" onclick="return jsTreeOpenAll();"><button class="button button-secondary">', esc_attr__('Open all','atec-dir-scan'), '</button></a>
					</div>
				</div>
				
				<div id="dirScanLoading" class="atec-center">', esc_attr__('Loading directory tree','atec-dir-scan'), ' . . .<br>
					<div class="atec-loader-dots"><span></span><span></span><span></span><span></span><span></span></div>
				</div>';
			
				$level=0;
			
				atec_flush();
				echo '<div id="dirScan" style="display: none;"><ul>';
					$this->atec_wpds_find_files($this->atec_wpds_root,substr_count($this->atec_wpds_root,DIRECTORY_SEPARATOR),$level);
				echo '</ul></div>';
			
				echo '
				<br>
				<div id="summary">
					<table class="atec-table atec-table-tiny">
						<tr><td class="atec-label">', esc_attr__('Files','atec-dir-scan'), ':</td><td>',esc_attr(number_format($this->totalCount)),'</td></tr>
						<tr><td class="atec-label">', esc_attr__('Size','atec-dir-scan'), ':</td><td>',esc_attr(number_format($this->totalSize)),' Bytes | ',esc_html(size_format($this->totalSize)),'</td></tr>
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