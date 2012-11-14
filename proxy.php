<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

function findIndexByFileExtAndQuality($download_links,$file_extension,$quality){
	foreach($download_links as $index=>$detail){
		if($detail['file_extension']==$file_extension && $detail['quality']==$quality){
			return $index;
		}
	}
	return 0;
}

require_once 'PhpTube.php';
$tube = new PhpTube();
$id = $_REQUEST['id'];
$watch_link = "https://www.youtube.com/watch?v=$id";
$video_info = $tube->getDownloadInfo($watch_link);

if(isset($_REQUEST['index'])){
	$index = $_REQUEST['index'];
}
else if(isset($_REQUEST['file_extension']) && isset($_REQUEST['quality'])){
	$index =  findIndexByFileExtAndQuality($video_info['download_links'],$_REQUEST['file_extension'],$_REQUEST['quality']);
}
else {
	$index = 0;
}

$download_info = $video_info['download_links'][$index];
$target_fname = empty($_REQUEST['target_fname'])?$download_info['file_name']:(PhpTube::get_download_file_name($_REQUEST['target_fname'],$download_info['file_extension'],$download_info['resolution']));

// die($download_info['url']);
// 
// 

$file_path = dirname(__FILE__) . '/files/' . $target_fname;

if(!file_exists($file_path)){
	set_time_limit(0);
	$fp = fopen($file_path, 'w');
 
	$ch = curl_init($download_info['url']);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURN_TRANSFER, false);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);	
}

header('Content-type: ' . $download_info['type']);
header('Content-Disposition: attachment; filename="' . $target_fname . '"');
header("Content-Length: " . filesize($file_path));

if (ob_get_level()) ob_end_clean(); // this line is required to turn off default output buffering in php.ini

readfile($file_path);
exit;