<?php

error_reporting(E_NONE);
ini_set('display_errors',0);

function findIndexByFileExtAndQuality($download_links,$file_extension,$quality){
	foreach($download_links as $index=>$detail){
		if($detail['file_extension']==$file_extension && $detail['quality']==$quality){
			return $index;
		}
	}
        
        foreach($download_links as $index=>$detail){
		if($detail['file_extension']==$file_extension && $detail['quality']==$download_links[0]['quality']){
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

$download_info = isset($video_info['download_links'][$index])?$video_info['download_links'][$index]:$video_info['download_links'][0];

$target_fname = empty($_REQUEST['target_fname'])?$download_info['file_name']:(PhpTube::get_download_file_name($_REQUEST['target_fname'],$download_info['file_extension'],$download_info['resolution']));
if((isset($_REQUEST['file_extension']) && $_REQUEST['file_extension'] != $download_info['file_extension']) OR (isset($_REQUEST['quality']) && $_REQUEST['quality'] != $download_info['quality'])){
    $script_path = dirname($_SERVER['PHP_SELF']);
    header("Location: $script_path/$id/{$download_info['quality']}/$target_fname");
    die();
}

$file_path = dirname(__FILE__) . '/files/' . $target_fname;

if(!file_exists($file_path)){
	set_time_limit(0);
	$fp = fopen($file_path, 'w');
 
	$ch = curl_init($download_info['url']);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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