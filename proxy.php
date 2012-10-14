<?php
require_once 'PhpTube.php';
$tube = new PhpTube();
$watch_link = "http://www.youtube.com/watch?v=$id";
$video_info = $tube->getDownloadInfo($watch_link);
$index = isset($_REQUEST['index'])?$_REQUEST['index']:0;
$download_info = $video_info['download_links'][$index];
header('Content-type: ' . $download_info['type']);
header('Content-Disposition: attachment; filename="' . $download_info['file_name'] . '"');

if (ob_get_level()) ob_end_clean(); // this line is required to turn off default output buffering in php.ini

readfile($download_info['url']);
