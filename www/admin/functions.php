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

$db=null;
include('inc/Torrent.php');
try { #Connect to the db
	switch($config['dbEngine']){
		case "sqlite" :
			$db = new PDO('sqlite:'.$config['dbName']);	
			break;
		case "mysql" :
			$db = new PDO('mysql:host='.$config['dbHost'].';port='.$config['dbPort'].';dbname='.$config['dbName'],$config['dbUser'],$config['dbPassword']);
			break;
		default :
			syslog(LOG_EMERG,$config['dbEngine']." not supported");
			die();
	}
} catch (PDOException $e ) {
	syslog(LOG_EMERG,$e->getMessage());
	die();
}

function launchProccess($cmd,$logFile){
	syslog(LOG_INFO,"Commande : $cmd");
	syslog(LOG_INFO,"Log file : $logFile");
	$cmdDescriptor = array(
		0 => array("pipe", "r"),  // stdin pipe 
		1 => array("file", $logFile, "w"),  // stdout to file  
		2 => array("pipe", "w")   // stderr pipe 
	);
	$proc = proc_open($cmd,$cmdDescriptor,$pipes);
	do{
		sleep(2);
		$procInfo = proc_get_status($proc);
	}while($procInfo['running']);
	$err_string = stream_get_contents($pipes[2]); //reading from std_err
	fclose($pipes[2]); 
	return $err_string;
}

function getDirSize($path){
	$total = 0;
	$dir = opendir($path);
	if($dir != null){
		while($entry = readdir($dir)){
			if($entry == "." || $entry == ".."){
				continue;
			}
			if(is_dir($entry)){
				$total += getDirSize($path.$entry);
			}else{
				$total += filesize($path.$entry);
			}
		}
	}
	return $total;
}

function inSize($currentSize,$maxSize){ 
	$maxUnit = substr($maxSize,-1,1);
	$units = array('K','M','G','T');
	foreach($units as $unit){
		$currentSize /= 1024;
		if($unit == $maxUnit){
			break;
		}
	}
	return $currentSize;
}

function isSpace($maxSize,$path){
	$currentSize = getDirSize($path);
	$currentSize = inSize($currentSize,$maxSize);
	$maxSize = substr($maxSize,0,-1);
	if($currentSize < $maxSize){
		return true;
	}
	return false;
}

function dbCounter($sql,$db){
	$conn = $db->prepare($sql);
	$conn->execute();
	$res = $conn->fetch(PDO::FETCH_ASSOC);
	$result = $res['count'];
	return $result;
}

function mktorrent($file,$trackersUrl,$torrentDir,$torrentData,$channel='',$symlink=false,$tmpDir=''){
	$dest = '';
	$output = '';
	if(empty($channel) || !$symlink){
		$dest = "$torrentData";
		$output = $file.".torrent";
	}else{
		$dest = "$torrentData/$channel";
		$output = $channel."-".$file.".torrent";
	}
	rename("$tmpDir/$file","$dest/$file");
	$trackersUrl = explode(',',$trackersUrl);
	$torrent = new Torrent ("$dest/$file");
	foreach($trackersUrl as $tracker){
		$torrent->announce($tracker);
	}
	$magnet=$torrent->magnet(false);
	$torrent->save($output);
        #$cmd = escapeshellcmd("mktorrent -a '".$trackerUrl."' $dest/$file --output=\"$output\"");
	#syslog(LOG_INFO,$cmd);
        #$retour = exec($cmd);
        if(!$torrent->errors()){#Move torrent file and data
            #copy the file to the torrent directory
			if(!empty($channel) && $symlink){
				symlink("$dest/$file","$torrentData/$file");
			}
			#unlink("$file");
			copy($output,"$torrentDir/$output");
			unlink($output);
			return $magnet;
        }else{
		unlink($file);
		return false;
	}
}

