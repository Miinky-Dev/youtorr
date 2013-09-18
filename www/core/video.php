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
$videoID=0;
if(isset($_GET['video']) && !empty($_GET['video'])){
	$videoID=getVideoId($_GET['video'],$db);
}
$sql="SELECT `videos`.`id`,`videos`.`name`,`videos`.`url`,`videos`.`desc`,`videos`.`torrent_file`,`magnetlink`,`channels`.`channel` FROM `videos` LEFT JOIN `channels` ON `channels`.`id`=`videos`.`id_channel`";
if($videoID!=0){
		$sql.=" WHERE `videos`.`id` = :id";
}
$sql.=" ORDER BY `videos`.`id` DESC";
if($_GET['page']=='default' && $config['nbLastVideo'] > 0){
	//$sql.=" LIMIT 0, :limit";
	$sql.=" LIMIT 0, ".$config['nbLastVideo'];
}

$conn=$db->prepare($sql);
if($videoID!=0){
	$conn->bindParam(':id',$videoID);
}

$conn->execute();
$videos=$conn->fetchAll(PDO::FETCH_ASSOC);

include('views/video.php');
