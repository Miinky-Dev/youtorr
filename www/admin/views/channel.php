<?php
if(!defined('YOUTORR')){
	die(1);
}
if(isset($getChannel) && $getChannel!=''){
	echo "<fieldset><legend>$getChannel</legend>\n";
	echo "Videos downloaded ".$channel['videos']."<br />\n";
	echo "Videos reaming ".$channel['videos_tmp']."<br />\n";
	if(isset($channel['size'])){
		echo $channel['size']."/".$config['maxSize']."<br />\n";
	}
	echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=force'>Force check channel</a>\n";
	if($channel['delete']==0){
		echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=unfollow'>Unfollow channel</a><br />\n";
	}
	if($_SESSION['role'] == 100){
		if($channel['delete']==1){
			echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=undodelete'>Undo delete</a><br />\n";
		}else{
			echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=delete'>delete</a><br />\n";
		}
	}
	echo "</fieldset>\n";
}
