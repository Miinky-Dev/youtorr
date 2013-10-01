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
if(!defined('YOUTORR')){
	die(1);
}
if (!empty($_POST['url'])){ #url was posted :)
	#Secure variable
	$url = trim($_POST['url']);
	$urlArray=explode(' ',$url ); #nospace
	$url=$urlArray[0];
	$url = escapeshellcmd($url); #try to be secure
	$name=htmlentities($_POST['name']);
	$desc=htmlentities($_POST['desc']);
	#if is a youtube channel
	if(substr_count($url,'youtube') > 0 && (substr_count($url,'user') || substr_count($url,"list=") ) > 0){
		$type="user";
		if(substr_count($url,'list=')){ #It's a playlist
			$type="playlist";
		}
		$sql = "SELECT * FROM `channels` WHERE `url` = :url";
		$conn = $db->prepare($sql);
		$conn->bindParam(':url', $url);
		$conn->execute();
		$res = $conn->fetchAll(PDO::FETCH_ASSOC);
		if(count($res) > 0){ #channel in db update user_meta if needed
			$conn=$db->prepare("SELECT * FROM `user_meta` WHERE `id_user`=:id_user AND `key`='channel' AND `value` = :id_channel");
			$conn->bindParam(':id_user',$_SESSION['id']);
			$conn->bindParam(':id_channel',$res[0]['id']);
			$conn->execute();
			$res2=$conn->fetchAll(PDO::FETCH_ASSOC);
			if(count($res2) == 0){
				$conn=$db->prepare("INSERT INTO `user_meta` (`id_user`,`key`,`value`) VALUES (:id_user,'channel',:id_channel) ");	
				$conn->bindParam(':id_user',$_SESSION['id']);
				$conn->bindParam(':id_channel',$res[0]['id']);
				$conn->execute();
			}
		}else{#not in db, add channels
			$sql="INSERT INTO `channels` (`url`,`force`,`type`,`desc`,`channel`) VALUES (:url,1,:type,:desc,:name)";
			$conn = $db->prepare($sql);
			$conn->bindParam(':url', $url);
			$conn->bindParam(':type', $type);
			$conn->bindParam(':name', $name);
			$conn->bindParam(':desc', $desc);
			$conn->execute();
			$conn = $db->prepare("SELECT `id` FROM `channels` WHERE `url` = :url");
			$conn->bindParam(':url', $url);
			$conn->execute();
			$res = $conn->fetchAll(PDO::FETCH_ASSOC);
			$conn=$db->prepare("INSERT INTO `user_meta` (`id_user`,`key`,`value`) VALUES (:id_user,'channel',:id_channel)");
			$conn->bindParam(':id_user',$_SESSION['id']);
			$conn->bindParam(':id_channel',$res[0]['id']);
			$conn->execute();
		}
	}else{
		addUrl($url,$db,'',$desc,$name);
	}
}
#Get follow channels
$conn=$db->prepare("SELECT `value` FROM `user_meta` WHERE `id_user` = :id_user AND `key` = 'channel' "); 
$conn->bindParam(':id_user',$_SESSION['id']);
$conn->execute();
$channels=Array();
$userChannelList = $conn->fetchAll(PDO::FETCH_ASSOC);
if(count($userChannelList)>0){
	$counter=0;
	foreach($userChannelList as $userChannel){
		$conn=$db->prepare("SELECT * FROM `channels` WHERE `id` = :id_channel");
		$conn->bindParam(':id_channel',$userChannel['value']);
		$conn->execute();
		$channels[] = $conn->fetch(PDO::FETCH_ASSOC);
		$sql="SELECT `id`,`name`,`desc`,`url`,`torrent_file` FROM `videos` WHERE `id_channel` = :id_channel ORDER BY `id` ASC";
		if($config['nbVideoChannel'] > 0){
			$sql.=" LIMIT 0, :limit";
		}
		$conn=$db->prepare($sql);
		$conn->bindParam(':id_channel',$userChannel['value']);
		if($config['nbVideoChannel'] > 0){
			$conn->bindParam(':limit',$config['nbVideoChannel']);
		}
		$conn->execute();
		$channels[$counter]['videos']=$conn->fetchAll(PDO::FETCH_ASSOC);
		$channels[$counter]['nbVideo']=dbCounter("SELECT count(*) AS count FROM `videos` WHERE `id_channel` = ".$userChannel['value'],$db);
		$channels[$counter]['remain'] = dbCounter("SELECT count(*) AS count FROM `videos_tmp` WHERE `id_channel` = ".$userChannel['value'],$db);
		if($channels[$counter]['nbVideo'] > 0){
			$channels[$counter]['currentSize']=getDirSize($config['torrentDataDir']."/".$channels[$counter]['channel']."/");
			$channels[$counter]['currentSize']=inSize($channels[$counter]['currentSize'],$config['maxSize']);
			$channels[$counter]['currentSize']=substr($channels[$counter]['currentSize'],0,5);
		}
		$counter++;
	}
}
#Get video
$conn=$db->prepare("SELECT `value` FROM `user_meta` WHERE `id_user` = :id_user AND `key` = 'video' "); 
$conn->bindParam(':id_user',$_SESSION['id']);
$conn->execute();
$videos=Array();
$userVideoList = $conn->fetchAll(PDO::FETCH_ASSOC);
if(count($userVideoList)>0){
	$counter=0;
	foreach($userVideoList as $userVideo){
		$conn = $db->prepare("SELECT * FROM `videos` WHERE `id` = :id_video;");
		$conn->bindParam(':id_video',$userVideo['value']);
		$conn->execute();
		$videos[$counter]=$conn->fetch(PDO::FETCH_ASSOC);
		$counter++;
	}
}

#Get tmp video
$conn=$db->prepare("SELECT `value` FROM `user_meta` WHERE `id_user` = :id_user AND `key` = 'video_tmp' "); 
$conn->bindParam(':id_user',$_SESSION['id']);
$conn->execute();
$videosTMP=Array();
$userVideoTMPList = $conn->fetchAll(PDO::FETCH_ASSOC);
if(count($userVideoTMPList)>0){
	$counter=0;
	foreach($userVideoTMPList as $userVideoTMP){
		$conn = $db->prepare("SELECT * FROM `videos_tmp` WHERE `id` = :id_video;");
		$conn->bindParam(':id_video',$userVideoTMP['value']);
		$conn->execute();
		$videosTMP[$counter]=$conn->fetch(PDO::FETCH_ASSOC);
		$counter++;
	}
}
include('views/default.php');
