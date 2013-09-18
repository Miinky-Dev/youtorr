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
$channelID=0;
if(isset($_GET['channel']) && !empty($_GET['channel'])){
	$channelID=getChannelId($_GET['channel'],$db);
}
$sql="SELECT DISTINCT(`channels`.`id`),`channels`.`channel`,`channels`.`url` FROM `channels` INNER JOIN `videos` ON `videos`.`id_channel` = `channels`.`id`";
if($channelID!=0){
		$sql.=" WHERE `channels`.`id` = :id";
}
$sql.=" ORDER BY `channels`.`id` DESC";
if($_GET['page'] == 'default' && $config['nbLastChannel'] > 0){
	$sql.=" LIMIT 0, ".$config['nbLastChannel'];
}
$conn=$db->prepare($sql);
if($channelID!=0){
	$conn->bindParam(':id',$channelID);
}
$conn->execute();
$channels=$conn->fetchAll(PDO::FETCH_ASSOC);
$counter=0;

foreach($channels as $channel){
	$sql="SELECT `id`,`name`,`desc`,`url`,`torrent_file` FROM `videos` WHERE `id_channel` = :id_channel ORDER BY `id` ASC";
	if(($channelID==0 || $_GET['page']=='default')&& $config['nbVideoChannel'] > 0){
		$sql.=" LIMIT 0, ".$config['nbVideoChannel'];
	}
	$conn=$db->prepare($sql);
	$conn->bindParam(':id_channel',$channel['id']);
	$conn->execute();
	$channels[$counter]['videos']=$conn->fetchAll(PDO::FETCH_ASSOC);
	$channels[$counter]['nbVideo']=dbCounter("SELECT count(*) AS count FROM `videos` WHERE `id_channel` = ".$channel['id'],$db);
	if(is_dir($config['torrentDataDir']."/".$channel['channel']."/")){
		$channelSize=getDirSize($config['torrentDataDir']."/".$channel['channel']."/");
		$channelSize=inSize($channelSize,$config['maxSize']);
		$channelSize=substr($channelSize,0,5);
		$channels[$counter]['size']=$channelSize;
	}
	$counter++;
}
include('views/channel.php');
