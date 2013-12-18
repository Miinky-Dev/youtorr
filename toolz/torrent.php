<?php
/**
* This file is part of youtorr
*
* @author Jean-Lou Hau
* @copyright 2013 Jean-Lou Hau sybix@jeannedhack.org
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with youtorr.  If not, see <http://www.gnu.org/licenses/>.
*
*/
function mktorrent2($file,$trackersUrl,$torrentDir,$torrentData,$channel='',$symlink=false){
	$dest = '';
	$output = '';
	if(empty($channel) || !$symlink){
		$dest = "$torrentData";
		$output = $file.".torrent";
	}else{
		$dest = "$torrentData/$channel";
		$output = $channel."-".$file.".torrent";
	}
	$trackersUrl = explode(',',$trackersUrl);
	$torrent = new Torrent ("$dest/$file");
	foreach($trackersUrl as $tracker){
		$torrent->announce($tracker);
	}
	$magnet=$torrent->magnet(false);
	$torrent->save("$torrentDir/$output");
}
include_once('../htdocs/admin/config.php');
include_once('../htdocs/admin/functions.php');
$force=false;
$trackers=explode(',',$config['trackerUrl']);
$conn=$db->prepare("SELECT `id`,`name`,`magnetlink`,`id_channel` FROM videos");
$conn->execute();
$videos=$conn->fetchAll(PDO::FETCH_ASSOC);
$i=0;
$total = count($videos);
foreach($videos as $video){
	$i++;
	if($video['name'] == '') continue;
	echo "$i / $total : ".$video['name']."\n";
	$fileName=$video['name'];
	if(!empty($video['id_channel'])){
		$conn = $db->prepare("SELECT `id`,`channel` FROM `channels` WHERE `id`= :id_channel");
		$conn->bindParam(':id_channel', $video['id_channel']);
		$conn->execute();
		$res = $conn->fetch(PDO::FETCH_ASSOC);
		$magnetLink=mktorrent2($fileName,$config['trackerUrl'],$config['torrentFileDir'],$config['torrentDataDir'],$res['channel'],$config['channelSymlink']);
		$torrentFile = $res['channel']."-".$fileName.".torrent";
	}else{
		$magnetLink=mktorrent2($fileName,$config['trackerUrl'],$config['torrentFileDir'],$config['torrentDataDir'],'',false);
		$torrentFile = $fileName.".torrent";
	}

}
echo "\n";
?>
