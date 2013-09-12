<?php
if(!defined('YOUTORR')){
	die(1);
}
if (!empty($_POST['url'])){ #url was posted :)
	#Secure variable
	$url = trim($_POST['url']);
	$urlArray=explode(' ',$url ); #nospace
	$url=$urlArray[0];
	$url = escapeshellcmd(htmlentities($url)); #try to be secure
	$name=htmlentities($_POST['name']);
	$desc=htmlentities($_POST['desc']);
	#if is a youtube channel
	if(substr_count($url,'youtube')>0 && substr_count($url,'user') > 0){
		$sql = "SELECT * FROM `channels` WHERE `url` = :url";
		$conn = $db->prepare($sql);
		$conn->bindParam(':url', $url);
		$conn->execute();
		$res = $conn->fetchAll(PDO::FETCH_ASSOC);
		if(count($res) > 0){ #video in db
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
		}else{#not in db, check videos_tmp
			$sql="INSERT INTO `channels` (`url`,`force`) VALUES (:url,1)";
			$conn = $db->prepare($sql);
			$conn->bindParam(':url', $url);
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
		addUrl($url,$db);
	}
}
#Get follow channels
$conn=$db->prepare("SELECT `value` FROM `user_meta` WHERE `id_user` = :id_user AND `key` = 'channel' "); 
$conn->bindParam(':id_user',$_SESSION['id']);
$conn->execute();
$channels=Array();
$userChannelList = $conn->fetchAll(PDO::FETCH_ASSOC);
if(count($userChannelList)>0){
	//$i=0;
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
		$channels[$counter]['currentSize']=getDirSize($config['torrentDataDir']."/".$channels[$counter]['channel']."/");
		$channels[$counter]['currentSize']=inSize($channels[$counter]['currentSize'],$config['maxSize']);
		$channels[$counter]['currentSize']=substr($channels[$counter]['currentSize'],0,5);
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