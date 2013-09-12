<?php
if(!defined('YOUTORR')){
	die(1);
}
foreach($videos as $video){
	$url=str_replace('\\','',$video['url']);
	echo '<fieldset><legend><a href="'.$proto.$config['serverURL'].'index.php?page=video&video='.$video['id'].'">
	'.$video['name'].'</a></legend>
	'.$video['channel'].'<br />
	&nbsp;<a href="'.$proto.$config['serverURL'].$config['frontTorrentFileDir'].'/'.$video['torrent_file'].$config['torrentExt'].'">Torrent file</a>&nbsp;';
	if($config['httpDownload']){
		echo '<a href="'.$config['frontTorrentDataDir'].'/'.$video['name'].'">http link</a>&nbsp;';
	}
	echo '<a href="'.$url.'">Original link</a><br />
	'.nl2br($video['desc']).'
	</fieldset>
	';
}