#return false if url is in videos or tmp_video 
function addUrl($url,$db,$channel=''){
	$sql = "SELECT * FROM `videos` WHERE `url` = :url"; #check if url exist in videos
	$conn = $db->prepare($sql);
	$conn->bindParam(':url', $url);
	$conn->execute();
	$res = $conn->fetchAll(PDO::FETCH_ASSOC);
	if (count($res) > 0 && !empty($_SESSION['id'])){ #video in db
		#Check if user had video
		$idVideo = $res[0]['id'];
		$sql = "SELECT * FROM `user_meta` WHERE `key`='video' AND `id_user`=:id_user AND `value`=:id_video;";
		$conn = $db->prepare($sql);
		$conn->bindParam(':id_user',$_SESSION['id']);
		$conn->bindParam(':id_video',$res[0]['id']);
		$conn->execute();
		$res = $conn->fetchAll(PDO::FETCH_ASSOC);
		if(!count($res)>0){ #If not in user meta add it for user
			$sql = "INSERT INTO `user_meta` (`id_user`,`key`,`value`) VALUES (:id_user,'video',:id_video);";
			$conn = $db->prepare($sql);
			$conn->bindParam(':id_user',$_SESSION['id']);
			$conn->bindParam(':id_video',$idVideo);
			$conn->execute();
		}
		return false; 
	}elseif(count($res)==0){#not in db, check videos_tmp
		$sql = "SELECT * FROM `videos_tmp` WHERE `url` = :url"; #check if url exist in tmp videos (beeing downloading)
		$conn = $db->prepare($sql);
		$conn->bindParam(':url', $url);
		$conn->execute();
		$res = $conn->fetchAll(PDO::FETCH_ASSOC);
		if (count($res) > 0 && !empty($_SESSION['id'])){
			#Check if user had video
			$idVideo = $res[0]['id'];
			$sql = "SELECT * FROM `user_meta` WHERE `key`='video_tmp' AND `id_user`=:id_user AND `value`=:id_video;";
			$conn = $db->prepare($sql);
			$conn->bindParam(':id_user',$_SESSION['id']);
			$conn->bindParam(':id_video',$res[0]['id']);
			$conn->execute();
			$res = $conn->fetchAll(PDO::FETCH_ASSOC);
			if(!count($res)>0){ #If not in user meta add it for user
				$sql = "INSERT INTO `user_meta` (`id_user`,`key`,`value`) VALUES (:id_user,'video_tmp',:id_video);";
				$conn = $db->prepare($sql);
				$conn->bindParam(':id_user',$_SESSION['id']);
				$conn->bindParam(':id_video',$idVideo);
				$conn->execute();
			}
			return false;
		}elseif(count($res) == 0){ #let's download the video
			$sql = "INSERT INTO `videos_tmp` (`url`,`id_channel`) VALUES (:url,:id_channel)";
			$conn = $db->prepare($sql);
			$conn->bindParam(':url', $url);
			$conn->bindParam(':id_channel',$channel);
			$conn->execute();
			if(!empty($_SESSION['id'])){
				$sql = "SELECT * FROM `videos_tmp` WHERE `url` = :url"; #Get video id
				$conn = $db->prepare($sql);
				$conn->bindParam(':url', $url);
				$conn->execute();
				$res = $conn->fetchAll(PDO::FETCH_ASSOC);
				$sql = "INSERT INTO `user_meta` (`id_user`,`key`,`value`) VALUES (:id_user,'video_tmp',:id_video);";
				$conn = $db->prepare($sql);
				$conn->bindParam(':id_user',$_SESSION['id']);
				$conn->bindParam(':id_video',$res[0]['id']);
				$conn->execute();
				$res = $conn->fetchAll(PDO::FETCH_ASSOC);
			}
			return true;
		}
	}
}

function getChannelId($channel,$db){
	$conn = $db->prepare("SELECT `id` FROM `channels` WHERE `channel` = :channel");
	$conn->bindParam(':channel',$channel);
	$conn->execute();
	$res = $conn->fetch(PDO::FETCH_ASSOC);
		if(count($res)==1){
		return $res['id'];
	}
	return 0;
}

