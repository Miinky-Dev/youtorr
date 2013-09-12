<?php
if(!defined('YOUTORR')){
	die(1);
}
foreach($channels as $channel){
	echo '<fieldset><legend><a href="'.$proto.$config['serverURL'].'index.php?page=channel&channel='.$channel['channel'].'">'.$channel['channel'].'</a> Total videos : '
	.$channel['nbVideo'].' <a href="'
	.$proto.$config['serverURL'].'rss.php?channel='.$channel['channel'].'">RSS</a> <a href="'.$channel['url'].'">Original Channel</a>';
	if($config['zipUserTorrent'] && file_exists($config['frontTorrentFileDir']."/".$config['zipPrefix'].$channel['channel'].".zip")){
		echo '<a href="'.$proto.$config['serverURL'].$config['frontTorrentFileDir'].'/'.$config['zipPrefix'].$channel['channel'].".zip".'">All torrent files</a>';
	}
	echo '</legend>';
	if(!empty($channel['size'])){
		echo 'Estimed size : ' . $channel['size'].'<br />';
	}
	foreach($channel['videos'] as $video){
		echo '<a href="'.$proto.$config['serverURL'].'index.php?page=video&video='.$video['id'].'">'.$video['name']
		.'</a><br />
		';
	}
	echo '</fieldset>
	';
}
