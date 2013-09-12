<?php
if(!defined('YOUTORR')){
	die(1);
}
?>
<fieldset><legend>Search engine</legend>
<form action="" method="post">
<input name="search" type="text" />
<input name="submit" type="submit" value="search" />
</form>
</fieldset>
<?php
//if (!empty($_POST['search'])){
//echo $search;
if (!empty($search)){
	echo "<fieldset><legend>Result for $search</legend>\n";
	if(count($videos)>0){
		echo "<fieldset><legend>Videos : </legend>\n";
		foreach($videos as $video){
			echo "<a href='".$proto.$config['serverURL'].$config['frontTorrentFileDir']."/".$video['torrent_file'].$config['torrentExt']."'>".$video['name']
			."</a><br />\n";
			if($config['httpDownload']){
				echo "<a href='".$proto.$config['serverURL'].$config['frontTorrentDataDir']."/".$video['channel']."/".$video['name']."'>Download now</a><br />\n";
			}
		}
		echo "</fieldset>\n";
	}
	if(count($channels)>0){
			echo "<fieldset><legend>Channels : </legend>\n";
			foreach($channels as $channel){
				echo "<a href='".$proto.$config['serverURL']."?channel=".$channel['id']."'>".$channel['channel']."</a><br />\n";
			}
			echo "</fieldset>\n";
	}
	echo "</fieldset>\n";
}
