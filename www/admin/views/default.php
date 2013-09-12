<?php
if(!defined('YOUTORR')){
	die(1);
}
?>
<form action="index.php" method="post">
	url : <br />
	<input type="texte" name="url" /><br />
	name : <br />
	force video name :<br />
	<input type="texte" name="name" /><br />
	force video description :<br />
	<input type="texte" name="desc" /><br />
	<input type="submit" value="send" />
</form>
<?php 
if(count($videosTMP) > 0){
	echo '<fieldset><legend>Video to download</legend>';
	foreach($videosTMP as $videoTMP){
		echo $videoTMP['url']."<br />";
	}
	echo '</fieldset>';
}
if(count($videos) > 0){
	echo '<fieldset><legend>Downloaded videos</legend>';
	foreach($videos as $video){
		echo '<a href="'.$proto.$config['serverURL'].'/index.php?page=video&video='.$video['id'].'" >'.$video['name'].'</a><br />';
	}
	echo '</fieldset>';
}

if(count($channels) > 0){
	foreach($channels as $channel){
		echo '<fieldset><legend><a href="'.$proto.$config['serverURL'].'/admin/index.php?page=channel&channel='.$channel['channel'].'">'.$channel['channel'].'</a> Total videos : '
		.$channel['nbVideo'].' <a href="'
		.$proto.$config['serverURL'].'rss.php?channel='.$channel['channel'].'">RSS</a> <a href="'.$channel['url'].'">Original Channel</a></legend>
		';
		echo 'Total videos : '.$channel['nbVideo'].'<br />';
		echo 'Remain videos : '.$channel['remain'].'<br />';
		echo 'Disk Space : '.$channel['currentSize'].'<br />';
		if(count($channel['videos']) > 0){
			foreach($channel['videos'] as $video){
				echo '<a href="'.$proto.$config['serverURL'].'index.php?page=video&video='.$video['id'].'">'.$video['name']
				.'</a><br />
				';
			}
		}
		echo '</fieldset>
		';
	}
}
