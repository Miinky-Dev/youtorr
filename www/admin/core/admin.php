<?php
if(!defined('YOUTORR') || $_SESSION['role']!=100){
	die(1);
}
#User add
if(!empty($_POST)){
	$userMsg = Array();
	if($_POST['action']=='add'){
		if($_POST['login']==''){
			$userMsg[]="Empty login";
		}
		$login=htmlentities($_POST['login']);
		$conn=$db->prepare("SELECT `id` FROM `users` WHERE `login` = :login");
		$conn->bindParam(':login',$login);
		$conn->execute();
		$res = $conn->fetch(PDO::FETCH_ASSOC);
		if($res!=false){
			$userMsg[]="$login already in database";
		}
		if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)==false){
			$userMsg[]="Invalid email";
		}
		if($_POST['password1']==''){
			$userMsg[]="Empty password";
		}
		if($_POST['password1']!=$_POST['password2']){
			$userMsg[]="Passwords mismatch";
		}
		$role=0;
		switch($_POST['role']){
			case 'admin':
				$role=100;
				break;
			case 'user':
				$role=50;
				break;
			default :
				$userMsg[]="Invalide value for role ";
		}
		if(count($userMsg)==0){ #No errors
			$conn=$db->prepare("INSERT INTO `users` (`login`,`mail`,`role`) VALUES (:login,:mail,:role) ");
			$conn->bindParam(':login',$login);
			$conn->bindParam(':mail',$_POST['email']);
			$conn->bindParam(':role',$role);
			$conn->execute();
			$conn=$db->prepare('SELECT `id` FROM `users` WHERE `login` = :login');
			$conn->bindParam(':login',$login);
			$conn->execute();
			$res = $conn->fetch(PDO::FETCH_ASSOC);
			updatePassword($db,$_POST['password1'],$res['id']);
		}
	}elseif($_POST['action']=='delete'){
		foreach($_POST['id'] as $id){
			if($_SESSION['id']!=$id){
				$conn=$db->prepare("DELETE FROM `users` WHERE `id` = :id");
				$conn->bindParam(':id',$id);
				$conn->execute();
			}else{
				$userMsg[]="Can't delete yourself";
			}
		}
	}
}
#Get user list
$conn=$db->prepare("SELECT `id`,`login`,`role` FROM `users` ORDER BY `role` DESC");
$conn->execute();
$userList=$conn->fetchAll(PDO::FETCH_ASSOC);
#Check the downloading video
$res=$db->query("SELECT `id`,`name`,`url`,`desc`,`log_file` FROM `videos_tmp` WHERE `log_file` != ''");
$videoLog="";
$line='';
$lastLine='';
if(count($res) > 0){ #If video is queued
	foreach ($res as $tmpVideo){ #Print log for downloading video
		$logFile = fopen($tmpVideo['log_file'],'r');
		if ($logFile == null){
			$videoLog .= nl2br("Error opening ".$tmpVideo['log_file']."");
		}else{
			while($line=fgets($logFile)){ #crapy hack 'cause of "^M" the log
				if(substr_count($line,'download')>=1){
					if(substr_count($line,'ETA')>=1){ 
						$downloadArray=explode('[download]',$line);
						$lastLine="[download]".nl2br($downloadArray[count($downloadArray)-1]);
					}else{
						$videoLog.=nl2br($line);
					}
				}else{
					$videoLog.=nl2br($line);
				}
			}
			$videoLog.=$lastLine;
			fclose($logFile);
		}
	}
}
#Get errors
$conn=$db->prepare("SELECT * FROM errors");
$conn->execute();
$errors = $conn->fetchAll(PDO::FETCH_ASSOC);
#check the downloading channel
$res=$db->query("SELECT `id`,`channel`,`url`,`log_file` FROM `channels` WHERE `log_file` != ''");
$channelLog='';
if(count($res)> 0){
	foreach($res as $tmpChannel){
		$logFile = fopen($tmpChannel['log_file'],'r');
		if ($logFile == null){
			$channelLog .= nl2br("Error opening ".$tmpChannel['log_file']."");
		}else{
			$channelLog=nl2br(fread($logFile,filesize($tmpChannel['log_file'])));
		}
	}
}
$remain = dbCounter("SELECT count(*) AS count FROM `videos_tmp`",$db);
$nbVideos = dbCounter("SELECT count(*) AS count FROM `videos`",$db);
$nbChannels = dbCounter("SELECT count(*) AS count FROM `channels`",$db);
$currentSize=getDirSize($config['torrentDataDir']."/");
$currentSize=inSize($currentSize,$config['maxSize']);
$currentSize=substr($currentSize,0,5);

$conn=$db->prepare("SELECT * FROM `channels`");
$conn->execute();
$channels = $conn->fetchAll(PDO::FETCH_ASSOC);
$counter=0;
foreach($channels as $channel){
	$channels[$counter]['nbVideo']=dbCounter("SELECT count(*) AS count FROM `videos` WHERE `id_channel` = ".$channel['id'],$db);
	$channels[$counter]['nbFollower']=dbCounter("SELECT count(*) AS count FROM `user_meta` WHERE `key`='channel' AND `value`=".$channel['id'],$db);
	$counter++;
}

include('views/admin.php');
?>