function getVideoId($video,$db){
	$conn = $db->prepare("SELECT `id` FROM `videos` WHERE `id` = :id");
	$conn->bindParam(':id',$video);
	$conn->execute();
	$res = $conn->fetch(PDO::FETCH_ASSOC);
	if(count($res) == 1){
		return $res['id'];
	}
	return 0;
}

function checkPassword($login,$password,$db){
	$conn = $db->prepare("SELECT `password` FROM `users` WHERE `login` = :login");
	$conn->bindParam(':login',$login);
	$conn->execute();
	$res = $conn->fetch(PDO::FETCH_ASSOC);
	$dbSalt = substr($res['password'],0,128);
	$passwdHash = hash("sha512",$password);
	if($dbSalt.$passwdHash == $res['password']){
		return true;
	}
	return false;

}

#Store new password in db
#Todo generate password and store it correctly
function updatePassword($db,$password='',$userID=''){
	if($password == ''){#generate password
		echo "generate password";
	}
	$salt = bin2hex(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
	$passwdHash = hash("sha512",$password);
	$conn = $db->prepare("UPDATE  `users` SET `password` = :password WHERE `id` = :id");
	if($userID == ''){
		$conn->bindParam(':id',$_SESSION['id']);
	}else{
		$conn->bindParam(':id',$userID);
	}
	$dbPass = $salt.$passwdHash;
	$conn->bindParam(':password',$dbPass);
	$conn->execute();

}
function knownErrors($errors,$url,$id,$type,$db,$config){
	$errorMsg = '';
	$removeFromTMP = false;
	$errorsArray = explode("\n",$errors);
	$errorsArray = array_unique($errorsArray);
	$errorsArray = array_filter($errorsArray);
	$i=0;
	foreach($errorsArray as $error){
		foreach($config['youtubedlErrors'] as $youtubedlError){
			if(strstr($error,$youtubedlError) != false){
				$errorMsg = $errorsArray[$i];
				switch ($youtubedlError) {
					case $config['youtubedlErrors']['402'] :
						syslog(LOG_WARNING,$config['youtubedlErrors']['402']." append");
						syslog(LOG_WARNING,"Wait for ".$config['timeToSleep']);
						sleep($config['timeToSleep']);
						break;
					case $config['youtubedlErrors']['Unknown'] :
						$removeFromTMP = true;
						break;
				}
				$errors = str_replace($errorsArray[$i]."\n",'',$errors);
			}
		}
		#Save error, with the id in BDD
		if(!empty($errorMsg) || !empty($errors)){
			$message = $errorMsg;
			syslog(LOG_INFO,"an errors has occur $message");
			$conn = $db->prepare("SELECT * FROM `errors` WHERE `url` = :url AND `message` = :error AND `type` = :type");
			$conn->bindParam(':url',$url);
			$conn->bindParam(':error',$message);
			$conn->bindParam(':type',$type);
			$conn->execute();
			$res=$conn->fetchAll(PDO::FETCH_ASSOC);
			if(count($res) == 0){
				$conn = $db->prepare("INSERT INTO `errors` (`url`,`id_url`,`message`,`timestamp`,`type`) VALUES (:url,:id_url,:error,:timestamp,:type)");
				$url2=htmlentities($url);
				$conn->bindParam(':url',$url2);
				$conn->bindParam(':id_url',$id);
				$currentTime = time();
				$conn->bindParam(':timestamp',$currentTime);
				$conn->bindParam(':error',$message);
				$conn->bindParam(':type',$type);
				$conn->execute();
				if($type == 'video' && $removeFromTMP){
					$conn = $db->prepare("DELETE FROM `videos_tmp` WHERE `id` = :id");
					$conn->bindParam(':id',$id);
					$conn->execute();
					$conn = $db->prepare("DELETE FROM `user_meta` WHERE `key` = 'video_tmp' AND `value` = :id");
					$conn->bindParam(':id',$id);
					$conn->execute();
				}
			}
		}
		$i++;
	}
	return $errors;
}
