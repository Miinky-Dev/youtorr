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
include('../www/admin/config.php');
include('../www/admin/functions.php');
$force=false;
$trackers=explode(',',$config['trackerUrl']);
$conn=$db->prepare("SELECT `id`,`name`,`magnetlink` FROM videos");
$conn->execute();
$res=$conn->fetchAll(PDO::FETCH_ASSOC);
$i=0;
$total = count($res);
foreach($res as $video){
	$i++;
	if(!empty($video['magnetlink']) && !$force)
		continue;
	echo "$i/$total : ".$video['name']."\n";
	$torrent = new Torrent($config['torrentDataDir'].'/'.$video['name']);
	$torrent->announce($trackers);
	$magnet = $torrent->magnet(false);
	$conn=$db->prepare("UPDATE `videos` SET `magnetlink` = :magnet WHERE `id`=:id");
	$conn->bindParam(":magnet",$magnet);
	$conn->bindParam(":id",$video['id']);
	$conn->execute();
}
echo "\n";
?>
