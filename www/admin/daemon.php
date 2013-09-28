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
if (!function_exists('pcntl_fork')) {
	die("error: This script requires PHP compiled with PCNTL module.\n");
}

include_once('config.php');
include_once('functions.php');
openlog("youtorr", $config['daemonLog'], LOG_LOCAL0);
syslog(LOG_INFO,"Youtorr starting");

while (1){
	#Check for delete
	$conn = $db->prepare("SELECT `id`,`channel` FROM `channels` WHERE `delete` = 1");
	$conn->execute();
	$res = $conn->fetchAll(PDO::FETCH_ASSOC);
	foreach($res as $channelDelete){
			syslog(LOG_INFO,"Deleting channel : ".$channelDelete['channel']);
			#If in config we delete files
			if($config['deleteFile']){
				syslog(LOG_INFO,"Deleting all file from ".$channelDelete['channel']);
				$conn=$db->prepare("SELECT `torrent_file`,`name` FROM `videos` WHERE `id_channel` = :id");
				$conn->bindParam(':id',$channelDelete['id']);
				$conn->execute();
				$res2 = $conn->fetchAll(PDO::FETCH_ASSOC);
				foreach($res2 as $video){
					if($config['channelSymlink']){
						unlink($config['torrentDataDir']."/".$channelDelete['channel']."/".$video['name']);
					}
					unlink($config['torrentFileDir']."/".$video['torrent_file'].$config['torrentExt']);
					unlink($config['torrentDataDir']."/".$video['name']);
				}
				if($config['channelSymlink']){
					rmdir($config['torrentDataDir']."/".$channelDelete['channel']."/");
				}
			}
			#And delete from database
			$conn = $db->prepare("DELETE FROM `channels` WHERE id = :id");
			$conn->bindParam(':id',$channelDelete['id']);
			$conn->execute();
			$conn = $db->prepare("DELETE FROM `videos` WHERE `id_channel` = :id");
			$conn->bindParam(':id',$channelDelete['id']);
			$conn->execute();
			$conn = $db->prepare("DELETE FROM `videos_tmp` WHERE `id_channel` = :id");
			$conn->bindParam(':id',$channelDelete['id']);
			$conn->execute();
			$conn = $db->prepare("DELETE FROM `user_meta` WHERE `key` = 'channel' AND `value` = :id");
			$conn->bindParam(':id',$channelDelete['id']);
			$conn->execute();
	}
	#Check channel youtube
	$conn = $db->prepare("SELECT `id`,`url`,`channel`,`force` FROM `channels`");
	$conn->execute();
	$res = $conn->fetchAll(PDO::FETCH_ASSOC);
	$curTimestamp = time();
	$errorsCounter = 0;
	foreach($res as $channel){
		#if no channel name we Check channel properties
		if(empty($channel['channel'])){
			$logFile = $config['logDir']."/channelCheck-".$channel['id']."-$curTimestamp.log";
			#Simulate download of first video to get some info
			$cmd = $config['pythonPath']." ".$config['youtubedlPath']." ".$channel['url']." -s --playlist-end 1 > ".$logFile;
			$errors = launchProccess($cmd,$logFile);
			$errors = knownErrors($errors,$channel['url'],$channel['id'],'channel',$db,$config);
			if(!empty($errors)){
				var_dump($channel);
				#if need remove logfile
				if(!$config['keepLogs']){
					unlink($logFile);
				}
				if($config['exitOnError']){
					exit(-1);
				}else{
					break;
				}
			}else{
				#Get some info from log
				$logFileOpen = fopen($logFile,'r');
				while($line = fgets($logFileOpen)){ #crapy hack cause of the log
					if(empty($channel['channel'])){
						if(substr_count($line,'user') && empty($channel['channel'])){#Get the youtube user
							$channelNameArray = explode(' ',$line);
							$channelName = $channelNameArray[1];
							$channel['channel'] = substr($channelName,0,-1);
						}
					}
				}
				#Can have multiple channel by adding param to the youtube user url
				#Don't remember why it's here 
				if(dbCounter("SELECT count(id) AS count FROM channels WHERE channel = '".$channel['channel']."'",$db) >= 1){
					$conn = $db->prepare("DELETE FROM channels WHERE id = :id");
					$conn->bindParam(':id',$channel['id']);
					$conn->execute();
				}
				#if need remove logfile
				if(!$config['keepLogs']){
					unlink($logFile);
				}
			}
		}

		if(file_exists($config['torrentDataDir']."/".$channel['channel']) == false && $config['channelSymlink']){
			mkdir($config['torrentDataDir']."/".$channel['channel']);
		}
		#Check new video
		$newVideo = true;
		$start = 1;
		while($newVideo || $channel['force'] == 1){
			#download video list 
			$logFile = $config['logDir']."/videoList-".$channel['id']."-$curTimestamp.log";
			#Download recent playlist, and check video url in
			$cmd = $config['pythonPath']." ".$config['youtubedlPath']." ".$channel['url']." -s --skip-download -i --playlist-start $start --playlist-end "
			.($start+$config['channelCheckStep']); 
			$conn = $db->prepare("UPDATE `channels` SET `log_file` = :log_file WHERE `id` = :id");
			$conn->bindParam(':id',$channel['id']);
			$conn->bindParam(':log_file',$logFile);
			$conn->execute();
			$errors = launchProccess($cmd,$logFile);
			$errors = knownErrors($errors,$channel['url'],$channel['id'],'channel_check',$db,$config);
			if(!empty($errors)){
				syslog(LOG_WARNING,$errors);
				var_dump($channel);
				#if need remove logfile
				if(!$config['keepLogs']){
					unlink($logFile);
				}
				if($config['exitOnError']){
					exit(-1);
				}else{#exit from while 
					$newVideo=false;
					$channel['force']=0;
				}
			}else{
				$videoName = '';
				$logFileOpen = fopen($logFile,'r'); #Get video name and put in video_tmp
				$counter = 0;
				while($line = fgets($logFileOpen)){ 
					if(substr_count($line,'Extracting')){#Get video name and forge url
						$counter++;
						$videoNameArray = explode(' ',$line);
						$videoName = $videoNameArray[1];
						$videoName = substr($videoName,0,-1);
						$url = "https://www.youtube.com/watch?v=".$videoName;
						syslog(LOG_DEBUG,$url);
						$url = escapeshellcmd($url); #try to be secure
						$newVideo = addUrl($url,$db,$channel['id']);
						if($newVideo){
							syslog(LOG_DEBUG,"Add video in database");
						}else{
							syslog(LOG_DEBUG,"Video already in database");
						}
					}
				}
				if($counter == 0){#If no videos found, we have no new to download
					$newVideo = false;
					$channel['force'] = 0;
					$conn = $db->prepare("UPDATE `channels` SET `force` = 0 WHERE `id` = :id");
					$conn->bindParam(':id',$channel['id']);
					$conn->execute();
				}
				$start += $config['channelCheckStep']+1;
				#complete database
				$conn = $db->prepare("UPDATE `channels` SET `channel` = :channel WHERE `id`= :id ");
				$conn->bindParam(':id',$channel['id']);
				$conn->bindParam(':channel',$channel['channel']);
				$conn->execute();
				#if need remove logfile
				if(!$config['keepLogs']){
					unlink($logFile);
				}
				syslog(LOG_INFO,"update log for channel ".$channel['channel']);
				$conn = $db->prepare("UPDATE `channels` SET `log_file` = '' WHERE `id` = :id");
				$conn->bindParam(':id',$channel['id']);
				$conn->execute();
			}
		}
	}
	#end channel youtube
	#Download video
	$conn = $db->prepare("SELECT `id`,`url`,`name`,`log_file`,`id_channel` FROM `videos_tmp`");
	$conn->execute();
	$res = $conn->fetchAll(PDO::FETCH_ASSOC);
	foreach ($res as $video){
		#TODO : improve with --get-url and php curl
		if(!isSpace($config['maxSize'],$config['torrentDataDir']."/")){
			syslog(LOG_ALERT,"No more space,no downloading data");
			syslog(LOG_ALERT,"Change max limit or make some space");
			if($config['exitOnError']){
				exit(-1);
			}
			continue;
		}
		$video['name'] = str_replace(' ','_',$video['name']);
		$logFile = $config['logDir']."/download-".$video['id']."-$curTimestamp.log";
		#forge the command line
		$cmd = $config['pythonPath']." ".$config['youtubedlPath']." -i --restrict-filenames ".$video['url']."";
		$fileName = '';
		if (empty($video['name'])){
			syslog(LOG_INFO,"Auto set name");
			if ($config['tmpDir'] != ''){
				$cmd .= " -o '".$config['tmpDir']."%(title)s.%(ext)s'";
			}else{
				$cmd .= " -t";
			}
		}else{
			if ($config['tmpDir'] != ''){
				$cmd .= "-o '".$config['tmpDir'].$fileName;
			}
			$fileName = $video['name'];
		}
		#set log file
		if(!empty($video['log_file'])){
			syslog(LOG_INFO,"Removing old log file ".$video['log_file']." for ".$video['name']);
		}
		#set logfile in db
		$conn = $db->prepare("UPDATE `videos_tmp` SET `log_file` = :log WHERE `id`= :id");
		$conn->bindParam(':log', $logFile);
		$conn->bindParam(':id', $video['id']);
		$conn->execute();
		#Download File
		$errors = launchProccess($cmd,$logFile);
		$errors = knownErrors($errors,$video['url'],$video['id'],'video',$db,$config);
		if(!empty($errors)){
			#need clean log and tmpfile
			var_dump($video);
			if(!$config['keepLogs']){
				unlink($logFile);
				$conn = $db->prepare("UPDATE `videos_tmp` SET `log_file` = '' WHERE `id`= :id");
				$conn->bindParam(':id', $video['id']);
				$conn->execute();
			}
			if($config['exitOnError']){
			        exit(-1);
			}
		}else{
			#Get some info from log
			$logFileOpen = fopen($logFile,'r');
			$provider = '';
			while($line = fgets($logFileOpen)){ #crapy hack cause of the log
				if(empty($provider)){
					$providerArray = explode(" ",$line);
					$provider = $providerArray[0];
					$provider = substr($provider,1,-1);
				}
				if(substr_count($line,'Destination:') && empty($video['name'])){#We get the line
					$tmpString = explode(':',$line);
					$oldFileName = substr($tmpString[1],0,-1); #^M again :(
					$oldFileName = substr($oldFileName,1,strlen($oldFileName)); 
					$oldFileName = str_replace('?','',$oldFileName);
					$fileName = str_replace($config['tmpDir'],'',$oldFileName);
				}
			}
			#if need remove logfile
			if(!$config['keepLogs']){
				unlink($logFile);
			}
			#Get description
			if(empty($video['desc'])){
				$logFile = $config['logDir']."/desc-".$video['id']."-$curTimestamp.log";
				$cmd = $config['pythonPath']." ".$config['youtubedlPath']." ".$video['url']." --get-description";
				$errors = launchProccess($cmd,$logFile);
				$errors = knownErrors($errors,$video['url'],$video['id'],'video',$db,$config);
				if(!empty($errors)){
					if($config['exitOnError']){
						exit(-1);
					}
				}else{
					$video['desc'] = file_get_contents($logFile);
					syslog(LOG_DEBUG,$video['desc']);
				}
				#if need remove logfile
				if(!$config['keepLogs']){
					unlink($logFile);
				}
			}
			#make torrent
			#get youtube user name
			syslog(LOG_INFO,"making torrent file");
			$torrentFile = '';
			if(!empty($video['id_channel'])){
				$conn = $db->prepare("SELECT `id`,`channel` FROM `channels` WHERE `id`= :id_channel");
				$conn->bindParam(':id_channel', $video['id_channel']);
				$conn->execute();
				$res = $conn->fetch(PDO::FETCH_ASSOC);
				$magnetLink=mktorrent($fileName,$config['trackerUrl'],$config['torrentFileDir'],$config['torrentDataDir'],$res['channel'],$config['channelSymlink'],
				$config['tmpDir']);
				$torrentFile = $res['channel']."-".$fileName.".torrent";
				if($config['zipUserTorrent']){
					syslog(LOG_INFO,$res['channel']." : Making zip archive");
					$zip = new ZipArchive;
					if ($zip->open($config['torrentFileDir']."/".$config['zipPrefix'].$res['channel'].'.zip', ZipArchive::OVERWRITE) === TRUE) {
						$conn = $db->prepare("SELECT * FROM videos WHERE id_channel = :id_channel");
						$conn->bindParam(':id_channel',$res['id']);
						$conn->execute();
						$videos4zips = $conn->fetchAll(PDO::FETCH_ASSOC);
						if($zip->addEmptyDir($config['torrentFileDir']."/".$config['zipPrefix'].$res['channel'])){
							foreach($videos4zips as $video4zip){
								syslog(LOG_DEBUG,"adding ".$video4zip['torrent_file']." to ".$config['torrentFileDir']."/".$res['channel'].".zip");
								$zip->addFile($config['torrentFileDir']."/".$video4zip['torrent_file'].$config['torrentExt'],
								$res['channel']."/".$video4zip['torrent_file']);
							}
						}
						syslog(LOG_DEBUG,"adding ".$torrentFile." to ".$config['torrentFileDir']."/".$res['channel'].".zip");
						$zip->addFile($config['torrentFileDir']."/".$torrentFile.$config['torrentExt'],$res['channel']."/".$torrentFile);
						syslog(LOG_INFO,$res['channel']." : Zip archive contain ".($zip->numFiles - 1)." files ");
						$zip->close();
					}
				}
			}else{ 
				$magnetLink=mktorrent($fileName,$config['trackerUrl'],$config['torrentFileDir'],$config['torrentDataDir'],'',false,$config['tmpDir']);
				$torrentFile = $fileName.".torrent";
			}
			#put in video table
			$conn = $db->prepare("INSERT INTO `videos` (`url`,`name`,`desc`,`torrent_file`,`magnetlink`,`provider`,`add_date`,`id_channel`) 
			VALUES ( :url, :name, :desc, :torrent,:magnet, :provider,:add_date, :id_channel)");
			$conn->bindParam(':url', $video['url']);
			$conn->bindParam(':id_channel', $video['id_channel']);
			$conn->bindParam(':name', $fileName);
			$conn->bindParam(':desc', $video['desc']);
			$conn->bindParam(':torrent',$torrentFile);
			$conn->bindParam(':magnet',$magnetLink);
			$conn->bindParam(':provider', $provider);
			$conn->bindParam(':add_date', $curTimestamp);
			$conn->execute();
			$sql = "SELECT * FROM `videos` WHERE `url` = :url"; #Get video id
			$conn = $db->prepare($sql);
			$conn->bindParam(':url', $video['url']);
			$conn->execute();
			$res = $conn->fetchAll(PDO::FETCH_ASSOC);
			#update from video_tmp to video on user_meta.
			$sql = "UPDATE user_meta SET `key`='video',`value`=:id_video WHERE `key`='video_tmp' AND `value`=:id_video_tmp;";
			$conn = $db->prepare($sql);
			$conn->bindParam(':id_video',$res[0]['id']);
			$conn->bindParam(':id_video_tmp',$video['id']);
			$conn->execute();

			#remove from tmp
			if($errors == ''){
				$conn = $db->prepare("DELETE FROM `videos_tmp` WHERE `id` = :id");
				$conn->bindParam(':id', $video['id']);
				$conn->execute();
			}
		}
		#End download video
	}
	syslog(LOG_INFO,date('H:i:s')." - End of download next in ".$config['timeToSleep']." sec");
	sleep($config['timeToSleep']);
}
?>
