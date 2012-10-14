<?php
if(count($_POST)>0){
	$op = isset($_REQUEST['op'])?$_REQUEST['op']:'get_links';
	require_once 'PhpTube.php';
	$tube = new PhpTube();
	$links = array_filter(preg_split('#\n+#', $_POST['links']));
	$video_info = array();

	foreach ($links as $watch_link) {
		$video_info[] = $tube->getDownloadInfo($watch_link);
	}
}

?>
<html>
	<title>PhpTube Downloader</title>
	<body>
		<form method="post">
			<input type="hidden" name='op' value='get_links'/>
			<label>Video Links</label><br/>
			<textarea id='video_links' name='links'><?php echo isset($_POST['links'])?$_POST['links']:''; ?></textarea><br/>
			<input type="submit" value='Get Download Info'/>
		</form>
		<?php if(!empty($video_info)):?>
		<ol>
			<?php foreach ($video_info as $download_info):?>
			<li>
				<h3><?php echo $download_info['title']?></h3>
				<img src="<?php echo $download_info['thumbnail_hq']?>" alt="<?php echo $download_info['title']?>"/><br/>
				<table cellspacing="2" cellpadding="5">
					<tr>
						<th>Quality</th>
						<th>Type</th>
						<th>Resolution</th>
						<th>&nbsp;</th>
					</tr>
				<?php foreach ($download_info['download_links'] as $index=>$link_info):?>
					<tr>
						<td><?php echo $link_info['quality'];?></td>
						<td><?php echo array_shift(explode(';',$link_info['type']));?></td>
						<td><?php echo $link_info['resolution'];?></td>
						<td><a href="proxy.php?id=<?php echo $download_info['video_id']?>&index=<?php echo $index;?>" target="_blank">Download</a></td>
					</tr>
				<?php endforeach;?>
				</table>
			</li>
			<?php endforeach;?>
		</ol>
		<?php endif;?>
	</body>
</html>